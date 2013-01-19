<?php
/**
 * @copyright   Copyright (c) 2011 Amasty (http://www.amasty.com)
 */ 
class Amasty_Oaction_Model_Source_Commands
{
    public function toOptionArray()
    {
        $options = array();
        
        // magento wants at least one option to be selected
        $options[] = array(
            'value' => '',
            'label' => '',
            
        ); 
        $types = array('invoice', 'invoicecapture', 'invoiceship', 'invoicecaptureship', 'capture', 'ship', 'status');        
        foreach ($types as $type){
            $command = Amasty_Oaction_Model_Command_Abstract::factory($type);  
            $options[] = array(
                'value' => $type,
                'label' => Mage::helper('amoaction')->__($command->getLabel()),
                
            );
        }   
        return $options;
    }
}