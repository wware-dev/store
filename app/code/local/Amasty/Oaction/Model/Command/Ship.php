<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Oaction
*/
class Amasty_Oaction_Model_Command_Ship extends Amasty_Oaction_Model_Command_Abstract
{ 
    public function __construct($type)
    {
        parent::__construct($type);
        $this->_label = '出荷';
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
        $comment = $hlp->__('Shipment created');
        
        $carriers = $this->_getPost('carrier');
        $numbers  = $this->_getPost('tracking');
        
        
        foreach ($ids as $id){
            $order     = Mage::getModel('sales/order')->load($id);
            $orderCode = $order->getIncrementId();
            
            try {
                $shipmentCode = Mage::getModel('sales/order_shipment_api_v2')
                    ->create($orderCode, array(), $comment, false, false); 
                    
                //add tracking if any
                if ($shipmentCode && !empty($carriers[$id]) && !empty($numbers[$id])) {
                    Mage::getModel('sales/order_shipment_api_v2')->addTrack(
                        $shipmentCode,                                                           
                        $carriers[$id],                                                        
                        $this->_getTitleByCode($carriers[$id]),         
                        $numbers[$id]                                                      
                    );                        
                }                    
                
                //update status    
                $status = Mage::getStoreConfig('amoaction/ship/status', $order->getStoreId());    
                if ($status)  
                    Mage::getModel('sales/order_api')->addComment($orderCode, $status, '', false);    
                    
                $notifyCustomer = Mage::getStoreConfig('amoaction/ship/notify', $order->getStoreId());        
                if ($shipmentCode && $notifyCustomer){ 
                    $shipment = Mage::getModel('sales/order_shipment')
                        ->loadByIncrementId($shipmentCode);
                        
                    if ($shipment->getId()) {
                        $shipment
                            ->setEmailSent(true)
                            ->sendEmail(true)
                            ->save();
                    }
                    $shipment = null;
                    unset($shipment);
                } 
                
                ++$numAffectedOrders;           
            }
            catch (Exception $e) {
                $err = $e->getCustomMessage() ? $e->getCustomMessage() : $e->getMessage();
                $this->_errors[] = $hlp->__(
                    'Can not ship order #%s: %s', $orderCode, $err);
            }
            $order = null;
            unset($order); 
        }
        
        if ($numAffectedOrders){
            $success = $hlp->__('Total of %d order(s) have been successfully shipped.', 
                $numAffectedOrders);
        }         
        
        return $success; 
    }
    
    
    private function _getPost($field)
    {
        $data = array();
        $post = explode(',', Mage::app()->getRequest()->getPost($field));
        foreach ($post as $line) {
            list($id, $code) = explode('|', $line);
            $data[$id] = $code;
        }
        return $data;
    }
   
    private function _getTitleByCode($code)
    {
        $title = Mage::getStoreConfig('carriers/' . $code . '/title');
        if ($code == 'custom') {
            $title = Mage::getStoreConfig('amoaction/ship/title');
        } 
        if (!$title)
            $title = $code;
            
        return $title;
    }     
     
}