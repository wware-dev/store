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
 * @since        Class available since Release 3.1
 */
class GoMage_Navigation_Helper_Checkout_Cart extends Mage_Checkout_Helper_Cart
{

    /**
     * Retrieve current url
     *
     * @return string
     */
    public function getCurrentUrl()
    {          	  
        $url = parent::getCurrentUrl();
        if (Mage::helper('gomage_navigation')->isGomageNavigationAjax()){
        	$url = $this->removeRequestParam($url, 'ajax');
        }
        return $url;
    }
    
    /**
     * Remove request parameter from url
     *
     * @param string $url
     * @param string $paramKey
     * @return string
     */
    public function removeRequestParam($url, $paramKey, $caseSensitive = false)
    {
        $regExpression = '/\\?[^#]*?(' . preg_quote($paramKey, '/') . '\\=[^#&]*&?)/' . ($caseSensitive ? '' : 'i');
        while (preg_match($regExpression, $url, $mathes) != 0) {
            $paramString = $mathes[1];
            if (preg_match('/&$/', $paramString) == 0) {
                $url = preg_replace('/(&|\\?)?' . preg_quote($paramString, '/') . '/', '', $url);
            } else {
                $url = str_replace($paramString, '', $url);
            }
        }
        return $url;
    }
}
