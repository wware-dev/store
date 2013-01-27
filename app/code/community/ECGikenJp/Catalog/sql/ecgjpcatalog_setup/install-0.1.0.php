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
    'available_sort_by' => '並べ替え項目',
    'custom_apply_to_products' => '商品にも適用する',
    'custom_design' => 'カスタムデザイン',
    'custom_design_from' => 'カスタムデザイン適用開始日',
    'custom_design_to' => 'カスタムデザイン適用終了日',
    'custom_layout_update' => 'レイアウトXML',
    'custom_use_parent_settings' => '親カテゴリの設定を使用する',
    'default_sort_by' => '既定の並べ替え項目',
    'description' => '詳細説明',
    'display_mode' => '表示モード',
    'filter_price_range' => '価格表示幅',
    'image' => '画像',
    'include_in_menu' => 'ナビゲーションメニューに表示する',
    'is_active' => '有効',
    'is_anchor' => 'アンカー',
    'landing_page' => 'CMSブロック',
    'level' => 'レベル',
    'meta_description' => 'meta要素(description)',
    'meta_keywords' => 'meta要素(keywords)',
    'meta_title' => 'meta要素(title)',
    'name' => 'カテゴリ名',
    'page_layout' => 'ページレイアウト',
    'path' => 'カテゴリパス',
    'position' => '位置',
    'thumbnail' => 'サムネイル画像',
    'url_key' => 'URLキー',
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
    'color' => '色',
    'cost' => '原価',
    'country_of_manufacture' => '製造国',
    'custom_design' => 'カスタムデザイン',
    'custom_design_from' => 'カスタムデザイン適用開始日',
    'custom_design_to' => 'カスタムでアイン適用終了日',
    'custom_layout_update' => 'レイアウトXML',
    'description' => '詳細説明',
    'enable_googlecheckout' => 'Googleチェックアウトを有効にする',
    'gallery' => '画像',
    'gift_message_available' => 'ギフトメッセージ許可',
    'image' => '基本画像',
    'image_label' => '画像ラベル',
    'is_recurring' => '定期購入を有効にする',
    'links_purchased_separately' => 'リンクを個別購入可能',
    'links_title' => 'リンクタイトル',
    'manufacturer' => '製造業者',
    'media_gallery' => 'メディアギャラリー',
    'meta_description' => 'meta要素(description)',
    'meta_keyword' => 'meta要素(keywords)',
    'meta_title' => 'meta要素(title)',
    'minimal_price' => '最低価格',
    'msrp' => '希望小売価格',
    'msrp_display_actual_price_type' => 'Display Actual Price',
    'msrp_enabled' => 'Apply MAP',
    'name' => '商品名',
    'news_from_date' => 'Set Product as New from Date',
    'news_to_date' => 'Set Product as New to Date',
    'options_container' => 'オプション表示',
    'page_layout' => 'ページレイアウト',
    'price' => '価格',
    'price_view' => 'Price View',
    'recurring_profile' => '定期購入',
    'samples_title' => 'サンプルタイトル',
    'shipment_type' => '出荷タイプ',
    'short_description' => '概要',
    'sku' => 'SKU',
    'small_image' => '小画像',
    'small_image_label' => '小画像ラベル',
    'special_from_date' => '特別価格開始日',
    'special_price' => '特別価格',
    'special_to_date' => '特別価格終了日',
    'status' => '状態',
    'tax_class_id' => '税区分',
    'thumbnail' => 'サムネイル',
    'thumbnail_label' => 'サムネイルラベル',
    'tier_price' => '階層価格',
    'url_key' => 'URLキー',
    'visibility' => '可視性',
    'weight' => '重量'
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
