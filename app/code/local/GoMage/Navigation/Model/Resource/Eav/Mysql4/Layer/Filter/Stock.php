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

class GoMage_Navigation_Model_Resource_Eav_Mysql4_Layer_Filter_Stock extends Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute
{
	
	public function prepareSelect($filter, $value, $select){
        
		$val = (int)$value[0];
		
        $table = "stock_status";
        $manageStock = Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);  
        if($val == 1)
        {
        	
            $cond = array( 
                "{$table}.use_config_manage_stock = 0 AND {$table}.manage_stock=1 AND {$table}.is_in_stock=1",
                "{$table}.use_config_manage_stock = 0 AND {$table}.manage_stock=0",
            );

            if ($manageStock) {
                $cond[] = "{$table}.use_config_manage_stock = 1 AND {$table}.is_in_stock=1";
            } else {
                $cond[] = "{$table}.use_config_manage_stock = 1";
            }
            $select->where("{$table}.product_id=e.entity_id");
            $select->join(  
                array($table => Mage::getSingleton('core/resource')->getTableName('cataloginventory/stock_item')),
                '(' . join(') OR (', $cond) . ')',
                array("inventory_in_stock"=>"qty")
            );
                
        }
        elseif($val == 2)
        {

            $cond = array(
                "{$table}.use_config_manage_stock = 0 AND {$table}.manage_stock=1 AND {$table}.is_in_stock=0",
                "{$table}.use_config_manage_stock = 0 AND {$table}.manage_stock=0",
            );

            if ($manageStock) {
                $cond[] = "{$table}.use_config_manage_stock = 1 AND {$table}.is_in_stock=0";
            } else {
                $cond[] = "{$table}.use_config_manage_stock = 1";
            }

            $select->where("{$table}.product_id=e.entity_id");
            $select->join(  
                array($table => Mage::getSingleton('core/resource')->getTableName('cataloginventory/stock_item')),
                '(' . join(') OR (', $cond) . ')',
                array("inventory_in_stock"=>"qty")
                
            ); 

        } 
        
        return $this;     
	}

     
     /**
     * Apply attribute filter to product collection
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @param int $value
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute
     */
     
     
    public function applyFilterToCollection($filter, $value)
    {
        $collection = $filter->getLayer()->getProductCollection();
        
        $this->prepareSelect($filter, $value, $collection->getSelect());
        
        $base_select = $filter->getLayer()->getBaseSelect();
        
        foreach($base_select as $code=>$select){
        	
        	if('stock_status' != $code){
        	
        		$this->prepareSelect($filter, $value, $select);
        	
        	}
        }
        
        return $this;
    }

    /**
     * Retrieve array with products counts per attribute option
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @return array
     */
    public function getCount($filter)
    {
    	$connection = $this->_getReadAdapter();
    	
		$base_select = $filter->getLayer()->getBaseSelect();
		        
        if(isset($base_select['stock_status'])){
        	
        	
        	$select = $base_select['stock_status'];        	
        
        }else{
        	
        	$select = clone $filter->getLayer()->getProductCollection()->getSelect();
        	
        }
		
		$where = array();
		
        
        $_collection = clone $filter->getLayer()->getProductCollection();
    	$searched_entity_ids = $_collection->load()->getSearchedEntityIds();
        if ($searched_entity_ids && is_array($searched_entity_ids) && count($searched_entity_ids)){
        	$select->where('e.entity_id IN (?)', $searched_entity_ids);	
        } 
        
        return $connection->fetchPairs($select);
        
    }
}
