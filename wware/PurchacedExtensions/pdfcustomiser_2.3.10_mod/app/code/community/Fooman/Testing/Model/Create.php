<?php

class Fooman_Testing_Model_Create extends Mage_Adminhtml_Model_Sales_Order_Create {

    public function __construct()
    {
        $this->_session = Mage::getSingleton('foomantesting/session_quote');
    }

    /**
     * Resets all session data
     *
     * @return void
     */
    public function resetSession()
    {
        $session = $this->getSession();
        $session->resetAll();
        $this->_session = $session;
    }
    
}