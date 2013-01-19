<?php
/**
 * @copyright   Copyright (c) 2011 Amasty (http://www.amasty.com)
 */ 
class Amasty_Oaction_Model_Source_Carriers {

    public function toOptionArray() 
    {
        $options = array();
        $options[] = array(
            'value' => 'custom',
            'label' => Mage::helper('amoaction')->__('Custom')
        );
        
        foreach (Mage::getSingleton('shipping/config')->getAllCarriers() as $k => $carrier) {
            if ($carrier->isTrackingAvailable()) {
                $options[] = array(
                    'value' => $k,
                    'label' => $carrier->getConfigData('title'),
                );
            }             
        }
        
        return $options;
    }
}