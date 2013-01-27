<?php
/**
 * ECGiken_Price
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */

class ECGikenJp_I18n_Model_Customer_Resource_Customer_Collection extends Mage_Customer_Model_Resource_Customer_Collection {

    public function addNameToSelect() {
        $fields = array();
        $customerAccount = Mage::getConfig()->getFieldset('customer_account');
        foreach ($customerAccount as $code => $node) {
            if ($node->is('name')) {
                $fields[$code] = $code;
            }
        }
        $source_fields = array();
        foreach (preg_split('/, */u', Mage::getStoreConfig('customer/address_templates/name_sql')) as $source_field) {
            if (array_key_exists($source_field, $fields)) {
                if (!in_array($source_field, $source_fields)) {
                    $source_fields[] = $source_field;
                }
            }
        }

        $adapter = $this->getConnection();
        if (count($source_fields) == 0) {
            $concatenate = array();
            if (isset($fields['prefix'])) {
                $concatenate[] = $adapter->getCheckSql(
                    '{{prefix}} IS NOT NULL AND {{prefix}} != \'\'',
                    'LTRIM(RTRIM({{prefix}}))',
                    '\'\'');
            }
            $concatenate[] = 'LTRIM(RTRIM({{lastname}}))';
            if (isset($fields['middlename'])) {
                $concatenate[] = $adapter->getCheckSql(
                    '{{middlename}} IS NOT NULL AND {{middlename}} != \'\'',
                    'LTRIM(RTRIM({{middlename}}))',
                    '\'\'');
            }
            $concatenate[] = 'LTRIM(RTRIM({{firstname}}))';
            if (isset($fields['suffix'])) {
                $concatenate[] = $adapter
                    ->getCheckSql('{{suffix}} IS NOT NULL AND {{suffix}} != \'\'', "LTRIM(RTRIM({{suffix}}))", "''");
            }
            $nameExpr = $adapter->getConcatSql($concatenate, ' ');
        } else {
            foreach ($source_fields as $source_field) {
                $concatenate[] = $adapter->getCheckSql(
                    '{{' . $source_field . '}} IS NOT NULL AND {{' . $source_field . '}} != \'\'',
                    'LTRIM(RTRIM({{' . $source_field . '}}))',
                    '\'\'');
            }
            $nameExpr = $adapter->getConcatSql($concatenate, '');
        }

        $this->addExpressionAttributeToSelect('name', $nameExpr, $fields);

        return $this;
    }
}
