<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Model_Updates extends Mage_AdminNotification_Model_Feed
{
    const FEED_URL = 'store.fooman.co.nz/news/';

    /**
     * return url of feed
     *
     * @return string
     */
    public function getFeedUrl()
    {
        if (is_null($this->_feedUrl)) {
            $this->_feedUrl = (Mage::getStoreConfigFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://')
            . self::FEED_URL;
        }
        return $this->_feedUrl;
    }

    /**
     * return time of last run
     *
     * @return int|mixed
     */
    public function getLastUpdate()
    {
        return Mage::app()->loadCache('fooman_notifications_lastcheck');
    }

    /**
     * save time of last run
     *
     * @return Fooman_PdfCustomiser_Model_Updates
     */
    public function setLastUpdate()
    {
        Mage::app()->saveCache(time(), 'fooman_notifications_lastcheck');
        return $this;
    }
}