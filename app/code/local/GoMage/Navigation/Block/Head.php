<?php
 /**
 * GoMage Advanced Navigation Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2012 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.2
 * @since        Class available since Release 2.3
 */


class GoMage_Navigation_Block_Head extends Mage_Core_Block_Template
{    
    protected function _prepareLayout()
    { 
        parent::_prepareLayout();
        if(Mage::helper('gomage_navigation')->isGomageNavigation()){         	        	
        	if($head_block = $this->getLayout()->getBlock('head')){
	        	$styles_block = $this->getLayout()->createBlock('gomage_navigation/styles', 'advancednavigation_styles')->setTemplate('gomage/navigation/header/styles.php');	        
		        $head_block->setChild('advancednavigation_styles', $styles_block);	            
		        $head_block->addjs('gomage/advanced-navigation.js');
		        $head_block->addjs('gomage/category-navigation.js');
		        $head_block->addCss('css/gomage/advanced-navigation.css');
        	}        	                         
        }       
    }
}