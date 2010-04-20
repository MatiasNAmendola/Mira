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
class Mira_Service_VegaTypeService extends Mira_Service_Abstract
{
    
    // ###################################################
    // CREATE / EDIT / DELETE
    // ###################################################

    /**
     * Create a new vegatype
     * 
     * (not implemented yet)
     * $permissions contain security settings for this vegatype. 
     * - A vegatype can inherit its permissions from another vegatype : in that case $permissions  
     *   contain the id of that "parent" vega
     * - A vegatype can set its custom permissions : in that case $permissions is an associative
     *   array $userId => $role, where $role is either "editor" or "viewer"
     * 
     * $properties is an array of arrays. those inner array define new
     * properties to add to this new vegatype:
     * - name::string
     * - position::integer
     * - primitiveType::string|integer specify the name or the id of the primitive
     * - vegaType::string|integer specify the name or the id of the vegatype
     * 
     * @param string $name
     * @param array | integer $permissions
     * @param array $properties
     * 
     * @return Mira_Core_VegaType
     */
    public function create($name, $properties = null, $permissions = null) 
    {
        if (count($permissions) > 0) {
            throw new Mira_Core_Exception_NotImplementedException("Security on VegaTypes is not yet implemented");
        }
        
        $type = $this->api->createVegaType($name, $this->api->getUser());
        
        $cur = 0;
        if ($properties)
        foreach ($properties as $property) {
            $vp = $type->createProperty();
            $this->parseProperty($vp, $property, $cur++);
        }
        
        $type->save();
        return $type;
    }

	/**
     * Update an existing vegatype
     * 
     * (not implemented yet)
     * $permissions contains only the modified permissions.
     * - to inherit from another vegatype, $permissions contain the id of that vegatype
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
     * Similar update mecanism for $properties. The key of the associative array is the
     * name or the id of the property before the update. You can only change the position or name of an existing property.
     * array(
     * 	"last name" => array("position" => 1, "name" => ...), // property edited
     *  "property name" => null, // property removed
     *  "new property" => array("position" => 2, "name" => "new property", "primitiveType" => ..., "vegaType" => ...) // property added
     * ) 
     * 
     * @param integer $vegaId
     * @param string $newName set it to null to let it unchanged
     * @param array | integer $roles
     * @param array $properties contains the modified properties only. Other 
     * 				properties will remain unchanged
     * 
     * @return Mira_Core_VegaType
     */
    public function update($vegaTypeId, $newName, $properties = null, $permissions = null)
    {
        if (count($permissions) > 0) {
            throw new Mira_Core_Exception_NotImplementedException("Security on VegaTypes is not yet implemented");
        }
        
        $type = $this->api->tid($vegaTypeId);
        if (!$type) {
            throw new Mira_Core_Exception_NotFoundException("VegaType $vegaTypeId");
        }
        
        if ($newName) $type->name = $newName;
    
        $cur = 0;
        if ($properties)
        foreach ($properties as $propertyNameOrId => $property) {
        	$vp = null;
        	if (Mira_Utils_String::isId($propertyNameOrId))
            	$vp = $type->propertyWithId($propertyNameOrId);
            else
            	$vp = $type->propertyWithName($propertyNameOrId);
            // delete a property
            if (!$property) {
                if ($vp) {
                    $type->removeProperty($vp);
                }
            // edit or create a property
            } else {
                $edit = $vp != null;
                if (!$vp) $vp = $type->createProperty();
                $this->parseProperty($vp, $property, $cur++, $edit);
            }
        }
        
        $type->save();
        
        return $type;
    }
    
    /**
     * Delete a vegatype
     * 
     * @param integer $vegaTypeId
     */
    public function delete($vegaTypeId)
    {
        $type = $this->api->tid($vegaTypeId);
        if (!$type) {
            throw new Mira_Core_Exception_NotFoundException("VegaType $vegaTypeId");
        }
        
        $type->delete();
    }
    
    /**
     * Restore a vegatype that has been deleted
     * 
     * @param integer $vegaTypeId
     * 
     * @return Mira_Core_VegaType
     */
    public function restore($vegaTypeId)
    {
        $type = $this->api->tid($vegaTypeId);
        if (!$type) {
            throw new Mira_Core_Exception_NotFoundException("VegaType $vegaTypeId");
        }
        
        $type->restore();
    }
    
    // ###################################################
    // FIND METHODS
    // ###################################################
    
    /**
     * Find a vegatype by id
     * 
     * @param integer $vegaTypeId
     */
    public function findById($vegaTypeId)
    {
        return $this->api->tid($vegaTypeId);
    }
    
    /**
     * Find a vegatype by its name
     * 
     * It only returns the first result, if you want more control on the
     * results see {@link find()}
     * 
     * @param string $name
     */
    public function findByName($name)
    {
        return $this->api->tname($name);
    }
    
    /**
     * Generic find method
     * 
     * 1/ filters syntax
     * array(
     * 	array("id", 
     * 		$vegaTypeId),
     *  array("name", 
     *  	$anyString, 
     *  	"strict" | "permissive"),
     *  array("revision", 
     *  	"last" | $revisionId | "first"),
     *  array("status", 
     *  	"alive" | "trashed" | "any"),
     *  array("security", 
     *  	"viewer" | "editor" | "viewer_only"))
     *  
     *  note: "permissive" is translated by a SQL like "%value%"
     * 
     * 2/ paging options syntax
     * array("offset" => 0, "count" => 50, "sortBy" => "name", "sortAscending" => true)
     * 
     * @param array $filters filter results by name, vega properties, status, permissions...
     * @param array $options page / sort options 
     * 
     * @return array
     */
    public function find($filters = array(), $options = array())
    {
        /** @var VegaTypeSelect **/
        $select = $this->api->selectVegaTypes();
        
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
    // PARSERS
    // ###################################################
    
    /**
     * @access private
     */
    private function parseProperty($vegaProperty, $fromArray, $defaultPosition = 0, $edit = false)
    {
        $name =  Mira_Utils_OptionsHelper::getOption("name", null, $fromArray);
        $position = Mira_Utils_OptionsHelper::getOption("position", $edit ? null : $defaultPosition, $fromArray);
        $primitiveType = Mira_Utils_OptionsHelper::getOption("primitiveType", $edit ? null : 1, $fromArray);
        $vegaType = Mira_Utils_OptionsHelper::getOption("vegaType", null, $fromArray);
        
        if ($name) {
            $vegaProperty->name = $name;
        }
        
        if ($position) {
            $vegaProperty->position = $position;
        }

        if ($edit && ($vegaType || $primitiveType)) {
            throw new Mira_Core_Exception_BadRequestException("Cannot change a property's type", "You have to remove it and create a new one");
        } elseif ($vegaType || $primitiveType)  { 
            if (!$vegaType) {
                $vegaProperty->type = $primitiveType;
            } else {
                if (Mira_Utils_String::isId($vegaType)) {
                    $vegaType = $this->api->tid($vegaType);
                } else {
                    $vegaType = $this->api->tname($vegaType);
                }
                if ($vegaType) {
                    $vegaProperty->type = $vegaType;
                } else {
                    throw new Mira_Core_Exception_BadRequestException("Property type does not exist $vegaType");
                }
            }
        }
    }
}
