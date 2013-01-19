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

class GoMage_Navigation_Model_Layer_Filter_Stock extends GoMage_Navigation_Model_Layer_Filter_Abstract
{

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->_requestVar = 'stock_status';
    }

    /**
     * Get filter value for reset current filter state
     *
     * @return mixed
     */
    public function getResetValue($value_to_remove = null)
    {
        
        if($value_to_remove && ($current_value = Mage::app()->getFrontController()->getRequest()->getParam($this->_requestVar)))
        {    
            $current_value = explode(',', $current_value);
            
            if(false !== ($position = array_search($value_to_remove, $current_value)))
            {    
                unset($current_value[$position]);
                
                if(!empty($current_value))
                {    
                    return implode(',', $current_value);   
                }   
            }
        }
        
        return null;
    }

    /**
     * Apply category filter to layer
     *
     * @param   Zend_Controller_Request_Abstract $request
     * @param   Mage_Core_Block_Abstract $filterBlock
     * @return  Mage_Catalog_Model_Layer_Filter_Category
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $filter = $request->getParam($this->getRequestVar());
        
        $filters = explode(',', $filter);
        	
        $this->_getResource()->applyFilterToCollection($this, $filters);
        
        
        $collection = $this->getLayer()->getProductCollection();
        if($filter == 1)
        {
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
            $this->getLayer()->getState()->addFilter(
                    $this->_createItem(Mage::helper('gomage_navigation')->__("In Stock"), array("stock_status"=>$filter))
                );
        }
        elseif($filter == 2)
        {
            $manageStock = Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
            $cond = array(
                '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=1 AND {{table}}.is_in_stock=0',
                '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=0',
            );

            if ($manageStock) {
                $cond[] = '{{table}}.use_config_manage_stock = 1 AND {{table}}.is_in_stock=0';
            } else {
                $cond[] = '{{table}}.use_config_manage_stock = 1';
            }

            $collection->joinField(
                'inventory_in_stock',
                'cataloginventory/stock_item',
                'is_in_stock',
                'product_id=entity_id',
                '(' . join(') OR (', $cond) . ')'
            );    
            $this->getLayer()->getState()->addFilter(
                    $this->_createItem(Mage::helper('gomage_navigation')->__("In Stock"), array("stock_status"=>$filter))
                );                  
        }
        	
        return $this;
    }
    
    


    /**
     * Get filter name
     *
     * @return string
     */
    public function getName()
    {
        return Mage::helper('gomage_navigation')->__('Stock');
    }

    
    
    protected function _getItemsData()
    {
    
       
        $value = Mage::app()->getFrontController()->getRequest()->getParam($this->_requestVar);
                		
        $data[] = array(
            'label'     => Mage::helper('gomage_navigation')->__("In Stock"),
            'value'     => "1",
            'count'     => "",
            'active'    => ($value==1)?true:false,
            'image'        => "",
            );        		        		        		        		
        $data[] = array(
            'label'     => Mage::helper('gomage_navigation')->__("Out of Stock"),
            'value'     => "2",
            'count'     => "",
            'active'    => ($value==2)?true:false,
            'image'        => "",
            );                                                                


        return $data;
    }
    
    /**
     * Retrieve resource instance
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute
     */
    protected function _getResource()
    {
        if (is_null($this->_resource)) {
            $this->_resource = Mage::getModel('gomage_navigation/resource_eav_mysql4_layer_filter_stock');
        }
        return $this->_resource;
    }
    
}
