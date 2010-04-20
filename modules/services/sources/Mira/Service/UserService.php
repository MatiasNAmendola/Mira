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
class Mira_Service_UserService extends Mira_Service_Abstract
{
 	/**
 	 * Create a new user
 	 * 
 	 * $contactProperties is an associative array containing extra info of this user
 	 * "first name" | "last name" | "phone" ...
 	 * 
 	 * @param string $email
 	 * @param string $password
 	 * @param array $contactProperties
 	 * 
 	 * @return Mira_Core_User
 	 */
 	public function createUser($email, $password, $contactProperties = array())
 	{
 	    $user = $this->api->createUser($email, $password);
 	    $contact = $user->contact;
 	    foreach ($contactProperties as $pName => $pValue) {
 	        $contact->$pName = $pValue;
 	    }
 	    $user->save();
 	    
 	    return $user;
 	}
 	
 	/**
 	 * Update an existing user
 	 * 
 	 * $contactProperties is an associative array containing properties
 	 * to update.
 	 * 
 	 * @param string $email
 	 * @param array $contactProperties
 	 * 
 	 * @return Mira_Core_User
 	 */
 	public function updateUser($email, $contactProperties)
 	{
 	    $user = $this->api->uemail($email);
 	    if (!$user) {
 	        throw new Mira_Core_Exception_NotFoundException("User $email");
 	    }
 	    $contact = $user->contact;
 	    foreach ($contactProperties as $pName => $pValue) {
 	        $contact->$pName = $pValue;
 	    }
 	    $user->save();
 	    
 	    return $user;
 	}
 	
    /**
     * Find user by id
     * 
     * @param integer $id
     * @return Mira_Core_User
     */
    public function findById($id)
    {
        return $this->api->uid($id);
    }
    
    /**
     * Find user by email
     * 
     * @param string $email
     * @return Mira_Core_User
     */
    public function findByEmail($email)
    {
        return $this->api->uemail($email);
    }
    
    /**
     * Generic find method
     * 
     * Filters available:
     * - id::integer
     * - email::string
     * 
     * Options available:
     * - page_offset::integer for paging, the result index to start from. default = 0
     * - page_count::integer number of results to retrieve. default = 50
     * - order_by::string "email" | "id" | "first name" | "last name"... default = "first name"
     * - order_asc::bolean default = true
     * - lazy::bolean default = true
     * 
     * @param $options
     * @return array
     */
 	public function find($filters, $options)
 	{
 	    $select = $this->api->selectUsers();
 	    
 	    // read filters
 	    $id = Mira_Utils_OptionsHelper::getOption("id", null, $filters);
 	    $email = Mira_Utils_OptionsHelper::getOption("email", null, $filters);
 	    // read options
 	    $offset = Mira_Utils_OptionsHelper::getOption("page_offset", 0, $options);
 	    $count = Mira_Utils_OptionsHelper::getOption("page_count", 50, $options);
 	    $orderBy = Mira_Utils_OptionsHelper::getOption("order_by", null, $options);
 	    $orderAsc = Mira_Utils_OptionsHelper::getOption("order_asc", true, $options);
 	    $lazy = Mira_Utils_OptionsHelper::getOption("lazy", true, $options);
 	    
 	    // apply params
 	    if ($id) $select->where("id", $id);
 	    if ($email) $select->where("email", $email);
 	    $select->limit($count, $offset);
 	    if ($orderBy) $select->order($orderBy, $orderAsc);
 	    
 	    // fetch
 	    $results = $select->fetchAll($lazy);
 	    return $results;
 	}
 	
 	/**
 	 * Send an email with a token to validate this user's account
 	 * 
 	 * @param string $email
     * @return boolean true if user found and email sent
 	 */
 	public function sendValidationEmail($email)
 	{
 	    $user = $this->api->uemail($email);
 	    if (!$user) {
 	        throw new Mira_Core_Exception_NotFoundException("User $email");
 	    }
 	    $this->api->sendValidationEmail($user);
 	}
 	
 	/**
 	 * Send an email with a token to change this user's password
 	 * 
 	 * @param string $email
     * @return boolean true if user found and email sent
 	 */
 	public function sendRecoverPasswordEmail($email)
 	{
 	    $user = $this->api->uemail($email);
 	    if (!$user) {
 	        throw new Mira_Core_Exception_NotFoundException("User $email");
 	    }
 	    $this->api->sendRecoverPasswordEmail($user);
 	}
}