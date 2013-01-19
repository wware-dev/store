<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_Common
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_Common_Helper_Debug extends Mage_Core_Helper_Abstract
{
    /**
     * send to Firebug
     *
     * @param $content
     */
    public function sendToFirebug($content)
    {
        $writer = new Zend_Log_Writer_Firebug();
        $logger = new Zend_Log($writer);

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();
        $channel = Zend_Wildfire_Channel_HttpHeaders::getInstance();
        $channel->setRequest($request);
        $channel->setResponse($response);

        // Start output buffering
        ob_start();
        $logger->log($content, Zend_Log::INFO);

        // Flush log data to browser
        $channel->flush();
        $response->sendHeaders();
    }
}