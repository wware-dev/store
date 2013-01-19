<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Oaction
*/
class Amasty_Oaction_Model_Command_Invoice extends Amasty_Oaction_Model_Command_Abstract
{ 
    public function __construct($type)
    {
        parent::__construct($type);
        $this->_label = '領収';
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
        $comment = $hlp->__('Invoice created');
        
        foreach ($ids as $id){
            $order     = Mage::getModel('sales/order')->load($id);
            $orderCode = $order->getIncrementId();
            
            try {
                $invoiceCode = Mage::getModel('sales/order_invoice_api_v2')
                    ->create($orderCode, array(), $comment, false, false); 
                    
                $status = Mage::getStoreConfig('amoaction/invoice/status', $order->getStoreId());
                if ($status)    
                    Mage::getModel('sales/order_api')->addComment($orderCode, $status, '', false);                      
                    
                $notifyCustomer = Mage::getStoreConfig('amoaction/invoice/notify', $order->getStoreId());        
                if ($invoiceCode && $notifyCustomer){
                    $invoice = Mage::getModel('sales/order_invoice')
                        ->loadByIncrementId($invoiceCode);
                        
                    if ($invoice->getId()) {
                        $invoice
                            ->setEmailSent(true)
                            ->sendEmail(true)
                            ->save();
                    }
                    $invoice = null;
                    unset($invoice);
                }
                ++$numAffectedOrders;           
            }
            catch (Exception $e) {
                $err = $e->getCustomMessage() ? $e->getCustomMessage() : $e->getMessage();
                $this->_errors[] = $hlp->__(
                    'Can not invoice order #%s: %s', $orderCode, $err);
            }
            $order = null;
            unset($order); 
        }
        
        if ($numAffectedOrders){
            $success = $hlp->__('Total of %d order(s) have been successfully invoiced.', 
                $numAffectedOrders);
        }         
        
        return $success; 
    }
}