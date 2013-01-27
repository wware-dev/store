<?php
class ECGiken_Gmo_Model_Cc_Source_Cctype {
    public function toOptionArray() {
        return array(
            array('value' => 'VI','label' => Mage::helper('ecggmo')->__('Visa')),
            array('value' => 'MC','label' => Mage::helper('ecggmo')->__('Master Card')),
            array('value' => 'AE','label' => Mage::helper('ecggmo')->__('American Express')),
            array('value' => 'DI','label' => Mage::helper('ecggmo')->__('Discover')),
            array('value' => 'JCB','label' => Mage::helper('ecggmo')->__('JCB')),
        );
    }
}
