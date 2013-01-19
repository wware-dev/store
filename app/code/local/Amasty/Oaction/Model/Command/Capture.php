<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Oaction
*/
class Amasty_Oaction_Model_Command_Capture extends Amasty_Oaction_Model_Command_Abstract
{ 
    public function __construct($type)
    {
        parent::__construct($type);
        $this->_label = 'Capture';
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
        $comment = $hlp->__('Invoice captured');
        
        foreach ($ids as $id){
            $order     = Mage::getModel('sales/order')->load($id);
            $orderCode = $order->getIncrementId();
            
            try {
                $allInvoices = $order->getInvoiceCollection();
                if (!count($allInvoices)){
                    $this->_errors[] = $hlp->__('Order #%s has no invoices', $orderCode);
                    continue;                    
                }
                
                foreach ($allInvoices as $invoice) {
                    $invoiceCode = $invoice->getIncrementId();
                    if (!$invoice->canCapture()){
                        $this->_errors[] = $hlp->__('Can not capture invoice #%s', $invoiceCode);
                        continue;
                    } 
                    
                    $isOk = Mage::getModel('sales/order_invoice_api_v2')
                        ->capture($invoiceCode);   
                    
                    $notifyCustomer = Mage::getStoreConfig('amoaction/capture/notify', $order->getStoreId());     
                    if ($isOk && $notifyCustomer){   
                        Mage::getModel('sales/order_invoice_api_v2')
                            ->addComment($invoiceCode, $comment, true, true);                        
                    }
                }
                
                //update status    
                $status = Mage::getStoreConfig('amoaction/capture/status', $order->getStoreId());    
                if ($status) {
                    Mage::getModel('sales/order_api')->addComment($orderCode, $status, '', false); 
                }
                
                ++$numAffectedOrders;
            }
            catch (Exception $e) {
                $err = $e->getCustomMessage() ? $e->getCustomMessage() : $e->getMessage();
                $this->_errors[] = $hlp->__(
                    'Can not capture invoice for order #%s: %s', $orderCode, $err);
            }
            $order = null;
            unset($order); 
        }
        
        if ($numAffectedOrders){
            $success = $hlp->__('Total of %d order(s) have been successfully captured.', 
                $numAffectedOrders);
        }         
        
        return $success; 
    }
}