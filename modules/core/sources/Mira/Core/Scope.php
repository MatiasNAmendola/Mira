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
 * @package    Mira_Core
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * Describes the RBAC (Role Based Access Control) of an object (either a vega or
 * a vegatype). This is not astrict implementation of the RBAC. An object can either
 *   - define its user <-> role pairs (role being either "editor" or "viewer")
 *   - inherit its RBAC from another scope object (thus any modification to that other
 *     object will impact this object) 
 * 
 * @category   Mira
 * @package    Mira_Core
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_Scope extends Mira_Utils_Pretty_Row
{
    /**
     * this is used by AMF to specify remote AS classname
     * @access private
     * @var string
     */
    public $_explicitType = "com.vega.core.api.vega.Scope";
    
    // can be either a Mira_Core_Vega or Mira_Core_VegaType 
    public $scopeOwner;
    
    const ROLE_VIEWER = "viewer";
    const ROLE_EDITOR = "editor";
    
    private $_lastScopeId;
    private $_userId2role;
    private $_dirty = false;
    
 	public function __construct($config)
 	{
 	    // this table exposed properties
 	    $properties = array();
 	    $properties[] = new Mira_Utils_Pretty_Property_Basic($this, "id", false, true, null, "id_scp");
 	    $properties[] = new Mira_Utils_Pretty_Property_Basic($this, "inheritFrom", false, true, null, "inherit_from_scp");
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "userRoles", false, true, "userRoles");
 	    parent::__construct($config, $properties);
 	}
    
 	/**
	 * init() function is called after a Table->createNew() or a Table->fetchRow() is called
	 * @access private
     */
    public function init ()
    {
        parent::init();
        if ($this->id_scp != null) {
            // is retrieved from db
            $this->initializeScope();
        } else {
            // is new
            $this->_userId2role = array();
        }
    }
    
    /**
     * When we intialize we don't need to refresh the inherited scope
     * since it was already set at the last save. However, whenever the 
     * inherit_from_scp is changed, we have to go and look for the new one.
     * 
     * @access private
     */
    private function initializeScope() 
    {
        $this->_userId2role = array();
        
        // @var Zend_Db_Adapter
        $db = $this->getTable()->getAdapter();
        
        // @var Zend_Db_Select
        $sel = $db->select()
                  ->from(Mira_Core_Constants::TABLE_SCOPE_CUSTOM)
                  ->where("id_scp_scc = " . $this->id_scp);
            
        // @var Zend_Db_Table_Rowset
        $rowset = $db->fetchAll($sel);
        
        foreach ($rowset as $row) {
            $userId = $row->id_usr_scc;
            $this->_userId2role[$userId] = $row->role_scc;
        }
    }
    
    /**
     * @return boolean
     */
    public function isInheriting()
    {
        return $this->inherit_from_scp != null;
    }
    
    /**
     * @return boolean true if this scope has some changes to be saved
     */
 	public function isDirty()
 	{
 	    $mf = $this->_modifiedFields;
 	    foreach ($mf as $field => $isDirty) {
 	        if ($isDirty) return true;
 	    }
 	    return $this->_dirty;
 	}
    
    /**
     * Get all users with this role
     * 
     * @return array Associative array of Mira_Core_User <-> "editor" | "viewer"
     */
    public function getUserRoles()
    {
        $ret = array();
        foreach ($this->_userId2role as $userId => $role) {
            $r = new Mira_Core_UserRole();
            $r->user = Zend_Registry::get(Mira_Core_Constants::REG_API)->uid($userId);
            $r->role = $role;
            $ret[] = $r;
        }
        return $ret;
    }
    
    /**
     * Save
     */
    public function save()
    {
        // @var Zend_Db_Adapter
        $db = $this->getTable()->getAdapter();
        // @var boolean
        $isNew = ($this->_lastScopeId == null);
        
        if ($this->isInheriting()) {
            // clean any custom scope already assigned
            // and go to refresh them
            $this->_userId2role = array();
            
            $ancestor = $this->findScopeAncestor();
            
            // copy ancestor scope
            $sel = $db->select()
                      ->from(Mira_Core_Constants::TABLE_SCOPE_CUSTOM)
                      ->where("id_scp_scc = " . $ancestor->id_scp);
            $rs = $db->fetchAll($sel);
            foreach ($rs as $userRole) {
                $this->_userId2role[$userRole->id_usr_scc] = $userRole->role_scc;
            }
        }
        
        // save the current row
        parent::save();

        // delete previous custom scopes
        $db->delete(Mira_Core_Constants::TABLE_SCOPE_CUSTOM,
            "id_scp_scc = " . $this->id_scp); 
        // now save the custom scope if necessary
        foreach ($this->_userId2role as $userId => $userRole) {
            $db->insert(Mira_Core_Constants::TABLE_SCOPE_CUSTOM, 
                        array(        
                            "id_scp_scc" => $this->id_scp,
                            "id_usr_scc" => $userId,
                            "role_scc" => $userRole
                        ));
        }
        
        // we have also to update inheriting children
        // are this update versioned for them ??
        if (!$isNew) {
            $this->moveDescendants($this->_lastScopeId);
        }
        $this->_dirty = false;
        
        return $this->id_scp;
    }
    
    /**
     * When current scoped is altered, we need to tell all children
     * (scopes inheriting from this) to update themselves.
     * 
     * (Scope is copied in the whole tree of descendance rather than 
     * being computed each time - which would be to CPU/DB intensive)
     * 
     * @access private
     * @param integer $fromScopeId
     */
    public function moveDescendants($fromScopeId) 
    {
        // @var Zend_Db_Adapter
        $db = $this->getTable()->getAdapter();
        
        // relink descendants inhereting from $fromScopeId to current scopeId
        $nb = $db->update(Mira_Core_Constants::TABLE_SCOPE,
                    array("inherit_from_scp" => $this->id_scp),
            		"inherit_from_scp = " . $fromScopeId);
        
        // then dispatch this node's scope into children caches
        $this->updateChildrenScopes($this->id_scp, $db);          
    }
    
    /**
     * @access private
     * @param integer $currentId
     * @param Zend_Db_Adapter $db
     * @return Zend_Db_Select 
     */
    private function updateChildrenScopes($currentId, $db = null) 
    {
        if (!$db) $db = $this->getTable()->getAdapter();
            
        // find children
        // @var Zend_Db_Select;
        $sel = $db->select();
        $sel->from(Mira_Core_Constants::TABLE_SCOPE)
            ->where("inherit_from_scp = " . $currentId);
        $inheritingScopes = $db->fetchAll($sel);
        
        foreach ($inheritingScopes as $inheritingScope) {
            // @todo factorize this out of this foreach
            $db->update(Mira_Core_Constants::TABLE_SCOPE, 
                array(	"inherit_from_scp" => ($currentId == $this->_lastScopeId ? $this->id_scp : $inheritingScope->inherit_from_scp)),
                "id_scp = " . $inheritingScope->id_scp);
            // delete previous scopes
            $db->delete(Mira_Core_Constants::TABLE_SCOPE_CUSTOM,
                "id_scp_scc = " . $inheritingScope->id_scp); 
            // copy custom scopes
            foreach ($this->_userId2role as $userId => $userRole) {
                $db->insert(Mira_Core_Constants::TABLE_SCOPE_CUSTOM, 
                    array(        
                        "id_scp_scc" => $inheritingScope->id_scp,
                        "id_usr_scc" => $userId,
                        "role_scc" => $userRole
                    ));
            }
            
            // recursive
            $this->updateChildrenScopes($inheritingScope->id_scp, $db);
        }
    }
    
    /**
     * Which is the top most scope this object is inheriting
     * 
     * @return Mira_Core_Scope
     */
    public function findScopeAncestor()
    {
        if (!$this->isInheriting()) return;
        
        // @var Zend_Db_Adapter
        $db = $this->getTable()->getAdapter();
        
        $curId = $this->inherit_from_scp;
        do {
            $parent = $this->getTable()->fetchRow("id_scp = " . $curId);
            $curId = $parent->inherit_from_scp;
        } while ($curId != null);
        
        return $parent;
    }
    
    /**
     * @param string | Mira_Core_User $user
     * @return string 
     */
    public function getUserRole($user)
    {
        $userId = intval(($user instanceof Mira_Core_User) ? $user->id : $user);
        if (isset($this->_userId2role[$userId])) {
            return $this->_userId2role[$userId];
        } else {
            return null;
        }
    }
    
    /**
     * @param Mira_Core_Scope | integer $scope
     */
    public function setInheritFrom($scope)
    {
        $this->_dirty = true;
        if ($scope instanceof Mira_Core_Scope) {
            $this->inherit_from_scp = $scope->id_scp;
        } else {
            $this->inherit_from_scp = $scope;
        }
    }
    
    /**
     * @param Mira_Core_User | integer $user
     */
    public function removeUserRole($user)
    {
        $this->_dirty = true;
        $userId = ($user instanceof Mira_Core_User) ? $user->id : $user;
        unset($this->_userId2role[$userId]);        
    }
    
    /**
     * @param Mira_Core_User | integer $user
     * @param string $role "viewer" or "editor"
     * @return Zend_Db_Select 
     */
    public function addUserRole($user, $role) 
    {
        $userId = ($user instanceof Mira_Core_User) ? $user->id : $user;
        if ($userId && ($role == self::ROLE_EDITOR || $role == self::ROLE_VIEWER)) {
            $this->_dirty = true;
            $this->_userId2role[intval($userId)] = $role;
        } else {
            throw new Mira_Core_Exception_BadRequestException("Role $role does not exist or User was not defined", "expected 'editor' or 'viewer'"); 
        } 
    }
    
    /**
     * @return Mira_Core_Scope
     */
    public function duplicate()
    {
        // @var Mira_Core_Scope
        $newScope = $this->getTable()->createRow();
        $newScope->_lastScopeId = $this->id_scp;
        $newScope->inherit_from_scp = $this->inherit_from_scp;
        $newScope->_userId2role = $this->_userId2role;
        
        return $newScope;
    }
}