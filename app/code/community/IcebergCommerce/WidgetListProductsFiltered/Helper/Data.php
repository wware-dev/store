<?php
/**
 * Iceberg Commerce
 * @author     IcebergCommerce
 * @package    IcebergCommerce_WidgetListProductsFiltered
 * @copyright  Copyright (c) 2010 Iceberg Commerce
 */
class IcebergCommerce_WidgetListProductsFiltered_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getAttributes()
	{
		// Get All Attributes
		$attributes = Mage::getResourceModel('catalog/product_attribute_collection')
			->setItemObjectClass('catalog/resource_eav_attribute');
		$attributes->getSelect()
			->distinct(true)
			->where("frontend_input in ('select','multiselect','boolean')");
		$attributes->addStoreLabel(Mage::app()->getStore()->getId())
			->setOrder('position', 'ASC')
			//->addIsFilterableFilter()
			->load();
		
		foreach ($attributes as $a)
		{
			$a->setIsRequired(false);
		}
		
		return $attributes;
	}
	
	/**
     * Replaces Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute::applyFilterToCollection(..)
     * So that we can use this to filter products with multiselect attributes
     */
    public function applyFilterToCollection($filter, $value)
    {
    	$object = Mage::getResourceModel('catalog/layer_filter_attribute');
        $collection = $filter->getLayer()->getProductCollection();
        $attribute  = $filter->getAttributeModel();
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableAlias = $attribute->getAttributeCode() . '_idx';
        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId()),
            
        );
        
        if (is_array($value))
        {
        	$collection->getSelect()->from('','e.entity_id');
        	$collection->distinct(true);
        	$conditions[] = $connection->quoteInto("{$tableAlias}.value in (?)", $value);
        }
        else
        {
        	$conditions[] = $connection->quoteInto("{$tableAlias}.value = ?", $value);
        }
        
        $collection->getSelect()->join(
            array($tableAlias => $object->getMainTable()),
            join(' AND ', $conditions),
            array()
        );
    }
}
