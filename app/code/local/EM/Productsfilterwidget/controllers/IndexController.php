<?php
class EM_Productsfilterwidget_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/productsfilterwidget?id=15 
    	 *  or
    	 * http://site.com/productsfilterwidget/id/15 	
    	 */
    	/* 
		$productsfilterwidget_id = $this->getRequest()->getParam('id');

  		if($productsfilterwidget_id != null && $productsfilterwidget_id != '')	{
			$productsfilterwidget = Mage::getModel('productsfilterwidget/productsfilterwidget')->load($productsfilterwidget_id)->getData();
		} else {
			$productsfilterwidget = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($productsfilterwidget == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$productsfilterwidgetTable = $resource->getTableName('productsfilterwidget');
			
			$select = $read->select()
			   ->from($productsfilterwidgetTable,array('productsfilterwidget_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$productsfilterwidget = $read->fetchRow($select);
		}
		Mage::register('productsfilterwidget', $productsfilterwidget);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}