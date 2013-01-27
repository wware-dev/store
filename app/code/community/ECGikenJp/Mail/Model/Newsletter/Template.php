<?php
/**
 * ECGiken_Price
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */

class ECGikenJp_Mail_Model_Newsletter_Template extends Mage_Newsletter_Model_Template {

    public function getMail() {
        if (is_null($this->_mail)) {
            $this->_mail = Mage::getModel('ecgjpmail/mail');
        }
        return $this->_mail;
    }
}
