<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Oaction
*/
class Amasty_Oaction_Model_Command_Abstract
{ 
    protected $_type       = '';
    protected $_label      = '';
    protected $_fieldLabel = '';
    
    protected $_errors    = array();    
    
    public function __construct($type='')
    {
        $this->_type = $type;
    }
    
    /**
     * Factory method. Creates a new command object by its type
     *
     * @param string $type command type
     * @return Amasty_Oaction_Model_Command_Abstract
     */
    public static function factory($type)
    {
        $className = 'Amasty_Oaction_Model_Command_' . ucfirst($type);
        return new $className($type);
    }
    
    
    /**
     * Command name.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->_label;
    }
    
    /**
     * Executes the command
     *
     * @param array $ids product ids
     * @param string $val field value
     * @return string success message if any
     */
    public function execute($ids, $val)
    {
        $this->_errors = array();
        
        $hlp = Mage::helper('amoaction');
        if (!is_array($ids)) {
            throw new Exception($hlp->__('Please select order(s)')); 
        }
//        if (!strlen($val)) {
//            throw new Exception($hlp->__('Please provide the value for the action')); 
//        }                  
               
        return '';
    }
    
    /**
     * Adds the command label to the mass actions list
     *
     * @param Mage_Adminhtml_Block_Widget_Grid_Massaction $block
     * @return Amasty_Oaction_Model_Command_Abstract
     */
    public function addAction($block)
    {
        $block->addItem('amoaction_' . $this->_type, array(
            'label'      => $block->__($this->_label),
		    'url'        => Mage::helper('adminhtml')->getUrl('amoaction/adminhtml_index/do/command/' . $this->_type), 
            'additional' => $this->_getValueField($block->__($this->_fieldLabel)),		    
        ));
        
        return $this;         
    }    
    
    /**
     * Returns value field options for the mass actions block
     *
     * @param string $title field title
     * @return array
     */
    protected function _getValueField($title)
    {
        return null;
        $yesno = array();
        $yesno[] = array('value'=>0, 'label'=>Mage::helper('catalog')->__('No'));
        $yesno[] = array('value'=>1, 'label'=>Mage::helper('catalog')->__('Yes'));
        
        $field = array('amoaction_value' => array(
            'name'   => 'amoaction_value',
            'type'   => 'select',
            'class'  => 'required-entry',
            'label'  => $title,
            'values' => $yesno,
        )); 
        return $field;       
    }
    
    /**
     * Gets list of not critical errors after the command execution
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;       
    }   
    

}