<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_EmailAttachments
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_EmailAttachments_Model_Updates extends Mage_AdminNotification_Model_Feed
{
    const RSS_UPDATES_URL = 'store.fooman.co.nz/news/cat/emailattachments/updates';
    const XML_GET_EMAILATTACHMENTS_UPDATES_PATH = 'foomancommon/notifications/enableemailattachmentsupdates';

    /**
     * return url of feed
     *
     * @return string
     */
    public function getFeedUrl()
    {
        if (is_null($this->_feedUrl)) {
            $this->_feedUrl = (Mage::getStoreConfigFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://')
            . self::RSS_UPDATES_URL;
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
        return Mage::app()->loadCache('emailattachments_notifications_lastcheck');
    }

    /**
     * save time of last run
     *
     * @return Fooman_EmailAttachments_Model_Updates
     */
    public function setLastUpdate()
    {
        Mage::app()->saveCache(time(), 'emailattachments_notifications_lastcheck');
        return $this;
    }

    /**
     * check for updates
     *
     * @return Fooman_EmailAttachments_Model_Updates
     */
    public function checkUpdate()
    {
        if (Mage::getStoreConfigFlag(self::XML_GET_EMAILATTACHMENTS_UPDATES_PATH)) {
            Mage::log('Looking for updates - Fooman EmailAttachments');
            parent::checkUpdate();
        }
    }

}