<?php
 /**
 * GoMage Advanced Navigation Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.2
 * @since        Class available since Release 3.2
 */
	
class GoMage_Navigation_Block_Layer_Filter_Stock extends Mage_Catalog_Block_Layer_Filter_Abstract
{
    
    public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'gomage_navigation/layer_filter_stock';
        
        if(Mage::helper('gomage_navigation')->isGomageNavigation() &&
           Mage::getStoreConfigFlag('gomage_navigation/stock/active'))
        {
            
            $type = Mage::getStoreConfig('gomage_navigation/stock/filter_type');
            
            switch($type): 
            
                default:
                
                    $this->_template = ('gomage/navigation/layer/filter/default.phtml');
                
                break;
                
                case(GoMage_Navigation_Model_Layer::FILTER_TYPE_IMAGE):
                
                    $this->_template = ('gomage/navigation/layer/filter/image.phtml');
                
                break;
                
                case(GoMage_Navigation_Model_Layer::FILTER_TYPE_DROPDOWN):
                
                    $this->_template = ('gomage/navigation/layer/filter/dropdown.phtml');
                
                break;
            
            endswitch;
        }        
    }    
    
	
	public function getRemoveUrl($ajax = false)
    {
        $query = array($this->_filter->getRequestVar()=>null);
        $params['_nosid']       = true;
        $params['_current']     = true;
        $params['_use_rewrite'] = true;
        $params['_query']       = $query;
        $params['_escape']      = false;
        
        $params['_query']['ajax'] = null;
        
        if($ajax){
        	$params['_query']['ajax'] = true;	
        }
        
        return Mage::getUrl('*/*/*', $params);
    }
	
	public function getItems()
	{	
	    if(Mage::helper('gomage_navigation')->isGomageNavigation() && 
	       Mage::getStoreConfigFlag('gomage_navigation/stock/active')){

	        if(!$this->ajaxEnabled())
	        {				
    			$items = parent::getItems();;
    			
    			foreach($items as $key=>$item){
    				
    			    if($category = Mage::getModel('catalog/category')->load($item->getValue())){				
    					
    					$items[$key]->setUrl($category->getUrl());
    					
    				}
    			}
    			
    			return $items;
    		}
	    }
	    
		return parent::getItems();
	}
	
	public function getPopupId()
	{
		return 'stock_status';
	}
	
	public function ajaxEnabled()
	{	
		if (Mage::app()->getFrontController()->getRequest()->getRouteName() == 'catalogsearch')
		{
	        $is_ajax = true;
	    }
	    else
	    {
	    	$is_ajax = Mage::registry('current_category') && 
            	       Mage::registry('current_category')->getisAnchor() &&
                	   (Mage::registry('current_category')->getDisplayMode() != Mage_Catalog_Model_Category::DM_PAGE);
	    }
                   
	    $is_ajax = $is_ajax && Mage::getStoreConfigFlag('gomage_navigation/stock/ajax_enabled');

		return $is_ajax;
	}
	
	public function canShowMinimized(){
		
		if ( Mage::app()->getFrontController()->getRequest()->getParam('stock_status_is_open') )
		{
			if('true' === Mage::app()->getFrontController()->getRequest()->getParam('stock_status_is_open')){
				return false;
			}elseif('false' === Mage::app()->getFrontController()->getRequest()->getParam('stock_status_is_open')){
				return true;			
			}
		}
		
		return (bool) Mage::getStoreConfigFlag('gomage_navigation/stock/show_minimized');
	}
	
	public function canShowPopup(){
		return (bool) Mage::getStoreConfigFlag('gomage_navigation/stock/show_help');
	}
	
	public function getPopupText(){
		return trim(Mage::getStoreConfig('gomage_navigation/stock/popup_text'));
	}
	
	public function getPopupWidth(){
		return (int) Mage::getStoreConfig('gomage_navigation/stock/popup_width');
	}
	
	public function getPopupHeight(){
		return (int) Mage::getStoreConfig('gomage_navigation/stock/popup_height');
	}
	
	public function canShowCheckbox(){	
		return (bool) Mage::getStoreConfigFlag('gomage_navigation/stock/show_checkbox');
	}
	
	public function canShowLabels(){	
		return (bool) Mage::getStoreConfigFlag('gomage_navigation/stock/show_image_name');
	}
	
	public function getImageWidth(){
		return (int) Mage::getStoreConfig('gomage_navigation/stock/image_width');
	}
	
	public function getImageHeight(){
		return (int) Mage::getStoreConfig('gomage_navigation/stock/image_height');
	}
	
	public function getImageAlign(){
		
		switch(Mage::getStoreConfig('gomage_navigation/stock/image_align')):
		
			default:
				
				$image_align = 'default';
				
			break;
			
			case (1):
				
				$image_align = 'horizontally';
				
			break;
			
			case (2):
				
				$image_align = '2-columns';
				
			break;
			
		endswitch;
		
		return $image_align;	
	}
	
    public function canShowResetFirler(){
		return false;
	}
	
    
    public function getFilterType(){
        return Mage::getStoreConfig('gomage_navigation/stock/filter_type');    
    }
    
    public function getInBlockHeight(){
        return Mage::getStoreConfig('gomage_navigation/stock/inblock_height');    
    }
}
