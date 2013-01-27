/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
 
 var action;


	function refe(aa){
		$('loadimage').show();
		var form	=	$('widget_options_form');
		$('success_string').value	=	'';
		if(!form){
			form	=	$('edit_form');
			action = $('edit_form').getAttribute('action');
		}
		else
			action = $('widget_options_form').getAttribute('action');
		

		form.writeAttribute('action', aa);
		form.request({
		method: 'post',
		onComplete: showResponse
})
				
	}
	function showResponse(originalRequest)
	{
		$('loadimage').hide();
		var mass	=	"Apply is success . Click \" insert widget \" to continous . ";
		var form	=	$('widget_options_form');
		if(!form)	$('edit_form').writeAttribute('action',action);
		else	$('widget_options_form').writeAttribute('action',action);
		  
		$('options_fieldsetb5c7a9b92940228db609a848c8129206_conditions').value = originalRequest.responseText;
		$('success_string').value	=	mass;
	}
	