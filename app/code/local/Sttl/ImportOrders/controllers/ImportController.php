<?php
/**
 * Silver Touch Technologies Limited.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.silvertouch.com/MagentoExtensions/LICENSE.txt
 *
 * @category   Sttl
 * @package    Sttl_ImportOrders
 * @copyright  Copyright (c) 2011 Silver Touch Technologies Limited. (http://www.silvertouch.com/MagentoExtensions)
 * @license    http://www.silvertouch.com/MagentoExtensions/LICENSE.txt
 */
class Sttl_ImportOrders_ImportController extends Mage_Adminhtml_Controller_Action
{
    private $hasErrors;
    public function indexAction()
    {
        $this->loadLayout(array(
                'default',
                'importorder_import_index'
            ));
        $this->renderLayout();
    }
	public function mappingAction()
	{
			 if(isset($_FILES['report']['name']) && $_FILES['report']['name'] != '') {
				try {	
					/* Starting upload */	
					$uploader = new Varien_File_Uploader('report');
					
					// Any extention would work
	           		
					$uploader->setAllowRenameFiles(false);
					
					// Set the file upload mode 
					// false -> get the file directly in the specified folder
					// true -> get the file in the product like folders 
					//	(file.jpg will go in something like /media/f/i/file.jpg)
					$uploader->setFilesDispersion(false);
							
					// We set media as the upload dir
					$path = Mage::getBaseDir('media') . DS . 'orderimport';
					$uploader->save($path, $_FILES['report']['name'] );
					
				} catch (Exception $e) {
		      
		        }	       
	  			
			}
			$fileName = $_FILES['report']['name'];
			Mage::getModel('core/session')->setFileName($fileName);	 
			$this->loadLayout(array(
					'default',
					'importorder_import_mapp'
				));
			$this->renderLayout();
	}
    public function postAction()
    {	
			$postdata = $this->getRequest()->getPost(); //CSV Header field
			$importObject = Mage::getModel('importOrders/importOrders');
	      
	        try {
		        	$filename = Mage::getModel('core/session')->getFileName();	
					$path = Mage::getBaseDir('media') . DS . 'orderimport';	
					$csvObject  = new Varien_File_Csv();		
					$csvData = $csvObject->getData($path.'/'.$filename);					
					while (list($key, $value) = each($csvData)) {                
						if ($key == 0) {
							$fields = $value;                    
						} else {
							try {
								if (is_array($value) && count($value)>0) {							
									foreach($value as $key => $va) {		        				
										$data[$fields[$key]] = $va;
									}
									$importObject->loadFields($fields);
									$result = $importObject->importOrder($data,$postdata);
									if ($result) {
										throw new Exception($result);
									}
								}
							} catch (Exception $e) {
								$this->hasErrors = true;
								Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
								$this->_redirect('*/*');
							}
						}
					}
			
	        	} catch (Exception $e) {
	        		$this->hasErrors = true;
			        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			        $this->_redirect('*/*');
			    }
	        
      
        if (!$this->hasErrors) {
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Order Imported Successfully.'));
            $this->_redirect('*/import/index');
        }
    }
	public function _validateFormKey() {
		return true;
	}
}
?>
