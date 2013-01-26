<?php

class EM_Productsfilterwidget_Model_Rule extends Mage_SalesRule_Model_Rule
{
    public function getActionsInstance()
    {
        return Mage::getModel('productsfilterwidget/rule_combine');
    }

    public function getConditionsInstance()
    {
        return Mage::getModel('productsfilterwidget/rule_combine');
    }
}