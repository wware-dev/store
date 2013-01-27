<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Oaction
*/
class Amasty_Oaction_Model_Observer
{
    public function addNewActions($observer) 
    {
        if (!$this->_isSalesGrid($observer->getPage())){
            return $this;
        }        
        
        $block = $observer->getBlock();
        
        //$types = array('', 'ship', 'invoice', 'capture', 'delete');
        $types = Mage::getStoreConfig('amoaction/general/commands');
        if (!$types)
            return $this;
            
        $types = explode(',', $types); 
        foreach ($types as $i => $type){
            if ($type){
                $command = Amasty_Oaction_Model_Command_Abstract::factory($type);
                $command->addAction($block);
            }
            else { // separator
                $block->addItem('amoaction_separator' . $i, array(
                    'label'=> '---------------------',
                    'url'  => '' 
                ));                
            }
        }
        
        return $this;
    }
    
    public function modifyJs($observer) 
    {
        $page = $observer->getResult()->getPage();
        if (!$this->_isSalesGrid($page)){
            return $this;
        }
        
        $js = $observer->getResult()->getJs();
        $js = str_replace('varienGridMassaction', 'amoaction', $js); 
        $observer->getResult()->setJs($js);
        
        return $this;
    }  
    
    protected function _isSalesGrid($page)
    {
        return in_array($page, array('adminhtml_sales_order', 'sales_order'));
    }  
    
}