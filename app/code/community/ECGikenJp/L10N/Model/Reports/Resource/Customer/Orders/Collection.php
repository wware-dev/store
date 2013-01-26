<?php
/**
 * ECGiken_Price
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */

class ECGikenJp_L10N_Model_Reports_Resource_Customer_Orders_Collection extends Mage_Reports_Model_Resource_Customer_Orders_Collection {

    public function joinCustomerName($alias = 'name') {
        $fields      = array('main_table.customer_lastname', 'main_table.customer_firstname');
        $fieldConcat = $this->getConnection()->getConcatSql($fields, ' ');
        $this->getSelect()->columns(array($alias => $fieldConcat));
        return $this;
    }
}
