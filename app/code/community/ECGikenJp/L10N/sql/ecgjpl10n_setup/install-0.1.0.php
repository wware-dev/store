<?php
/**
 * ECGiken_Price
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */

$installer = $this;
$installer->startSetup();

$config = Mage::app()->getConfig();

$config->saveConfig('general/store_information/company', '会社名未設定');
$config->saveConfig('general/store_information/name', '店舗名未設定');
$config->saveConfig('general/store_information/phone', '店舗電話番号未設定');
$config->saveConfig('general/store_information/address', '〒000-0000
住所未設定
システム/設定/全般/店舗情報
にて設定を行なって下さい。');

// shipping methods
// flat rate
$config->saveConfig('carriers/flatrate/title', '全国一律');
$config->saveConfig('carriers/flatrate/name', '固定');
$config->saveConfig('carriers/flatrate/sallowspecific', true);
$config->saveConfig('carriers/flatrate/specificcountry', 'JP');

// free shipping
$config->saveConfig('carriers/freeshipping/title', '送料無料');
$config->saveConfig('carriers/freeshipping/name', '無料');
$config->saveConfig('carriers/freeshipping/sallowspecific', true);
$config->saveConfig('carriers/freeshipping/specificcountry', 'JP');

// table rate
$config->saveConfig('carriers/tablerate/title', '最安');
$config->saveConfig('carriers/tablerate/name', '料金表');
$config->saveConfig('carriers/tablerate/sallowspecific', true);
$config->saveConfig('carriers/tablerate/specificcountry', 'JP');

// payment methods
// saved cc
$config->saveConfig('payment/ccsave/active', false);
$config->saveConfig('payment/ccsave/title', '保存済クレジットカード');
$config->saveConfig('payment/ccsave/cctypes', 'AE,VI,MC,JCB');

// check / money order
$config->saveConfig('payment/checkmo/active', true);
$config->saveConfig('payment/checkmo/title', '現金');

// free
$config->saveConfig('payment/free/active', true);
$config->saveConfig('payment/free/title', '支払情報不要');

// Purchase Order
$config->saveConfig('payment/purchaseorder/active', false);
$config->saveConfig('payment/purchaseorder/title', '注文書');

// 404 Not Found Page
$page = Mage::getModel('cms/page')
    ->getCollection()
    ->addFieldToFilter(
        'identifier',
        array('eq' => 'no-route')
    )
    ->getFirstItem();
if ($page && $page->getId() <= 0) {
    $page = Mage::getModel('cms/page');
}
$page->setTitle('404 Not Found');
$page->setContent('<div class="not-found-bkg">
<div class="page-title">
        <h1>404 ERROR FILE NOT FOUND<br />指定されたページ（URL）は見つかりません。</h1>
</div>
    <ul class="no-disc">
        <li>大変申し訳ございません。<br />お客様のアクセスしたページ(URL)を、見つけることができません。</li>
        <li>お探しのページは現在アクセスできない状況にあるか<br />移動もしくは削除された可能性があります。</li>
        <li>まだ始まる前の期間限定キャンペーン商品は、<br />開催期間中に再度アクセスして頂きますようお願い致します。</li>
    </ul>
    <ul class="go-to-home">
        <li><strong>TOPページ</strong>
        <a href="{{store direct_url=""}}">{{store direct_url=""}}</a></li>
        <li><strong>サイトマップ</strong>
        <a href="{{store url="catalog/seo_sitemap/category"}}">{{store url="catalog/seo_sitemap/category"}}</a></li>
    </ul>
</div>');
$page->setStores(array(0));
$page->setIdentifier('no-route');
$page->save();

// mail template
$mail_template = Mage::getModel('core/email_template');
$mail_template
    ->setTemplateCode('受注メール')
    ->setTemplateType('1')
    ->setTemplateSubject('【{{config path="general/store_information/name"}}】ご注文ありがとうございます。')
    ->setTemplateText('{{var name}} 様

{{config path="general/store_information/name"}}でございます。

この度はご注文いただき誠に有難うございます。
下記ご注文内容にお間違えがないかご確認下さい。

{{layout handle="sales_email_order_details" order=$order}}

なお、このメールにお心あたりがない場合は、お手数ですが下記宛にお問い合わせ
いただけますようお願い申し上げます。

-------------------------------------------
MAIL: {{config path="trans_email/ident_sales/email"}}

TEL: {{config path="general/store_information/phone"}}
-------------------------------------------

今後とも{{config path="general/store_information/name"}}をよろしくお願いいたします。


******************************************
{{config path="general/store_information/name"}}
{{config path="general/store_information/address"}}
URL: {{store direct_url=""}}
MAIL: {{config path="trans_email/ident_sales/email"}}');
$mail_template->save();
$config->saveConfig('sales_email/order/template', $mail_template->getId());
$config->saveConfig('sales_email/order/guest_template', $mail_template->getId());

$mail_template = Mage::getModel('core/email_template');
$mail_template
    ->setTemplateCode('受注コメントメール')
    ->setTemplateType('1')
    ->setTemplateSubject('【{{config path="general/store_information/name"}}】ご注文につきまして。')
    ->setTemplateText('{{var name}} 様

{{config path="general/store_information/name"}}でございます。

すでに承っております以下の注文につきまして、コメントさせていただきます。

{{layout handle="sales_email_order_details" order=$order comment=$comment}}

なお、このメールにお心あたりがない場合は、お手数ですが下記宛にお問い合わせ
いただけますようお願い申し上げます。

-------------------------------------------
MAIL: {{config path="trans_email/ident_sales/email"}}

TEL: {{config path="general/store_information/phone"}}
-------------------------------------------

今後とも{{config path="general/store_information/name"}}をよろしくお願いいたします。


******************************************
{{config path="general/store_information/name"}}
{{config path="general/store_information/address"}}
URL: {{store direct_url=""}}
MAIL: {{config path="trans_email/ident_sales/email"}}');
$mail_template->save();
$config->saveConfig('sales_email/order_comment/template', $mail_template->getId());
$config->saveConfig('sales_email/order_comment/guest_template', $mail_template->getId());

$mail_template = Mage::getModel('core/email_template');
$mail_template
    ->setTemplateCode('領収メール')
    ->setTemplateType('1')
    ->setTemplateSubject('【{{config path="general/store_information/name"}}】ご入金を確認いたしました。')
    ->setTemplateText('{{var name}} 様

{{config path="general/store_information/name"}}でございます。

お客様からの入金(決済等)を確認致しました。
対応するご注文の情報は以下のとおりとなります。

{{layout handle="sales_email_order_details" order=$order}}

なお、このメールにお心あたりがない場合は、お手数ですが下記宛にお問い合わせ
いただけますようお願い申し上げます。

-------------------------------------------
MAIL: {{config path="trans_email/ident_sales/email"}}

TEL: {{config path="general/store_information/phone"}}
-------------------------------------------

今後とも{{config path="general/store_information/name"}}をよろしくお願いいたします。


******************************************
{{config path="general/store_information/name"}}
{{config path="general/store_information/address"}}
URL: {{store direct_url=""}}
MAIL: {{config path="trans_email/ident_sales/email"}}');
$mail_template->save();
$config->saveConfig('sales_email/invoice/template', $mail_template->getId());
$config->saveConfig('sales_email/invoice/guest_template', $mail_template->getId());

$mail_template = Mage::getModel('core/email_template');
$mail_template
    ->setTemplateCode('領収コメントメール')
    ->setTemplateType('1')
    ->setTemplateSubject('【{{config path="general/store_information/name"}}】領収内容につきまして。')
    ->setTemplateText('{{var name}} 様

{{config path="general/store_information/name"}}でございます。

すでに承っております以下の注文につきまして、ショップよりコメントさせていただきます。

{{layout handle="sales_email_order_details" order=$order comment=$comment}}

なお、このメールにお心あたりがない場合は、お手数ですが下記宛にお問い合わせ
いただけますようお願い申し上げます。

-------------------------------------------
MAIL: {{config path="trans_email/ident_sales/email"}}

TEL: {{config path="general/store_information/phone"}}
-------------------------------------------

今後とも{{config path="general/store_information/name"}}をよろしくお願いいたします。


******************************************
{{config path="general/store_information/name"}}
{{config path="general/store_information/address"}}
URL: {{store direct_url=""}}
MAIL: {{config path="trans_email/ident_sales/email"}}');
$mail_template->save();
$config->saveConfig('sales_email/invoice_comment/template', $mail_template->getId());
$config->saveConfig('sales_email/invoice_comment/guest_template', $mail_template->getId());

$mail_template = Mage::getModel('core/email_template');
$mail_template
    ->setTemplateCode('発送メール')
    ->setTemplateType('1')
    ->setTemplateSubject('【{{config path="general/store_information/name"}}】発送が完了致しました。')
    ->setTemplateText('{{var name}} 様

{{config path="general/store_information/name"}}でございます。

いつもお世話になります。
下記の商品の発送準備が整いましたので
ご連絡申し上げます。

{{layout handle="sales_email_order_details" order=$order shipment=$shipment comment=$comment}}

なお、このメールにお心あたりがない場合は、お手数ですが下記宛にお問い合わせ
いただけますようお願い申し上げます。

-------------------------------------------
MAIL: {{config path="trans_email/ident_sales/email"}}

TEL: {{config path="general/store_information/phone"}}
-------------------------------------------

今後とも{{config path="general/store_information/name"}}をよろしくお願いいたします。


******************************************
{{config path="general/store_information/name"}}
{{config path="general/store_information/address"}}
URL: {{store direct_url=""}}
MAIL: {{config path="trans_email/ident_sales/email"}}');
$mail_template->save();
$config->saveConfig('sales_email/shipment/template', $mail_template->getId());
$config->saveConfig('sales_email/shipment/guest_template', $mail_template->getId());

$mail_template = Mage::getModel('core/email_template');
$mail_template
    ->setTemplateCode('発送コメントメール')
    ->setTemplateType('1')
    ->setTemplateSubject('【{{config path="general/store_information/name"}}】発送につきまして。')
    ->setTemplateText('{{var name}} 様

{{config path="general/store_information/name"}}でございます。

すでに承っております以下の注文につきまして、ショップよりコメントさせていただきます。

{{layout handle="sales_email_order_details" order=$order shipment=$shipment comment=$comment}}

なお、このメールにお心あたりがない場合は、お手数ですが下記宛にお問い合わせ
いただけますようお願い申し上げます。

-------------------------------------------
MAIL: {{config path="trans_email/ident_sales/email"}}

TEL: {{config path="general/store_information/phone"}}
-------------------------------------------

今後とも{{config path="general/store_information/name"}}をよろしくお願いいたします。


******************************************
{{config path="general/store_information/name"}}
{{config path="general/store_information/address"}}
URL: {{store direct_url=""}}
MAIL: {{config path="trans_email/ident_sales/email"}}');
$mail_template->save();
$config->saveConfig('sales_email/shipment_comment/template', $mail_template->getId());
$config->saveConfig('sales_email/shipment_comment/guest_template', $mail_template->getId());

$mail_template = Mage::getModel('core/email_template');
$mail_template
    ->setTemplateCode('ニュースレター登録メール')
    ->setTemplateType('1')
    ->setTemplateSubject('【{{config path="general/store_information/name"}}】ニュースレターのご登録ありがとうございました。')
    ->setTemplateText('このたびは、ニュースレターにご登録いただき
ありがとうございました。

注目の新商品情報をはじめ、期間限定のたいへんお得なキャンペーン情報を
いち早くお届けいたします。

ぜひご利用いただけますようお願いいたします。

なお、このメールにお心あたりがない場合は、お手数ですが下記宛にお問い合わせ
いただけますようお願い申し上げます。

-------------------------------------------
MAIL: {{config path="trans_email/ident_sales/email"}}

TEL: {{config path="general/store_information/phone"}}
-------------------------------------------

今後とも{{config path="general/store_information/name"}}をよろしくお願いいたします。


******************************************
{{config path="general/store_information/name"}}
{{config path="general/store_information/address"}}
URL: {{store direct_url=""}}
MAIL: {{config path="trans_email/ident_sales/email"}}');
$mail_template->save();
$config->saveConfig('newsletter/subscription/success_email_template', $mail_template->getId());

$mail_template = Mage::getModel('core/email_template');
$mail_template
    ->setTemplateCode('ニュースレター解除メール')
    ->setTemplateType('1')
    ->setTemplateSubject('【{{config path="general/store_information/name"}}】ニュースレター配信停止のご連絡')
    ->setTemplateText('ニュースレターをこれまでご愛読いただきありがとうございました。

お客様宛のニュースレター配信停止の手続きが完了いたしました。

これまでご愛読いただき、誠にありがとうございました。

{{config path="general/store_information/name"}}ではニュースレターご登録者以外でもお買い物をお楽しみいただけます。
{{config path="general/store_information/name"}}を引き続きよろしくお願い申し上げます。

なお、このメールにお心あたりがない場合は、お手数ですが下記宛にお問い合わせ
いただけますようお願い申し上げます。

-------------------------------------------
MAIL: {{config path="trans_email/ident_sales/email"}}

TEL: {{config path="general/store_information/phone"}}
-------------------------------------------

今後とも{{config path="general/store_information/name"}}をよろしくお願いいたします。


******************************************
{{config path="general/store_information/name"}}
{{config path="general/store_information/address"}}
URL: {{store direct_url=""}}
MAIL: {{config path="trans_email/ident_sales/email"}}');
$mail_template->save();
$config->saveConfig('newsletter/subscription/un_email_template', $mail_template->getId());

$mail_template = Mage::getModel('core/email_template');
$mail_template
    ->setTemplateCode('ニュースレター登録確認メール')
    ->setTemplateType('1')
    ->setTemplateSubject('【{{config path="general/store_information/name"}}】ニュースレター登録のご確認')
    ->setTemplateText('この度はニュースレター会員登録依頼をいただきまして誠にありがとうございます。

ご登録いただきましたメールアドレスが確認できますとニュースレター登録完了となります。
お客様が間違いなく当サイトにてニュースレター登録を行なっている場合は、以下のリンクを
クリックしていただき、ニュースレター登録確認を行なってください。

{{var subscriber.getConfirmationLink()}}

なお、このメールにお心あたりがない場合は、お手数ですが下記宛にお問い合わせ
いただけますようお願い申し上げます。

-------------------------------------------
MAIL: {{config path="trans_email/ident_sales/email"}}

TEL: {{config path="general/store_information/phone"}}
-------------------------------------------

今後とも{{config path="general/store_information/name"}}をよろしくお願いいたします。


******************************************
{{config path="general/store_information/name"}}
{{config path="general/store_information/address"}}
URL: {{store direct_url=""}}
MAIL: {{config path="trans_email/ident_sales/email"}}');
$mail_template->save();
$config->saveConfig('newsletter/subscription/confirm_email_template', $mail_template->getId());

$mail_template = Mage::getModel('core/email_template');
$mail_template
    ->setTemplateCode('会員登録確認メール')
    ->setTemplateType('1')
    ->setTemplateSubject('【{{config path="general/store_information/name"}}】会員登録のご確認')
    ->setTemplateText('{{var name}} 様

{{config path="general/store_information/name"}}でございます。

この度は会員登録依頼をいただきまして誠にありがとうございます。

ご登録いただきましたメールアドレスが確認できますと会員登録完了となります。
お客様が間違いなく当サイトにて会員登録を行なっている場合は、以下のリンクを
クリックしていただき、登録確認を行なってください。

{{store url="customer/account/confirm/" _query_id=$customer.id _query_key=$customer.confirmation _query_back_url=$back_url}}

なお、このメールにお心あたりがない場合は、お手数ですが下記宛にお問い合わせ
いただけますようお願い申し上げます。

-------------------------------------------
MAIL: {{config path="trans_email/ident_sales/email"}}

TEL: {{config path="general/store_information/phone"}}
-------------------------------------------

今後とも{{config path="general/store_information/name"}}をよろしくお願いいたします。


******************************************
{{config path="general/store_information/name"}}
{{config path="general/store_information/address"}}
URL: {{store direct_url=""}}
MAIL: {{config path="trans_email/ident_sales/email"}}');
$mail_template->save();
$config->saveConfig('customer/create_account/email_confirmation_template', $mail_template->getId());

$mail_template = Mage::getModel('core/email_template');
$mail_template
    ->setTemplateCode('会員登録ウェルカムメール')
    ->setTemplateType('1')
    ->setTemplateSubject('【{{config path="general/store_information/name"}}】会員登録のご完了')
    ->setTemplateText('{{var name}} 様

{{config path="general/store_information/name"}}でございます。

この度は会員登録依頼をいただきまして誠にありがとうございます。

本会員登録が完了いたしました。
ショッピングをお楽しみくださいませ。

なお、このメールにお心あたりがない場合は、お手数ですが下記宛にお問い合わせ
いただけますようお願い申し上げます。

-------------------------------------------
MAIL: {{config path="trans_email/ident_sales/email"}}

TEL: {{config path="general/store_information/phone"}}
-------------------------------------------

今後とも{{config path="general/store_information/name"}}をよろしくお願いいたします。


******************************************
{{config path="general/store_information/name"}}
{{config path="general/store_information/address"}}
URL: {{store direct_url=""}}
MAIL: {{config path="trans_email/ident_sales/email"}}');
$mail_template->save();
$config->saveConfig('customer/create_account/email_template', $mail_template->getId());
$config->saveConfig('customer/create_account/email_confirmed_template', $mail_template->getId());

$mail_template = Mage::getModel('core/email_template');
$mail_template
    ->setTemplateCode('パスワードリセット確認メール')
    ->setTemplateType('1')
    ->setTemplateSubject('【{{config path="general/store_information/name"}}】パスワードリセットのご連絡')
    ->setTemplateText('{{var name}} 様

{{config path="general/store_information/name"}}をご利用いただきまして、
ありがとうございます。

パスワードリセットのご要望により本メールを送信しております。
間違いなくお客様がパスワードリセット要求を行われている場合は、以下のリン
クをクリックしてパスワードのリセットを行なってください。

{{store url="customer/account/resetpassword/" _query_id=$customer.id _query_token=$customer.rp_token}}

上記のリンクがうまく機能しない場合はブラウザに上記のリンクをコピー＆ペー
スト(改行を含まないようにご注意ください。)してください。

お客様がパスワードリセットの要求を行なっていない場合は、このメールを無視
するようにしてください。


-------------------------------------------
MAIL: {{config path="trans_email/ident_sales/email"}}

TEL: {{config path="general/store_information/phone"}}
-------------------------------------------

今後とも{{config path="general/store_information/name"}}をよろしくお願いいたします。


******************************************
{{config path="general/store_information/name"}}
{{config path="general/store_information/address"}}
URL: {{store direct_url=""}}
MAIL: {{config path="trans_email/ident_sales/email"}}');
$mail_template->save();
$config->saveConfig('customer/password/forgot_email_template', $mail_template->getId());

$mail_template = Mage::getModel('core/email_template');
$mail_template
    ->setTemplateCode('新しいパスワードメール')
    ->setTemplateType('1')
    ->setTemplateSubject('【{{config path="general/store_information/name"}}】パスワード変更のご連絡')
    ->setTemplateText('{{var name}} 様

{{config path="general/store_information/name"}}をご利用いただきまして、
ありがとうございます。

お客様の新しいパスワードは {{var $customer.password}} です。

尚、パスワードはマイページのアカウント情報よりご変更いただけます。

マイページのアカウント情報
{{store url="customer/account/edit"}}

なお、このメールにお心あたりがない場合は、お手数ですが下記宛にお問い合わせ
いただけますようお願い申し上げます。

-------------------------------------------
MAIL: {{config path="trans_email/ident_sales/email"}}

TEL: {{config path="general/store_information/phone"}}
-------------------------------------------

今後とも{{config path="general/store_information/name"}}をよろしくお願いいたします。


******************************************
{{config path="general/store_information/name"}}
{{config path="general/store_information/address"}}
URL: {{store direct_url=""}}
MAIL: {{config path="trans_email/ident_sales/email"}}');
$mail_template->save();
$config->saveConfig('customer/password/remind_email_template', $mail_template->getId());

$installer->endSetup();
