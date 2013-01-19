<?php
/**
 * ECGiken_Price
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */

$installer = $this;
$installer->startSetup();

// translation
$product_attributes = array(
    'custom_design_to' => 'カスタムデザイン適用終了日',
    'status' => 'ステータス'
);
foreach ($product_attributes as $product_attribute => $product_label) {
    $installer->updateAttribute(
        'catalog_product',
        $product_attribute,
        'frontend_label',
        $product_label
    );
}

$installer->endSetup();
