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
 * @subpackage Command
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * Regroups all processes for user management (send validation email, confirmation email, ...)
 * 
 * For more info on our Event framework, have a look at {@link Mira_Utils_Event_CommandBus}.
 * 
 * @category   Mira
 * @package    Mira_Core
 * @subpackage Command
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_Command_UserCommand extends Mira_Utils_Event_AbstractCommand
{
    /**
     * User has just created an account, this function sends him an email with a link he has
     * to click to check the validity of his address. 
     * 
     * @param UserEvent $event
     * @return Zend_Mail
     */
    public function sendValidationEmail($event)
    {
        $user = $event->user;
        $email = $user->email;
        $token = $user->token;
        
        $config = Zend_Registry::get(Mira_Core_Constants::REG_CONFIG);
        $configFiles = $config->mail;
        $configFiles = $configFiles->toArray();
        
        if($configFiles["validation"]) {
            $mail = new Zend_Mail ();
            $mail->setFrom ($configFiles['from'], 'Vega');
            $path = $configFiles['url'];
            $mail->addTo ($email, $email);
            $mail->setSubject ('Please validate your email');
            $mail->setBodyHtml ("<html><head></head>
                                 <body style='font-size:8px'>
                                 <p>Welcome To VEGA</p>
                                 <p>Confirm your email address enter the following link: 
                                 <a href= '" . $path . "/validate/code/" . $token . "/email/" . $email . "'>here</a></p>
                                 Thanks!! and Enjoy it
                                 </body></html>");
            return $mail->send ();
        } 
    }
    
    /**
     * After email validation, this sends a simple confirmation email.
     * 
     * @param UserEvent $event
     * @return Zend_Mail
     */
    public function sendConfirmationEmail($event)
    {
        $user = $event->user;
        $email = $user->email;
        $token = $user->token;
        
        $config = Zend_Registry::get(Mira_Core_Constants::REG_CONFIG);
        $configFiles = $config->mail;
        $configFiles = $configFiles->toArray();
        
        if($configFiles["validation"]) {
            
            $mail = new Zend_Mail ();
            $mail->setFrom ($configFiles['from'], 'Vega');
            $path = $configFiles['url'];
            $mail->addTo ($email, $email);
            $mail->setSubject ('Vega registration confirmation');
            $mail->setBodyHtml ("<html><head></head>
                                 <body style='font-size:8px'>
                                 <p>Thank you for your registration, hope you will enjoy Vega!</p>
                                 </body></html>");
            return $mail->send ();
            
        } 
    }
    
    /**
     * User has forgot an email, he can set a new one by going to an url.
     * This page will ask him a code he will find in this email.
     * 
     * @param UserEvent $event
     * @return Zend_Mail
     */
    public function sendRecoverPasswordEmail($event) 
    {
        $user = $event->user;
        $email = $user->email;
        $token = $user->token;
        
        $config = Zend_Registry::get(Mira_Core_Constants::REG_CONFIG);
        $configFiles = $config->mail;
        $configFiles = $configFiles->toArray();
        
        if($user && $configFiles["validation"]) {
            
            $mail = new Zend_Mail ();
            $mail->setFrom ($configFiles['from'], 'Vega');
            $path = $configFiles['url'];
            $mail->addTo ($email, $email);
            $mail->setSubject ('Vega registration confirmation');
            $mail->setBodyHtml ("<html><head></head>
            	                 <body style='font-size:8px'>
                	             <p>VEGA</p>
                    	         <p>To Recover your password enter the following link: 
                        	     <a href= '" . $path . "/passwordrecovery/code/" . $token . "/email/" . $email . "'>here</a></p>
                             	</body></html>");
            return $mail->send ();
            
        } 
    }
}