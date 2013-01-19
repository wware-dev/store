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
 * @since        Class available since Release 3.2
 */
 		
class GoMage_Navigation_Block_Adminhtml_Config_Form_Renderer_Shopby extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $after_element_html = $element->getAfterElementHtml();
        $javaScript = "
            <script type=\"text/javascript\">
            	if('{$element->getHtmlId()}' == 'gomage_navigation_category_show_shopby')
            	{
            		var sel_cat = $('gomage_navigation_category_filter_type');
            		
            		Event.observe('{$element->getHtmlId()}', 'change', function(){
	                    var value = $('{$element->getHtmlId()}').value;                    
	                    if (value == 1){
	                    	for(i=sel_cat.options.length-1;i>=0;i--)
							{
								if (sel_cat.options[i].value == '8'
										||
									sel_cat.options[i].value == '6')
		                    	{
		                    		sel_cat.remove(i);
		                    	}
								
							}
                    	
	    				}else{
	    					var option_fly = false;
	    					var option_plain = false;
	    					for(i=sel_cat.options.length-1;i>=0;i--)
							{
								if (sel_cat.options[i].value == '8')
		                    	{
		                    		option_fly = true;
		                    	}
		                    	
		                    	if (sel_cat.options[i].value == '6')
		                    	{
		                    		option_plain = true;
		                    	}
							}
							
	    					if ( !option_plain )
							{
								sel_cat.options[sel_cat.options.length] = new Option('Plain', '6');
							}
							
							if ( !option_fly )
							{
								sel_cat.options[sel_cat.options.length] = new Option('Fly-Out', '8');
							}
	    				}
	                });
            	}
            	else if('{$element->getHtmlId()}' == 'gomage_navigation_rightcolumnsettings_show_shopby')
            	{
            		var sel_right = $('gomage_navigation_rightcolumnsettings_filter_type');
            		
            		Event.observe('{$element->getHtmlId()}', 'change', function(){
	                    var value = $('{$element->getHtmlId()}').value;                    
	                    if (value == 1){
	                    	for(i=sel_right.options.length-1;i>=0;i--)
							{
								if (sel_right.options[i].value == '8'
										||
									sel_right.options[i].value == '6'
									)
		                    	{
		                    		sel_right.remove(i);
		                    	}
								
							}
                    	
	    				}else{
	    					var option_fly = false;
	    					var option_plain = false;
	    					for(i=sel_right.options.length-1;i>=0;i--)
							{
								if (sel_right.options[i].value == '8')
		                    	{
		                    		option_fly = true;
		                    	}
		                    	
		                    	if (sel_right.options[i].value == '6')
		                    	{
		                    		option_plain = true;
		                    	}
							}
							
	    					if ( !option_plain )
							{
								sel_right.options[sel_right.options.length] = new Option('Plain', '6');
							}
							
							if ( !option_fly )
							{
								sel_right.options[sel_right.options.length] = new Option('Fly-Out', '8');
							}
	    				}
	                });
            	}
            	
                
                document.observe('dom:loaded', function() {   	
                	init_{$element->getHtmlId()}();                	
                });
                document.onreadystatechange = init_{$element->getHtmlId()};
                
                function init_{$element->getHtmlId()}() {
                	Gomage_Navigation_fireEvent($('{$element->getHtmlId()}'), 'change');
                }
            </script>";
        
        $element->setData('after_element_html', $javaScript . $after_element_html);
        
        return $element->getElementHtml();
    }
        
}