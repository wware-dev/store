<?php
class ECGiken_Gmo_Block_Form_Cc extends Mage_Payment_Block_Form_Cc {

    protected function _construct() {
        if (Mage::getStoreConfig('payment/ecggmo_cc/test')) {
            Mage_Payment_Block_Form::_construct();
            $this->setTemplate('ecggmo/form/cc.phtml');
        } else {
            parent::_construct();
        }
    }

    public function isTest() {
        return Mage::getStoreConfig('payment/ecggmo_cc/test');
    }
}
