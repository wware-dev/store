<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   BL
 * @package    BL_CustomGrid
 * @copyright  Copyright (c) 2011 Benoît Leulliette <benoit.leulliette@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class BL_CustomGrid_Column_Renderer_AttributeController
    extends Mage_Adminhtml_Controller_Action
{
    protected function _initRenderer()
    {
        if ($code = $this->getRequest()->getParam('code')) {
            $renderer = Mage::getSingleton('customgrid/column_renderer_attribute')->getConfigAsObject($code);
            if ($renderer->isEmpty()) {
                $renderer = null;
            }
        } else {
            $renderer = null;
        }
        Mage::register('current_attribute_column_renderer', $renderer);
        return $renderer;
    }
    
    public function indexAction()
    {
        if ($renderer = $this->_initRenderer()) {
            $this->loadLayout('empty');
            if (($params = $this->getRequest()->getParam('params'))
                && ($block = $this->getLayout()->getBlock('column_renderer_attribute'))) {
                $params = Mage::getSingleton('customgrid/column_renderer_attribute')->decodeParameters($params);
                $block->setRendererParams($params);
            }
            $this->renderLayout();
        } else {
            $this->loadLayout(array(
                'empty', 
                strtolower($this->getFullActionName()),
                'customgrid_column_renderer_attribute_unknown',
            ))->renderLayout();
        }
    }
    
    public function buildRendererAction()
    {
        $params  = $this->getRequest()->getPost('parameters', array());
        $encoded = Mage::getSingleton('customgrid/column_renderer_attribute')->encodeParameters($params);
        $this->getResponse()->setBody($encoded);
    }
    
    protected function _isAllowed()
    {
        $session = Mage::getSingleton('admin/session');
        return ($session->isAllowed('system/customgrid/customization')
                || $session->isAllowed('system/customgrid/grids'));
    }
}