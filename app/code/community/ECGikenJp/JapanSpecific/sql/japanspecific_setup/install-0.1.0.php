<?php
/**
 * ECGiken_Price
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */

$installer = $this;
$installer->startSetup();

$tax_calculation_rate = Mage::getModel('tax/calculation_rate')
    ->setTaxCountryId('JP')
    ->setTaxRegionId(0)
    ->setTaxPostcode('*')
    ->setCode('JP-*-*-Rate')
    ->setRate(5.0000)
    ->save();
$customer_tax_class = Mage::getModel('tax/class')
    ->getCollection()
    ->addFieldToFilter(
        'class_name',
        array(
            'eq' => 'Retail Customer'
        )
    )
    ->getFirstItem();
$product_tax_class = Mage::getModel('tax/class')
    ->getCollection()
    ->addFieldToFilter(
        'class_name',
        array(
            'eq' => 'Taxable Goods'
        )
    )
    ->getFirstItem();
$installer->run("
    UPDATE `{$installer->getTable('tax/tax_calculation_rule')}` SET priority=priority+1, position=position+1;
");
$tax_calculation_rule = Mage::getModel('tax/calculation_rule')
    ->setCode('Retail Customer-Taxable Goods-Rate')
    ->setData('tax_customer_class', array())
    ->setData('tax_product_class', array())
    ->setData('tax_rate', array())
    ->setPriority(1)
    ->setPosition(1)
    ->save();
$tax_calculation = Mage::getModel('tax/calculation')
    ->setTaxCalculationRateId($tax_calculation_rate->getId())
    ->setTaxCalculationRuleId($tax_calculation_rule->getId())
    ->setCustomerTaxClassId($customer_tax_class->getId())
    ->setProductTaxClassId($product_tax_class->getId())
    ->save();

$regions = array(
    'Hokkaido' => '北海道',
    'Aomori' => '青森県',
    'Iwate' => '岩手県',
    'Miyagi' => '宮城県',
    'Akita' => '秋田県',
    'Yamagata' => '山形県',
    'Fukushima' => '福島県',
    'Ibaragi' => '茨城県',
    'Tochigi' => '栃木県',
    'Gunma' => '群馬県',
    'Saitama' => '埼玉県',
    'Chiba' => '千葉県',
    'Tokyo' => '東京都',
    'Kanagawa' => '神奈川県',
    'Niigata' => '新潟県',
    'Toyama' => '富山県',
    'Ishikawa' => '石川県',
    'Fukui' => '福井県',
    'Yamanashi' => '山梨県',
    'Nagano' => '長野県',
    'Gifu' => '岐阜県',
    'Shizuoka' => '静岡県',
    'Aichi' => '愛知県',
    'Mie' => '三重県',
    'Shiga' => '滋賀県',
    'Kyoto' => '京都府',
    'Osaka' => '大阪府',
    'Hyogo' => '兵庫県',
    'Nara' => '奈良県',
    'Wakayama' => '和歌山県',
    'Tottori' => '鳥取県',
    'Shimane' => '島根県',
    'Okayama' => '岡山県',
    'Hiroshima' => '広島県',
    'Yamaguchi' => '山口県',
    'Tokushima' => '徳島県',
    'Kagawa' => '香川県',
    'Ehime' => '愛媛県',
    'Kochi' => '高知県',
    'Fukuoka' => '福岡県',
    'Saga' => '佐賀県',
    'Nagasaki' => '長崎県',
    'Kumamoto' => '熊本県',
    'Oita' => '大分県',
    'Miyazaki' => '宮崎県',
    'Kagoshima' => '鹿児島県',
    'Okinawa' => '沖縄県'
);
foreach ($regions as $region_key => $region_name) {
    $sql = "
        INSERT INTO `{$installer->getTable('directory/country_region')}`(
            country_id,
            code,
            default_name
        )
        VALUES(
          'JP',
          '$region_key',
          '$region_name'
        );
    ";
    $installer->run($sql);
}

$config = Mage::app()->getConfig();

// general
$config->saveConfig('general/local/code', 'ja_JP');
$config->saveConfig('general/local/timezone', 'Asia/Tokyo');
$config->saveConfig('general/country/default', 'JP');
$config->saveConfig('general/country/allow', 'JP');
$config->saveConfig('general/locale/firstday', 0);
$config->saveConfig('general/locale/weekend', '0,6');
$config->saveConfig('currency/options/base', 'JPY');
$config->saveConfig('currency/options/default', 'JPY');
$config->saveConfig('currency/options/allow', 'JPY');

// web
$config->saveConfig('web/seo/use_rewrites', true);

// tax
$config->saveConfig('tax/calculation/algorithm', 'TOTAL_BASE_CALCULATION');
$config->saveConfig('tax/calculation/apply_after_discount', '0');
$config->saveConfig('tax/calculation/apply_tax_on', '0');
$config->saveConfig('tax/calculation/based_on', 'shipping');
$config->saveConfig('tax/calculation/discount_tax', '1');
$config->saveConfig('tax/calculation/price_includes_tax', '1');
$config->saveConfig('tax/calculation/shipping_includes_tax', '1');
$config->saveConfig('tax/cart_display/full_summary', '0');
$config->saveConfig('tax/cart_display/grandtotal', '0');
$config->saveConfig('tax/cart_display/price', '2');
$config->saveConfig('tax/cart_display/shipping', '2');
$config->saveConfig('tax/cart_display/subtotal', '2');
$config->saveConfig('tax/cart_display/zero_tax', '0');
$config->saveConfig('tax/classes/shipping_tax_class', '2');
$config->saveConfig('tax/defaults/country', 'JP');
$config->saveConfig('tax/defaults/postcode', '*');
$config->saveConfig('tax/defaults/region', '0');
$config->saveConfig('tax/display/shipping', '2');
$config->saveConfig('tax/display/type', '2');
$config->saveConfig('tax/sales_display/full_summary', '0');
$config->saveConfig('tax/sales_display/grandtotal', '0');
$config->saveConfig('tax/sales_display/price', '2');
$config->saveConfig('tax/sales_display/shipping', '2');
$config->saveConfig('tax/sales_display/subtotal', '2');
$config->saveConfig('tax/sales_display/zero_tax', '0');
$config->saveConfig('tax/weee/apply_vat', '0');
$config->saveConfig('tax/weee/discount', '0');
$config->saveConfig('tax/weee/display', '0');
$config->saveConfig('tax/weee/display_email', '0');
$config->saveConfig('tax/weee/display_list', '0');
$config->saveConfig('tax/weee/display_sales', '0');
$config->saveConfig('tax/weee/enable', '0');
$config->saveConfig('tax/weee/include_in_subtotal', '0');

// origin
$config->saveConfig('shipping/origin/country_id', 'JP');

$installer->endSetup();
