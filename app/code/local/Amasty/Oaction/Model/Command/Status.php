<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Oaction
*/
class Amasty_Oaction_Model_Command_Status extends Amasty_Oaction_Model_Command_Abstract
{ 
    public function __construct($type)
    {
        parent::__construct($type);
        $this->_label      = 'ステータス変更';
        $this->_fieldLabel = '⇒';
    } 
        
    /**
     * Executes the command
     *
     * @param array $ids product ids
     * @param string $val field value
     * @return string success message if any
     */   
    public function execute($ids, $val)
    {     
        $success = parent::execute($ids, $val);
        
        $numAffectedOrders = 0;

        $hlp = Mage::helper('amoaction'); 
        
        foreach ($ids as $id){
            $order = Mage::getModel('sales/order')->load($id);
            $orderCode = $order->getIncrementId();
            try {
                Mage::getModel('sales/order_api')->addComment($orderCode, $val, '', false);
                ++$numAffectedOrders;          
            }
            catch (Exception $e) {
                $err = $e->getCustomMessage() ? $e->getCustomMessage() : $e->getMessage();
                $this->_errors[] = $hlp->__(
                    'Can not update order #%s: %s', $orderCode, $err);
            }
            $order = null;
            unset($order); 
        }
        
        if ($numAffectedOrders){
            $success = $hlp->__('Total of %d order(s) have been successfully updated.', 
                $numAffectedOrders);
        }         
        
        return $success; 
    }

    protected function _getValueField($title)
    {
        $field = array('amoaction_value' => array(
            'name'   => 'amoaction_value',
            'type'   => 'select',
            'class'  => 'required-entry',
            'label'  => $title,
            'values' => Mage::getModel('sales/order_config')->getStatuses(),
        )); 
        return $field;       
    }    
}