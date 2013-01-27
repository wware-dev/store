<?php
$installer = $this;
$installer->startSetup();

$installer->AddAttribute(
    'quote_payment',
    'gmo_order_id',
    array(
        'visible'=> false
    )
);
$installer->AddAttribute(
    'order_payment',
    'gmo_order_id',
    array(
        'visible'=> false
    )
);

