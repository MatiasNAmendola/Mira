<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Mira_Gdata_Contacts_Extension_Primary
{
	/**
	 * Whether or not this element is marked as the primary one amongst it's peers
	 *
	 * @return boolean True if primary, false otherwise
	 */
	public function isPrimary();

	/**
	 * Changes the flag for whether this is the primary e-mail for a contact.
	 * Depending on implementation this may or may not unset the primary flag for
	 * other elements.
	 *
	 * @param boolean $value True to set as primary, false otherwise.
	 */
	public function setPrimary($value);

	/**
	 * Returns the label used to prevent multiple primaries.
	 *
	 * All objects with the same label may only have one "primary" item between
	 * them, e.g. "email" elements.
	 *
	 * @return string 	A label unique to a set of items where only one item may
	 * 					be set as primary
	 */
	public function getPrimaryLabel();
}
