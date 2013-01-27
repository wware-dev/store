<?php

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


class Licentia_Ecms_Block_Adminhtml_Block_Grid extends Mage_Adminhtml_Block_Cms_Block_Grid {

    protected function _prepareMassaction() {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('page');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('ecms')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('ecms')->__('Are you sure?')
        ));

        $statuses = array(
                        array('value'=>'0','label' => Mage::helper('ecms')->__('Disabled')),
                        array('value'=>'1','label' => Mage::helper('ecms')->__('Enabled')),
                        );

        array_unshift($statuses, array('label' => '', 'value' => ''));


        $this->getMassactionBlock()->addItem('status', array(
            'label' => Mage::helper('ecms')->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('ecms')->__('Status'),
                    'values' => $statuses
                )
            )
        ));

        $storesViewList = array();
        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $storesViewList[$store->getId()] = $website->getName() . ' / ' . $group->getName() . ' / ' . $store->getName();
                }
            }
        }


        $this->getMassactionBlock()->addItem('copyView', array(
            'label' => Mage::helper('ecms')->__('Copy to Store View'),
            'url' => $this->getUrl('*/*/massCopy', array('_current' => true)),
            'additional' => array(
                'store' => array(
                    'name' => 'storeview',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'style' => 'max-width:100px',
                    'label' => Mage::helper('ecms')->__('Store View'),
                    'values' => $storesViewList
                ),
                'suffix' => array(
                    'name' => 'suffix',
                    'type' => 'text',
                    'style' => 'width:50px',
                    'label' => Mage::helper('ecms')->__('Suffix'),
                )
            )
        ));

        return $this;
    }
}
