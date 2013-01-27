<?php

include_once("Mage/Adminhtml/controllers/Cms/BlockController.php");


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


class Licentia_Ecms_Adminhtml_Cms_BlockController extends Mage_Adminhtml_Cms_BlockController {

    public function massDeleteAction() {

        $pages = $this->getRequest()->getParam('page');
        if (!is_array($pages)) {
            $this->_getSession()->addError($this->__('Please select block(s).'));
        } else {
            try {
                foreach ($pages as $id) {
                    $model = Mage::getModel('cms/block');
                    $model->load($id);
                    $title = $model->getTitle();
                    $model->delete();

                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been deleted.', count($pages))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage() . ' ' . $this->__('No blocks Deleted'));
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
            $this->_getSession()->addError($this->__('Please select block(s).'));
        } else {
            try {
                foreach ($pages as $pageId) {


                    $model = Mage::getModel('cms/block')->load($pageId);
                    $data = $model->getData();
                    $data['is_active'] = $status;
                    $data['stores'] = $data['store_id'];
                    unset($data['store_id']);

                    $model->setData($data);
                    $model->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have their status updated.', count($pages))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage() . ' ' . $this->__('No blocks with status updated'));
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
            $this->_getSession()->addError($this->__('Please select block(s).'));
        } else {
            try {
                foreach ($pages as $pageId) {

                    $data = Mage::getModel('cms/block')->load($pageId)->getData();
                    $data['stores'] = array($store);

                    if (strlen($suffix) > 0) {
                        $data['identifier'] = $data['identifier'] . '-' . $suffix;
                    }

                    unset($data['store_id'], $data['block_id']);

                    $model = Mage::getModel('cms/block');
                    $model->setData($data);
                    $model->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been copied.', count($pages))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage() . ' ' . $this->__('No blocks copied'));
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
                    return Mage::getSingleton('admin/session')->isAllowed('cms/block/save');
                    break;
                case 'massDelete':
                    return Mage::getSingleton('admin/session')->isAllowed('cms/block/delete');
                    break;
            }
        }
        return parent::_isAllowed();
    }

}