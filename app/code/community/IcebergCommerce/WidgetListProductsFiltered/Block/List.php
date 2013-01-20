<?php
/**
 * Iceberg Commerce
 * @author     IcebergCommerce
 * @package    IcebergCommerce_WidgetListProductsFiltered
 * @copyright  Copyright (c) 2010 Iceberg Commerce
 */
class IcebergCommerce_WidgetListProductsFiltered_Block_List extends Mage_Catalog_Block_Product_List implements Mage_Widget_Block_Interface
{
	/**
	 * Initialize Widget List
	 * We have to define some defaults for grid columns 
	 * this is usually done through layout xml, but we want to minimize footprint of widget
	 */
	protected function _beforeToHtml()
	{
		if ($this->getOptionTemplate())
		{
			$this->setTemplate($this->getOptionTemplate());
		}
		else 
		{
			$this->setTemplate('catalog/product/list.phtml');
		}
		
		// Default Column Counts For Widget
		$this->addColumnCountLayoutDepend('one_column',        5)
			 ->addColumnCountLayoutDepend('two_columns_left',  4)
			 ->addColumnCountLayoutDepend('two_columns_right', 4)
			 ->addColumnCountLayoutDepend('three_columns',     3);
	
		return parent::_beforeToHtml();
	}
    
	
	/**
	 * Get Product Collection for Widget List
	 * This sets category id
	 * Also sets all filterable attribute values
	 */
    protected function _getProductCollection()
    { 
        if (is_null($this->_productCollection)) 
        {
            $layer = Mage::getModel('catalog/layer');

            if ($this->getShowRootCategory()) 
            {
               $this->setCategoryId(Mage::app()->getStore()->getRootCategoryId());
            }

			preg_match( '#category/(\d+)#i' , $this->getData('id_path') , $match );
			$categoryId = $match[1];

            if ($categoryId) 
            {
                $category = Mage::getModel('catalog/category')->load($categoryId);
                if ($category->getId()) 
                {
                    $layer->setCurrentCategory($category);
                }
            }

            $this->_productCollection = $layer->getProductCollection();
            
            $attributes = Mage::helper('widgetlistproductsfiltered/data')->getAttributes();
            foreach ($attributes as $attribute) 
			{
				// If Attribute name begins with "option", it may be a magento option, so skip it
				if (substr($attribute->getAttributeCode(),0,6)=='option')
				{
					continue;
				}
				
				// Skip reserved attribute names (used by magento in widget embed code)
				if (in_array($attribute->getAttributeCode(),array('type', 'id_path')))
				{
					continue;
				}
				
				$value = $this->getData($attribute->getAttributeCode());
				
				if ($value)
				{
					if ($attribute->getFrontendInput() == 'multiselect')
					{
						$value = explode(',',$value);
						
						if (!is_array($value) || empty($value))
						{
							// Skip since there is nothing to filter by
							continue;
						}
						
						// Do some type casting so that magento db layer will not do weird stuff with quotes
						foreach ($value as $k=>$v)
						{
							if (is_numeric($v))
							{
								$value[$k] = $v * 1;
							}
						}
					}
					
					// Two different ways to add filters to collection.
					// First is using the same logic as layered nav (more efficient)
					// second is to just do joins and filter on attribute (less efficient, but only way to do it when a non filterable attribute is used)
					if ($attribute->getIsFilterable())
					{
						$filter = Mage::getModel('catalog/layer_filter_attribute')
								->setLayer($layer)
								->setAttributeModel($attribute);
								
						Mage::helper('widgetlistproductsfiltered/data')->applyFilterToCollection($filter, $value);
					}
					else 
					{
						if ($attribute->getFrontendInput() == 'multiselect')
						{
							if (is_array($value))
							{
								if (count($value) == 1)
								{
									$this->_productCollection->addAttributeToFilter($attribute,array('finset' =>$value[0]));
								}
								else 
								{
									$filters = array();
									foreach ($value as $v)
									{
										$filters[] = array('attribute'=>$attribute,'finset' =>$v);
									}
									$this->_productCollection->addAttributeToFilter($attribute, $filters);
								}
							}
						}
						else 
						{
							$this->_productCollection->addAttributeToFilter($attribute,$value);
						}
					}
				}
			}
			
			$this->_productCollection = $layer->getProductCollection();
        }
        
        // try to remove category filter so that we can make category filter optional
        //$this->_productCollection->removeAttributeToSelect('category_id');
        //unset($this->_productCollection->_productLimitationFilters['category_id']);
        //print_r($this->_productCollection->getSelect()->__toString()); exit;
        
        $limitSize = (int) $this->getOptionLimitSize();
		if ($limitSize > 0)
		{
			$this->_productCollection->setPageSize($limitSize);
		}
		
        return $this->_productCollection;
    }
    
	/**
	 * Overrides parent definition of this method
	 * so that we can use our custom flags to control pagination parameters
	 * and to also add a pager block
	 */
	public function getToolbarBlock()
	{
		$toolbarBlock = null;
		$blockName    = $this->getToolbarBlockName();
		
		// Rand is used in case more than one instance of widget on same page... to insure unique block.
		$pagerBlock   = $this->getLayout()->createBlock('page/html_pager', microtime() . '-' . rand(0,100000));
		
		if ($blockName) 
		{
			$toolbarBlock = $this->getLayout()->getBlock($blockName);
		}
		
		if (!$toolbarBlock) 
		{
			// Rand is used in case more than one instance of widget on same page... to insure unique block.
			$toolbarBlock = $this->getLayout()->createBlock($this->_defaultToolbarBlock, microtime() . '-' . rand(0,100000));
		}
		
		// --------------------------------------------
		// Set Defaults for Pagination
		// --------------------------------------------
		$pageSize = (int) $this->getOptionPageSize();
		if ($pageSize > 0)
		{
			$toolbarBlock->setData('_current_limit', $pageSize);
		}
		
		$showAll = (int) $this->getOptionShowAll();
		if ($showAll > 0)
		{
			$toolbarBlock->setData('_current_limit', 'all');
		}
		
		$columnCount = (int) $this->getOptionColumnCount();
		if ($columnCount > 0)
		{
			$this->setColumnCount($columnCount);
		}
		
		// Default Order
		$sort = $this->getOptionSortBy();
		if( $sort ) {
			$toolbarBlock->setDefaultOrder( $sort );
			if ($this->isToolbarHidden())
			{
				$toolbarBlock->setData('_current_grid_order', $sort);
			}
		}
		
		// Default Direction
		$dir = $this->getOptionSortDirection();
		if( $dir )
		{
			$toolbarBlock->setDefaultDirection( $dir );
			if ($this->isToolbarHidden())
			{
				$toolbarBlock->setData('_current_grid_direction', $dir);
			}
		}
		
		// Default Mode 
		// - for paginated, show what user wants
		// - for no toolbar, only show grid mode
		if ($this->isToolbarHidden())
		{
			$toolbarBlock->setData('_current_grid_mode', 'grid');
		}
		
		
		$toolbarBlock->setChild('product_list_toolbar_pager', $pagerBlock);
		return $toolbarBlock;
	}
	
	/**
	 * Extend parent logic to add the ability to turn off toolbar
	 */
	public function getToolbarHtml()
	{
		if ($this->isToolbarHidden())
		{
			return null;
		}
		
		return parent::getToolbarHtml();
	}
	
	/**
	 * Helper
	 */
	private function isToolbarHidden()
	{
		return ($this->getOptionShowAll() || $this->getOptionLimitSize());
	}
    
	/**
	 * Render
	 */
	protected function _toHtml()
	{
		// Magento 1.7+ CSS rules to fix .std ul rule
		$extraHtml = '';
		try 
		{
			$versionInfo = Mage::getVersionInfo();
			
			if ($versionInfo['major'] >= 1)
			{
				if ($versionInfo['minor'] >= 7)
				{
					$extraHtml = '<style type="text/css">.std .category-products ul{padding-left:0;list-style:none}</style>';
				}
			}
		}
		catch (Exception $e)
		{
			// Ignore exception.
		}
		
		return $extraHtml . parent::_toHtml();
	}
}
