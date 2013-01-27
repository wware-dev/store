<?php
class ECGiken_Gmo_Block_System_Config_Filepath extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $message = "";
        if(file_exists("lib/com") && file_exists("lib/conf/connector.properties")) {
            $message .= "<b>Install Dir : </b>[Magento Root Dir]/lib/<br />";
            $message .= "<b>".Mage::Helper('ecggmo')->__('GMO module is installed.')."</b><br />";
        }else{
            $message = "<font color='red'><b>".Mage::Helper('ecggmo')->__('Please install the GMO module first.')."</b></font><br />";
        }
        return $message;
    }

}

