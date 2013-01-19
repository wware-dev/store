<?php
 /**
 * GoMage Advanced Navigation Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2012 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.2
 * @since        Release available since Release 3.1
 */

$installer = $this;

$installer->startSetup();

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Border Radius, px',
        'input'             => 'text',
        'class'             => '',
		'frontend_class'    => 'gomage-validate-number',
        'source'            => '',
        'global'            => true,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,	
		'sort_order'		=> 385,	
    );
$installer->addAttribute('catalog_category', 'navigation_pw_m_bradius', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Selected Category Color',
        'input'             => 'text',
        'class'             => '',
        'source'            => '',
        'global'            => true,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
		'note'				=> 'e.g. "ffffff", "000"',	
		'sort_order'		=> 397,	
    );
$installer->addAttribute('catalog_category', 'navigation_pw_m_sccolor', $attribute_data);
   
$installer->endSetup(); 