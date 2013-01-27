<?php
/**
 * @copyright   Copyright (c) 2011 Amasty (http://www.amasty.com)
 */ 
class Amasty_Oaction_Model_Source_Statuses {

    public function toOptionArray() 
    {
        $options = array();
        $options[] = array(
            'value' => '',
            'label' => Mage::helper('amoaction')->__('Magento Default')
        );
        
        foreach (Mage::getModel('sales/order_config')->getStatuses() as $k => $v) {
            $options[] = array(
                'value' => $k,
                'label' => $v
            );
        }
        
        return $options;
    }

}
