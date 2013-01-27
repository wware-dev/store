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

class BL_CustomGrid_Helper_Config extends Mage_Core_Helper_Abstract
{
    const XML_GLOBAL_EXCEPTIONS_HANDLING_MODE = 'customgrid/global/exceptions_handling_mode';
    const XML_GLOBAL_EXCEPTIONS_LIST = 'customgrid/global/exceptions_list';
    const XML_GLOBAL_EXCLUSIONS_LIST = 'customgrid/global/exclusions_list';
    const XML_GLOBAL_STORE_PARAMETER = 'customgrid/global/store_parameter';
    const XML_GLOBAL_SORT_WITH_DND   = 'customgrid/global/sort_with_dnd';
    
    const GRID_EXCEPTION_HANDLING_EXCLUDE = 'exclude';
    const GRID_EXCEPTION_HANDLING_ALLOW   = 'allow';
    
    protected $_exclusions = null;
    protected $_exceptions = null;
    
    protected function _prepareExceptionPattern($pattern)
    {
        return str_replace(array('\\*', '\\.'), array('.*', '%'), '#' . preg_quote($pattern, '#') . '#i');
    }
    
    protected function _getSerializedArrayConfig($key)
    {
        $values = Mage::getStoreConfig($key);
        if (!is_array($values)) {
            /* 
            Unserialize values if needed 
            (should always be the case, as _afterLoad is not called with getStoreConfig)
            */
            $values = @unserialize($values);
            $values = (is_array($values) ? $values : array());
        }
        return $values;
    }
    
    protected function _getExceptionsListConfig($key)
    {
        $exceptions = $this->_getSerializedArrayConfig($key);
        
        foreach ($exceptions as $key => $exception) {
            // Prepare exceptions patterns
            $exceptions[$key] = array(
                'block_type' => $this->_prepareExceptionPattern($exception['block_type']),
                'rewriting_class_name' => $this->_prepareExceptionPattern($exception['rewriting_class_name']),
            );
        }
        
        return $exceptions;
    }
    
    protected function _matchGridAgainstException($exception, $blockType, $rewritingClassName)
    {
        return (preg_match($exception['block_type'], $blockType)
                && preg_match($exception['rewriting_class_name'], $rewritingClassName));
    }
    
    public function getExclusionsList()
    {
        if (is_null($this->_exclusions)) {
            $this->_exclusions = $this->_getExceptionsListConfig(self::XML_GLOBAL_EXCLUSIONS_LIST);
        }
        return $this->_exclusions;
    }
    
    public function getExceptionsHandlingMode()
    {
        return Mage::getStoreConfig(self::XML_GLOBAL_EXCEPTIONS_HANDLING_MODE);
    }
    
    public function getExceptionsList()
    {
        if (is_null($this->_exceptions)) {
            $this->_exceptions = $this->_getExceptionsListConfig(self::XML_GLOBAL_EXCEPTIONS_LIST);
        }
        return $this->_exceptions;
    }
    
    public function isExcludedGrid($blockType, $rewritingClassName)
    {
        foreach ($this->getExclusionsList() as $exclusion) {
            if ($this->_matchGridAgainstException($exclusion, $blockType, $rewritingClassName)) {
                return true;
            }
        }
        
        foreach ($this->getExceptionsList() as $exception) {
            if ($this->_matchGridAgainstException($exception, $blockType, $rewritingClassName)) {
                return ($this->getExceptionsHandlingMode() == self::GRID_EXCEPTION_HANDLING_EXCLUDE);
            }
        }
        
        return ($this->getExceptionsHandlingMode() == self::GRID_EXCEPTION_HANDLING_ALLOW);
    }
    
    public function getStoreParameter($default=null)
    {
        return (strlen($value = Mage::getStoreConfig(self::XML_GLOBAL_STORE_PARAMETER)) ? $value : $default);
    }
    
    public function getSortWithDnd()
    {
        return Mage::getStoreConfig(self::XML_GLOBAL_SORT_WITH_DND);
    }
}