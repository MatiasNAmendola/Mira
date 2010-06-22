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
 * @subpackage Select
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * See README.md or http://github.com/getvega/mira for selects usage.
 * 
 * @category   Mira
 * @package    Mira_Core
 * @subpackage Select
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_Select_UserSelect extends Mira_Core_Select_Abstract
{
    const SELECTOR_EMAIL     = "email";
    const SELECTOR_TOKEN     = "token";
    const SELECTOR_ACCOUNT   = "account";
    
    const FQN_VEGATYPE = "Mira_Core_Contact";
    
    /**
     * @var Zend_Db_Table_Abstract
     */
    protected $table;
    
    protected $store_idWhere;
    protected $store_emailWhere;    
    protected $store_tokenWhere;    
    protected $store_accountWhere;
    protected $store_propertyWheres = array();
    
    protected $store_options = array();
    
    
    // ###################################################
    // API
    // ###################################################
    
    /**
     * {@inheritdoc}
     * <b>Warning: Mira_Core_Select_UserSelect does not support revision, name and security selectors.</b>
     * 
     * Mira_Core_Select_UserSelect special selectors:
     * <ol>
     * <li>
     * 	<ul>
     * 		<li>$selector: {@link SELECTOR_EMAIL}</li>
     * 		<li>$value: any string | expression</li>
     * 		<li>$config: "strict" (default) | "permissive" | "expression"</li>
     * 	</ul>
     * </li>
     * <li>
     * 	<ul>
     * 		<li>$selector: {@link SELECTOR_TOKEN} - the unique token sent to user by email to check his email or reset his password</li>
     * 		<li>$value: any string</li>
     * 	</ul>
     * </li>
     * <li>
     * 	<ul>
     * 		<li>$selector: {@link SELECTOR_ACCOUNT} - the status of this user's account (whether his email has been validated or not)</li>
     * 		<li>$value: "validated" (default) | "validating" | "both"</li>
     * 	</ul>
     * </li>
     * </ol>
     * @param string|id $selector
     * @param mixed $value
     * @param mixed $config
     * @return VegaSelect
     */
    public function where($selector, $value = null, $config = null) 
    {
        $args = array($selector, $value, $config);
        switch ($selector) {
            case self::SELECTOR_ID:
                $this->store_idWhere = $args;
                break;
            case self::SELECTOR_EMAIL:
                $this->store_emailWhere = $args;
                break;
            case self::SELECTOR_TOKEN:
                $this->store_tokenWhere = $args;
                break;
            case self::SELECTOR_ACCOUNT:
                $this->store_accountWhere = $args;
                break;
            default: 
                // any other filter will be used
                // against this users' contact vegas.
                $this->store_propertyWheres[] = $args;
                break;
        }
        return $this;
    }
    
    // ###################################################
    // INTERNALS
    // ###################################################
    
    /**
     * {@inheritdoc}
     */
    protected function prepareSelect($alias = null)
    {
        // @var Zend_Db_Table_Select
        $select = parent::prepareSelect($alias ? $alias : Mira_Core_Constants::TABLE_USER); 
        $select->from(Mira_Core_Constants::TABLE_USER);
        
        // all renders
        $select = $this->renderIdWhere($this->store_idWhere, $select);
        $select = $this->renderEmailWhere($this->store_emailWhere, $select);
        $select = $this->renderTokenWhere($this->store_tokenWhere, $select);
        $select = $this->renderAccountWhere($this->store_accountWhere, $select);
        $select = $this->renderPropertyWheres($this->store_propertyWheres, $select);
        
        if (!$select) return null;
        $select = $this->renderOptions($this->store_options, $select);
        return $select;
    }
    
    /**
     * {@inheritdoc}
     */
    public function limit($count = 50, $offset = 0)
    {
        $this->store_options["offset"] = $offset;
        $this->store_options["count"] = $count;
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function order($byProperty, $ascending = true) 
    {
        $this->store_options["orderBy"] = $byProperty;
        $this->store_options["orderAsc"] = $ascending;
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function buildReferenceFromRow($row) 
    {
        // @var Mira_Core_Reference
        $ret = new Mira_Core_Reference();
        $ret->id = $row["id_usr"];
        $ret->uid = Mira_Core_User::getUID($row["id_usr"]);
        $ret->name = $row["email_usr"];
        $ret->type = "user";
        return $ret;
    }
    
    // ###################################################
    // RENDERERS
    // ###################################################
    
    /**
     * @param array $args
     * @param Zend_Db_Table_Select $select
     * @return Zend_Db_Table_Select
     */
    protected function renderIdWhere($args, $select)
    {
        if (!$args) return $select;
        list ($selector, $value, $config) = $args;
        $select->where($this->currentAlias . ".id_usr = $value");    
        return $select;        
    }
    
    /**
     * @param array $args
     * @param Zend_Db_Table_Select $select
     * @return Zend_Db_Table_Select
     */
    protected function renderTokenWhere($args, $select)
    {
        if (!$args) return $select;
        list ($selector, $value, $config) = $args;
        $select->where($this->currentAlias . ".token_usr = \"$value\"");    
        return $select;        
    }
    
    /**
     * @param array $args
     * @param Zend_Db_Table_Select $select
     * @return Zend_Db_Table_Select
     */
    protected function renderAccountWhere($args, $select)
    {
        if (!$args) return $select;
        list ($selector, $value, $config) = $args;
        if ($value == "validated")
            $select->where($this->currentAlias . ".account_status_usr = 'validated'");
        elseif ($value == "validating")    
            $select->where($this->currentAlias . ".account_status_usr = 'validating'");
        // else account is both, so we don't place any filter
        return $select;        
    }
    
    /**
     * @param array $args
     * @param Zend_Db_Table_Select $select
     * @return Zend_Db_Table_Select
     */
    protected function renderEmailWhere($args, $select)
    {
        if (!$args) return $select;
        list ($selector, $value, $config) = $args;
        if ($config == "permissive")
            $select->where($this->currentAlias . ".email_usr LIKE '%$value%'");
        elseif ($config == "expression")
            $select->where($this->currentAlias . ".email_usr $value");
        else
            $select->where($this->currentAlias . ".email_usr = ?", $value);    
        return $select;        
    }
    
    /**
     * @param array $multipleArgs
     * @param Zend_Db_Table_Select $select
     * @return Zend_Db_Table_Select
     */
    protected function renderPropertyWheres($multipleArgs, $select)
    {
        // those filters act on $user->contact properties
        // so we use an internal Mira_Core_Select_VegaSelect for this purpose
        $selectVega = $this->api->selectVegas();
        
        $currentContactAlias = "contact_" . uniqid();
        
        if(count($multipleArgs) === 0 || !$multipleArgs) {
            $select->joinInner(
                array(  $currentContactAlias => Mira_Core_Constants::TABLE_VEGA), 
				"$currentContactAlias.id_vg = $this->currentAlias.id_vg_usr AND $currentContactAlias.status_vg = 'enabled'");
            return $select;
        }
        
        $typeSelect = $this->api->selectVegaTypes()
                                ->where("fqn", "Mira_Core_Contact");
        $selectVega->where("vegaType", $typeSelect);
        
        foreach($multipleArgs as $a) {
            list ($selector, $value, $config) = $a;
            $selectVega->where($selector, $value, $config);
        }
        
        $select->joinInner(
            array($currentContactAlias => $selectVega->prepareSelect()), 
            "$currentContactAlias.id_vg = $this->currentAlias.id_vg_usr");
        return $select;
    }
    
    /**
     * @param array $options
     * @param Zend_Db_Table_Select $select
     * @return Zend_Db_Table_Select
     */
    protected function renderOptions ($options, $select)
    {
        $offset = Mira_Utils_OptionsHelper::getOption("offset", 0, $options);
        $count = Mira_Utils_OptionsHelper::getOption("count", 50, $options);
        $orderBy = Mira_Utils_OptionsHelper::getOption("orderBy", "email", $options);
        $ascending = Mira_Utils_OptionsHelper::getOption("orderAsc", true, $options);
        
        $select->limit($count, $offset);
        $orderBy = $this->getColumnName($orderBy);
        $select->order($orderBy . " " . ($ascending ? "ASC" : "DESC"));
        
        return $select;
    }
    
    // ###################################################
    // PRIVATE
    // ###################################################
    
    /**
     * @access private
     */
    private function getColumnName($prettyName) 
    {
        if ($prettyName == "email") return "email_usr";
        if ($prettyName == "creationDate") return "date_created_usr";
        return $prettyName;
    }
}