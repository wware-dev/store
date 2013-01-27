<?php
class ECGikenJp_L10N_Model_Reports_Resource_Order_Collection extends Mage_Reports_Model_Resource_Order_Collection {

    public function joinCustomerName($alias = 'name') {
        $fields      = array('main_table.customer_lastname', 'main_table.customer_firstname');
        $fieldConcat = $this->getConnection()->getConcatSql($fields, ' ');
        $this->getSelect()->columns(array($alias => $fieldConcat));
        return $this;
    }
}
