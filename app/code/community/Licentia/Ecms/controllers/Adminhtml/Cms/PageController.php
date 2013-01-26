<?php

include_once("Mage/Adminhtml/controllers/Cms/PageController.php");


/**
 * Licentia Enhanced CMS
 *
 * NOTICE OF LICENSE
 * This source file is subject to the European Union Public Licence
 * It is available through the world-wide-web at this URL:
 * http://joinup.ec.europa.eu/software/page/eupl/licence-eupl
 *
 * @title      Licentia Enhanced CMS
 * @category   Easy of Use
 * @package    Licentia
 * @author     Bento Vilas Boas <bento@licentia.pt>
 * @copyright  Copyright (c) 2012 Licentia - http://licentia.pt
 * @license    European Union Public Licence
 */


class Licentia_Ecms_Adminhtml_Cms_PageController extends Mage_Adminhtml_Cms_PageController {

    public function massDeleteAction() {

        $pages = $this->getRequest()->getParam('page');
        if (!is_array($pages)) {
            $this->_getSession()->addError($this->__('Please select page(s).'));
        } else {
            try {
                foreach ($pages as $id) {
                    $model = Mage::getModel('cms/page');
                    $model->load($id);
                    $title = $model->getTitle();
                    $model->delete();

                    Mage::dispatchEvent('adminhtml_cmspage_on_delete', array('title' => $title, 'status' => 'success'));
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been deleted.', count($pages))
                );
            } catch (Exception $e) {
                Mage::dispatchEvent('adminhtml_cmspage_on_delete', array('title' => $title, 'status' => 'fail'));
                $this->_getSession()->addError($e->getMessage() . ' ' . $this->__('No Pages Deleted'));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Update page(s) status action
     *
     */
    public function massStatusAction() {

        $pages = $this->getRequest()->getParam('page');
        $status = $this->getRequest()->getParam('status');

        if (!is_array($pages)) {
            $this->_getSession()->addError($this->__('Please select page(s).'));
        } else {
            try {
                foreach ($pages as $pageId) {


                    $model = Mage::getModel('cms/page')->load($pageId);
                    $data = $model->getData();
                    $data = $this->_filterPostData($data);
                    $data['is_active'] = $status;
                    $data['stores'] = $data['store_id'];
                    unset($data['store_id']);

                    $model->setData($data);
                    Mage::dispatchEvent('cms_page_prepare_save', array('page' => $model, 'request' => $this->getRequest()));
                    $model->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have their status updated.', count($pages))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage() . ' ' . $this->__('No pages with status updated'));
            }
        }

        $this->_redirect('*/*/');
    }

    /**
     * Save action
     */
    public function massCopyAction() {

        $pages = $this->getRequest()->getParam('page');
        $store = $this->getRequest()->getParam('storeview');
        $suffix = $this->getRequest()->getParam('suffix');

        if (!is_array($pages)) {
            $this->_getSession()->addError($this->__('Please select page(s).'));
        } else {
            try {
                foreach ($pages as $pageId) {

                    $data = Mage::getModel('cms/page')->load($pageId)->getData();
                    $data = $this->_filterPostData($data);
                    $data['stores'] = array($store);

                    if (strlen($suffix) > 0) {
                        $data['identifier'] = $data['identifier'] . '-' . $suffix;
                    }

                    unset($data['store_id'], $data['page_id']);
                    $model = Mage::getModel('cms/page');

                    $model->setData($data);
                    Mage::dispatchEvent('cms_page_prepare_save', array('page' => $model, 'request' => $this->getRequest()));
                    $model->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been copied.', count($pages))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage() . ' ' . $this->__('No pages copied'));
            }
        }

        $this->_redirect('*/*/');
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed() {

        if (in_array($this->getRequest()->getActionName(), array('massDelete', 'massStatus', 'massCopy'))) {

            switch ($this->getRequest()->getActionName()) {
                case 'massCopy':
                case 'massStatus':
                    return Mage::getSingleton('admin/session')->isAllowed('cms/page/save');
                    break;
                case 'massDelete':
                    return Mage::getSingleton('admin/session')->isAllowed('cms/page/delete');
                    break;
            }
        }
        return parent::_isAllowed();
    }

}