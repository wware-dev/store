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
$category_attributes = array(
    'is_anchor' => '階層ナビと下位カテゴリ商品の表示'
);
foreach ($category_attributes as $category_attribute => $category_attribute_label) {
    $installer->updateAttribute(
        'catalog_category',
        $category_attribute,
        'frontend_label',
        $category_attribute_label
    );
}

$product_attributes = array(
    'msrp_display_actual_price_type' => '実売価の表示方法(希望小売価格)',
    'msrp_enabled' => '希望小売価格を使用',
    'news_from_date' => '新商品表記開始日',
    'news_to_date' => '新商品表記終了日',
    'price_view' => '価格ビュー'
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
