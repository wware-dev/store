<?php
abstract class Fooman_EmailAttachments_Test_Controller_Admin_Abstract extends EcomDev_PHPUnit_Test_Case_Controller
{
    const FAKE_USER_ID = 1;
    public function setUp()
    {
        parent::setUp();
        $this->createAdminSession();
    }
    public function tearDown()
    {
        $adminSession = Mage::getSingleton('admin/session');
        $adminSession->unsetAll();
        $adminSession->getCookie()->delete($adminSession->getSessionName());
        parent::tearDown();
    }

    protected function createAdminSession()
    {
        $this->_registerUserMock();
        Mage::getSingleton('adminhtml/url')->turnOffSecretKey();
        $adminSessionMock = $this->getModelMock('admin/session', array('renewSession','isAllowed'));

        $adminSessionMock->expects($this->any())
            ->method('isAllowed')
            ->will($this->returnValue(true));
        $this->replaceByMock('singleton', 'admin/session', $adminSessionMock);
        $adminSessionMock->login('fakeuser', 'fakeuser_pass');
        return $adminSessionMock;
    }

    /**
     * Creates a mock object for admin/user Magento Model
     *
     * @return My_Module_Test_Controller_Adminhtml_Controller
     */
    protected function _registerUserMock()
    {
        $user = $this->getModelMock('admin/user');
        $user->expects($this->any())->method('getId')->will($this->returnValue(self::FAKE_USER_ID));
        $this->replaceByMock('model', 'admin/user', $user);
        return $this;
    }
    /**
     * Test whether fake user successfully logged in
     */
    public function testLoggedIn()
    {
        $this->assertTrue(Mage::getSingleton('admin/session')->isLoggedIn());
    }
    /**
     * Test whether logged user is fake
     */
    public function testLoggedUserIsFakeUser()
    {
        $this->assertEquals(Mage::getSingleton('admin/session')->getUser()->getId(), self::FAKE_USER_ID);
    }

    protected function _workaroundAdminMenuIssue()
    {
        $menuBlock = $this->getBlockMock('adminhtml/page_menu', array('_toHtml'));
        $menuBlock->expects($this->any())
            ->method('_toHtml')
            ->will($this->returnCallback($this, '_getAdminhtmlPageMenuTemplate'));
        $this->replaceByMock('block', 'adminhtml/page_menu', $menuBlock);
    }

    protected function _getAdminhtmlPageMenuTemplate()
    {
        if (function_exists('drawMenuLevel')) {
            return '';
        }
        return Mage::getModel('adminhtml/page_menu')->renderView();
    }

}