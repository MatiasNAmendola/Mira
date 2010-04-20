<?php

/**
 * Mira
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.opensource.org/licenses/bsd-license.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@gevega.com so we can send you a copy immediately.
 *
 * @category   Mira
 * @package    Mira_Service
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * @category   Mira
 * @package    Mira_Service
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Service_VegaService extends Mira_Service_Abstract
{
    // ###################################################
    // CREATE / EDIT / DELETE
    // ###################################################

    /**
     * Create a new vega
     * 
     * $permissions contain security settings for this vega. 
     * - A vega can inherit its permissions from another vega : in that case $permissions  
     *   contain the id of that "parent" vega
     * - A vega can set its custom permissions : in that case $permissions is an associative
     *   array $userId => $role, where $role is either "editor" or "viewer"
     * 
     * $properties is an associative array property name or id => value. If the corresponding
     * property is a vega, then the value is its id only.
     * 
     * @param integer $typeId
     * @param string $name
     * @param array | integer $permissions
     * @param array $properties
     * 
     * @return Mira_Core_Vega
     */
    public function create($typeId, $name, $properties = null, $permissions = null) 
    {
        $type = $this->api->tid($typeId);
        if (!$type) throw new Mira_Core_Exception_NotFoundException("Type $typeId");
        
        $vega = $type->createVega($name, $this->api->getUser());
        
        /** @var Mira_Core_Scope **/
        $scope = $vega->scope;
        if (Mira_Utils_String::isId($permissions)) {
            // inheriting
            $parent = $this->api->vid($permissions);
            if (!$parent) throw new Mira_Core_Exception_NotFoundException("Vega $permissions");
            $scope->setInheritFrom($parent->scope->id);
        } else if ($permissions) {
	        foreach ($permissions as $userId => $role) {
	            $scope->addUserRole($userId, $role);
	        }
        }
        
        foreach ($properties as $pNameOrId => $pValue) {
        	if (Mira_Utils_String::isId($pNameOrId)) {
        		$prop = $type->propertyWithId($pNameOrId);
        		if ($prop) $pNameOrId = $prop->name;
        	}
            $vega->$pNameOrId = $pValue;
        }
        
        $vega->save();
        
        return $vega;
    }

	/**
     * Update an existing vega
     * 
     * $permissions contains only the modified permissions.
     * - to inherit from another vega, $permissions contain the id of that vega
     * - to delete a role, set the value to null
     * - to add a role simply add a pair $userId => $role ("editor" or "viewer")
     * - to edit a role set the new pair $userId => $role
     * 
     * 1 - for instance if the previous $permissions were
     * array(2 => "editor", 5 => "viewer", 8 => "viewer")
     * 2 - if you call that function with
     * $permissions = array(2 => null, 3 => "editor", 8 => "editor")
     * 3 - the resulting $permissions will be
     * array(3 => "editor", 5 => "viewer", 8 => "editor") 
     * 
     * @param integer $vegaId
     * @param string $newName set it to null to let it unchanged
     * @param array | integer $permissions
     * @param array $properties contains the modified properties only. Other 
     * 				properties will remain unchanged
     * 
     * @return Mira_Core_Vega
     */
    public function update($vegaId, $newName, $properties = null, $permissions = null)
    {
        $vega = $this->api->vid($vegaId);
        if (!$vega) throw new Mira_Core_Exception_NotFoundException("Vega $vegaId");
        if ($vega->scope->getUserRole($this->api->getUser()) != Mira_Core_Scope::ROLE_EDITOR)
            throw new Mira_Core_Exception_NotAuthorizedException($this->api->getUser(), "edit Vega $vega->name");
        
        if ($newName) $vega->name = $newName;
        
        /** @var Mira_Core_Scope **/
        $scope = $vega->scope;
        foreach ($permissions as $userId => $role) {
            if (!$role) {
                $scope->removeUserRole($userId);
            } else {
                $scope->addUserRole($userId, $role);
            }
        }
        
        foreach ($properties as $pName => $pValue) {
            $vega->$pName = $pValue;
        }
        
        $vega->save();
        
        return $vega;
    }
    
    /**
     * Delete a vega
     * 
     * @param integer $vegaId
     */
    public function delete($vegaId)
    {
        $vega = $this->api->vid($vegaId);
        if (!$vega) throw new Mira_Core_Exception_NotFoundException("Vega $vegaId");
        if ($vega->scope->getUserRole($this->api->getUser()) != Mira_Core_Scope::ROLE_EDITOR)
            throw new Mira_Core_Exception_NotAuthorizedException($this->api->getUser(), "delete Vega $vega->name");
        
        $vega->delete();
    }
    
    /**
     * Restore a vega that has been deleted
     * 
     * @param integer $vegaId
     * 
     * @return Mira_Core_Vega
     */
    public function restore($vegaId)
    {
        $vega = $this->api->selectVegas()
                          ->where("id", $vegaId)
                          ->where("status", "trashed")
                          ->fetchObject();
        if (!$vega) throw new Mira_Core_Exception_NotFoundException("Vega $vegaId");
        if ($vega->scope->getUserRole($this->api->getUser()) != Mira_Core_Scope::ROLE_EDITOR)
            throw new Mira_Core_Exception_NotAuthorizedException($this->api->getUser(), "delete Vega $vega->name");
        
        $vega->restore();
    }
    
    // ###################################################
    // FIND METHODS
    // ###################################################
    
    /**
     * Find a vega by id
     * 
     * @param integer $vegaId
     * 
     * @return Mira_Core_Vega
     */
    public function findById($vegaId)
    {
        return $this->api->vid($vegaId);
    }
    
    /**
     * Find a vega by its name
     * 
     * It only returns the first result, if you want more control on the
     * results see {@link find()}
     * 
     * @param string $name
     * 
     * @return Mira_Core_Vega
     */
    public function findByName($name)
    {
        $this->api->vname($name);
    }
    
    /**
     * Generic find method
     * 
     * 1/ filters syntax
     * array(
     * 	array("id", 
     * 		$vegaId),
     *  array("name", 
     *  	$anyString, 
     *  	"strict" | "permissive"),
     *  array("revision", 
     *  	"last" | $revisionId | "first"),
     *  array("vegaType", 
     *  	$vegaTypeId | $vegaTypeName),
     *  array("status", 
     *  	"alive" | "trashed" | "any"),
     *  array("security", 
     *  	"viewer" | "editor" | "viewer_only"),
     *  array($propertyName | $propertyId, 
     *  	$propertyValue | $vegaId | $vegaName,
     *  	"strict" | "permissive"))
     *  
     *  note: "permissive" is translated by a SQL like "%value%"
     * 
     * 2/ linkedTo syntax
     * array(
     * 	array($vegaId | $vegaName,
     * 		"any" | "vegaproperty" | "generic",
     * 		"any" | "from" | "to",
     * 		"strict" | "permissive"),
     *  ...)
     * 
     * 3/ Options available:
     * - page_offset::integer for paging, the result index to start from. default = 0
     * - page_count::integer number of results to retrieve. default = 50
     * - order_by::string "email" | "id" | "first name" | "last name"... default = "first name"
     * - order_asc::bolean default = true
     * - lazy::bolean default = true
     * 
     * @param array $filters filter results by name, vega properties, status, permissions...
     * @param array $linkedTo filter results by their links
     * @param array $pagingOptions page / sort options 
     * 
     * @return array
     */
    public function find($filters = array(), $linkedTo = array(), $options = array())
    {
        /** @var Mira_Core_Select_VegaSelect **/
        $select = $this->api->selectVegas();
        
        // filters
        $index = 0;
        if ($filters)
        foreach ($filters as $filter) {
            $index++;
            if (!is_array($filter) || count($filter) < 2) {
                throw new Mira_Core_Exception_BadRequestException("Could not parse filter #$index", "Filters are 3 entry arrays: name, value and an optional option arg");
            }
            $select->where($filter[0], $filter[1], (count($filter) > 2 ? $filter[2] : null));
        }
        
        // links
        $index = 0;
        if ($linkedTo)
        foreach ($linkedTo as $link) {
            $index++;
            if (!is_array($link) || count($link) != 4) {
                throw new Mira_Core_Exception_BadRequestException("Could not parse link #$index", "Filters are 4 entry arrays: name or id, link type, link direction, option");
            }
            $select->linkedTo($link[0], $link[1], $link[2], $link[3]);
        }
        
 	    // read options
 	    $offset = Mira_Utils_OptionsHelper::getOption("page_offset", 0, $options);
 	    $count = Mira_Utils_OptionsHelper::getOption("page_count", 50, $options);
 	    $orderBy = Mira_Utils_OptionsHelper::getOption("order_by", null, $options);
 	    $orderAsc = Mira_Utils_OptionsHelper::getOption("order_asc", true, $options);
 	    $lazy = Mira_Utils_OptionsHelper::getOption("lazy", true, $options);
 	    // apply options
 	    $select->limit($count, $offset);
 	    if ($orderBy) $select->order($orderBy, $orderAsc);
 	    
 	    // fetch
 	    $results = $select->fetchAll($lazy);
 	    return $results;
    }
    
    // ###################################################
    // LINK METHODS
    // ###################################################
    
    /**
     * Find vega linked to $vegaId
     * 
     * @param integer $vegaId
     * @param integer $vegaRevision Leave 0 to get latest 
     * @param array $options
     * 
     * @return array
     */
    public function findLinks($vegaId, $vegaRevision = 0, $options = array())
    {
        $vselect = $this->api->selectVegas()
                        ->where("id", $vegaId);
        if ($vegaRevision) $vselect->where("revision", $vegaRevision);

        $vega = $vselect->fetchObject(false);
        
        if (!$vega) throw new Mira_Core_Exception_NotFoundException("Vega $vegaId $vegaRevision");
        
        $select = $this->api->selectVegas();
        $select->linkedTo($vega, Mira_Core_Select_VegaSelect::LINK_TYPE_ANY, Mira_Core_Select_VegaSelect::LINK_DIRECTION_TO);
        
 	    // read options
 	    $offset = Mira_Utils_OptionsHelper::getOption("page_offset", 0, $options);
 	    $count = Mira_Utils_OptionsHelper::getOption("page_count", 50, $options);
 	    $orderBy = Mira_Utils_OptionsHelper::getOption("order_by", null, $options);
 	    $orderAsc = Mira_Utils_OptionsHelper::getOption("order_asc", true, $options);
 	    $lazy = Mira_Utils_OptionsHelper::getOption("lazy", true, $options);
 	    // apply options
 	    $select->limit($count, $offset);
 	    if ($orderBy) $select->order($orderBy, $orderAsc);
        
        return $select->fetchAll($lazy);
    }
    
    /**
     * Add a free link between two vegas
     * 
     * @param integer $vegaId
     * @param integer $vegaRevision put 0 for the latest revision
     * @param $targetVegaId
     */
    public function addGenericLink($vegaId, $vegaRevision, $targetVegaId)
    {
        $select = $this->api->selectVegas()->where("id", $vegaId);
        if ($vegaRevision) $select->where("revision", $vegaRevision);
        /** @var Mira_Core_Vega **/
        $vega = $select->fetchObject(false);
        
        if (!$vega) throw new Mira_Core_Exception_NotFoundException("Vega $vegaId");
        if ($vega->scope->getUserRole($this->api->getUser()) != Mira_Core_Scope::ROLE_EDITOR)
            throw new Mira_Core_Exception_NotAuthorizedException($this->api->getUser(), "edit Vega $vega->name");
        
        if ($this->api->selectVegas()->where("id", $targetVegaId)->count() == 0) {
            throw new Mira_Core_Exception_NotFoundException("Vega $targetVegaId");
        }
        
        return $vega->addGenericLink($targetVegaId);
    }
    
    /**
     * Delete a link
     * 
     * @param integer $vegaId
     * @param integer $vegaRevision put 0 for the latest revision
     * @param integer $linkId
     */
    public function deleteGenericLink($vegaId, $vegaRevision, $toVegaId)
    {
        $select = $this->api->selectVegas()->where("id", $vegaId);
        if ($vegaRevision) $select->where("revision", $vegaRevision);
        /** @var Mira_Core_Vega **/
        $vega = $select->fetchObject(false);
        
        if (!$vega) throw new Mira_Core_Exception_NotFoundException("Vega $vegaId");
        if ($vega->scope->getUserRole($this->api->getUser()) != Mira_Core_Scope::ROLE_EDITOR)
            throw new Mira_Core_Exception_NotAuthorizedException($this->api->getUser(), "delete Vega $vega->name");
        
        $vega->deleteGenericLink($toVegaId);
    }
}
