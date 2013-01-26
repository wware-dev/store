<?php
$installer = $this;
$installer->startSetup();

$installer->AddAttribute(
    'quote_payment',
    'gmo_access_id',
    array(
        'visible'=> false
    )
);
$installer->AddAttribute(
    'quote_payment',
    'gmo_access_pass',
    array(
        'visible'=> false
    )
);
$installer->AddAttribute(
    'quote_payment',
    'gmo_approve',
    array(
        'visible'=> false
    )
);
$installer->AddAttribute(
    'quote_payment',
    'gmo_tran_id',
    array(
        'visible'=> false
    )
);
$installer->AddAttribute(
    'order_payment',
    'gmo_access_id',
    array(
        'visible'=> false
    )
);
$installer->AddAttribute(
    'order_payment',
    'gmo_access_pass',
    array(
        'visible'=> false
    )
);
$installer->AddAttribute(
    'order_payment',
    'gmo_approve',
    array(
        'visible'=> false
    )
);
$installer->AddAttribute(
    'order_payment',
    'gmo_tran_id',
    array(
        'visible'=> false
    )
);
$installer->endSetup();

