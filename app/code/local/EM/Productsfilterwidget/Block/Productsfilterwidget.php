<?php
class EM_Productsfilterwidget_Block_Productsfilterwidget extends Mage_Catalog_Block_Product_List implements Mage_Widget_Block_Interface
{
    /**
     * Retrieve loaded category collection
     *	$midM = round(memory_get_usage()/1048576,2) . "\n"; // 36640
		$usedM = $midM-$startM;
		echo "<br>Dung 1 : {$usedM}</br>";
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */

    protected $_productCollection;
	protected $_productIds;
	protected $_arr;
	
	public function getLoadedProductCollection()
	{		
		return $this->getProductCollection();
	}
	
    protected function getProductCollection()
    {	
		$out_of_stock = Mage::getStoreConfig('cataloginventory/options/show_out_of_stock');
			
		$in = $this->getConfigs();
		$reg = Mage::registry('config');
		
		if($reg)
			Mage::unregister('config');
		Mage::register('config',$in);
		
		$actionsArr =unserialize($in['conditions']);
		
		//print_r($actionsArr);die;
		
		if($actionsArr['conditions'])
		{			
			$conditions=$actionsArr['conditions'];		
			$strAttribute=$this->getStrAttribute($conditions);
			$arrAttribute=$this->getArrAttribute($strAttribute);
			
		}
		else
		{
			$arrAttribute=array();
		}	
		if(Mage::registry('arrAttribute'))
			Mage::unregister('arrAttribute');
		Mage::register('arrAttribute',$arrAttribute);	
		
	   $catalogRule = Mage::getModel('productsfilterwidget/rule');
	   if (!empty($actionsArr) && is_array($actionsArr))
		{
			$catalogRule->getConditions()->loadArray($actionsArr);
		}
		
		$catarule=Mage::registry('catalogRule');
		if($catarule) Mage::unregister('catalogRule');	
		Mage::register('catalogRule',$catalogRule);
		
				
		$lib_multicache	=	Mage::helper('productsfilterwidget/multicache');		
		$productIds	=	$lib_multicache->get('conditions_'.$in['time']);
		if(!$productIds)
		{	
			$productIds=Mage::getModel('productsfilterwidget/productrule')->getMatchingProductIds();
			if(!$productIds) $productIds = 'empty';
			$lib_multicache	=	Mage::helper('productsfilterwidget/multicache');
			$lib_multicache->set('conditions_'.$in['time'],$productIds,$in['cache_time']*60);			
		}			
		if($productIds	==	'empty')	$productIds = '';
		if($in['sort_by'] == 'position') {$in['sort_by'] = 'entity_id';}
		

			if($in['sort_by'] == 'mostviews' || $in['sort_by'] == 'ordered_qty')
			{
			
				
				$result = Mage::getResourceModel('reports/product_collection');
				if($in['sort_by'] == 'mostviews')
				{
					$result->addViewsCount()
					->addAttributeToFilter('entity_id',array('in' => $productIds));			
				}
				elseif($in['sort_by'] == 'ordered_qty')//bestseller
				{
					$result->addAttributeToSelect('*')
											->addOrderedQty()
											->setOrder('ordered_qty', 'desc')
											->addAttributeToFilter('entity_id',array(
													'in' => $productIds
													));
				}
			}
			else
			{
				$result = Mage::getModel('productsfilterwidget/product')->getCollection()->addAttributeToFilter('entity_id',array(
														'in' => $productIds
														));
				if($in['sort_by'] == 'random')
				{
					$result->getSelect()->order(new Zend_Db_Expr('RAND()'));
				}
				else
				{
					$result->setOrder($in['sort_by'],$in['sort_direction']);
				}
			}
			
		if($in['newproduct']==1)
		{
			$todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
					
					$result->addAttributeToFilter('news_from_date', array('or'=> array(
						0 => array('date' => true, 'to' => $todayDate),
						1 => array('is' => new Zend_Db_Expr('null')))
					), 'left')
					->addAttributeToFilter('news_to_date', array('or'=> array(
						0 => array('date' => true, 'from' => $todayDate),
						1 => array('is' => new Zend_Db_Expr('null')))
					), 'left')
					->addAttributeToFilter(
						array(
							array('attribute' => 'news_from_date', 'is'=>new Zend_Db_Expr('not null')),
							array('attribute' => 'news_to_date', 'is'=>new Zend_Db_Expr('not null'))
							)
					)
					->addAttributeToSort('news_from_date', 'desc');
		}
		if($out_of_stock==0)
		{		
			$result->addAttributeToSelect('*')->getSelect()->joinLeft(array('cain'=>'cataloginventory_stock_status'),'e.entity_id=cain.product_id',array('cain.stock_status'))->where('cain.stock_status =1');
		}
				
		$result->addAttributeToSelect('*')->setPageSize($in['limit_count'])->setcurPage($this->getRequest()->getParam('p',1));			
		$this->setCollection($result);	
		$this->_defaultToolbarBlock = 'productsfilterwidget/toolbar';
	
		return $this->_productCollection;

    }
	 
	 function getStrAttribute($conditions)
	{
	
		foreach($conditions as $attribute)
				{
					if($attribute['attribute'])
					{
						
							$this->_arr.=$attribute['attribute'].",";
					}
					if($attribute['conditions'])
					{	
						$conditions=$attribute['conditions'];
						$this->getStrAttribute($conditions);
					}
				}
		return $this->_arr;
	}
	
	public function getArrAttribute($str)
	{
		
		$arr=explode(',',$str,-1);
		$n=count($arr);
		$arr1=array();
		$arr1[]=$arr[0];
			for($i=1;$i<$n;$i++)
			{
				if($this->check($arr[$i],$arr1))
					$arr1[]=$arr[$i];
			}
		return $arr1;
	}
	public function check($x,$arr)
	{
		$n=count($arr);
		for($i=0;$i<$n;$i++)
		{
			if ($arr[$i]==$x)
				return false;
		}
		return true;
	}
    public function getProductsFilterWidget()     
    { 
        if (!$this->hasData('productsfilterwidget')) {
            $this->setData('productsfilterwidget', Mage::registry('productsfilterwidget'));
        }
        return $this->getData('productsfilterwidget');
        
    }
	
	public function getConfigs()
	{	$input['newproduct']		=	$this->getData('newproduct');
		$input['cache_time']		=	$this->getData('cache_time');
		$input['col_count']			=	$this->getData('col_count');
		$input['limit_count']		=	$this->getData('limit_count');
		$input['sort_by']			=	$this->getData('sort_by');
		$input['sort_direction']	=	$this->getData('sort_direction');		
		$input['toolbar']			=	$this->getData('toolbar');
		$plit	=	explode('-',$this->getData('conditions'));
		$count	=	count($plit);
		$tam	=	$plit[0];
		for($i=1;$i<$count-1;$i++){
			$tam	.=	'-'.$plit[$i];
		}		
		$input['conditions']		=	Mage::helper('core')->urlDecode($tam);
		
		$input['time']				=	$plit[$count-1];
		
		return $input;
	}
	
	protected function _toHtml()
    {   
		if($this->getData('template')	==	'custom_template')
		{	$this->setTemplate($this->getData('custom_theme'));		}
        return parent::_toHtml();
    }
	
	public function getColumnCount()
	{
		return $this->getData('col_count');
	}

	
}