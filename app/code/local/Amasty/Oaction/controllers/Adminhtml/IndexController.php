<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Oaction
*/
class Amasty_Oaction_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
    public function doAction()
    {
        $ids         = $this->getRequest()->getParam('order_ids');
        $val         = trim($this->getRequest()->getParam('amoaction_value'));        
        $commandType = trim($this->getRequest()->getParam('command'));
        
        try {
            $command = Amasty_Oaction_Model_Command_Abstract::factory($commandType);
            
            $success = $command->execute($ids, $val);
            if ($success){
                 $this->_getSession()->addSuccess($success);
            }
            
            // show non critical errors to the user
            foreach ($command->getErrors() as $err){
                 $this->_getSession()->addError($err);
            }            
        }
        catch (Exception $e) {
            $this->_getSession()->addError($this->__('Error: %s', $e->getMessage()));
        } 
        
        $this->_redirect('adminhtml/sales_order');
        return $this;        
    }
}