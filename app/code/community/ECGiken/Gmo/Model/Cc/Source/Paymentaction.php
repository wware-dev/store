<?php
class ECGiken_Gmo_Model_Cc_Source_Paymentaction {

    public function toOptionArray() {
        $select = array(
            array( 'value' => 'authorize','label' => Mage::helper('ecggmo')->__('Authorize') ),
            array( 'value' => 'authorize_capture','label' => Mage::helper('ecggmo')->__('Authorize and capture') )
        );
        return $select;
    }
}
