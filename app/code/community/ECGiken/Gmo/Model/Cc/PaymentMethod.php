<?php
require_once( 'com/gmo_pg/client/input/EntryTranInput.php');
require_once( 'com/gmo_pg/client/input/ExecTranInput.php');
require_once( 'com/gmo_pg/client/input/EntryExecTranInput.php');
require_once( 'com/gmo_pg/client/tran/EntryExecTran.php');
require_once( 'com/gmo_pg/client/input/AlterTranInput.php');
require_once( 'com/gmo_pg/client/tran/AlterTran.php');
class ECGiken_Gmo_Model_Cc_PaymentMethod extends Mage_Payment_Model_Method_Cc 
{

    protected $_code = "ecggmo_cc";
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = true;
    protected $_canVoid = true;
    protected $_canCancel = true;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_canSaveCc = false;
    protected $_formBlockType = 'ecggmo/form_cc';
//    protected $_infoBlockType = 'ecggmo/info_cc';
    protected $paygent;
    protected $comm_helper;
    protected $orderPayment;

//    public function __construct() {
//        $this->paygent = Mage::helper('ecgpaygent/paygent');
//        $this->comm_helper = Mage::helper('ecggmo');
//    }

    // バックエンドで使用できるかどうかを返すメソッド（コンフィグで設定可能）
    public function canUseInternal()
    {
        if($this->getConfigData('can_use_internal')) {
            return true;
        }
        return false;
    }

    public function isAvailable($quote = null) {
        if (Mage_Payment_Model_Method_Abstract::isAvailable($quote)) {
            return true;
        }
        return false;
    }

    public function assignData($data) {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        $info->setCcType($data->getCcType())
            ->setCcOwner($data->getCcOwner())
            ->setCcLast4(substr($data->getCcNumber(), -4))
            ->setCcNumber($data->getCcNumber())
            ->setCcNumberEnc(Mage::helper('core')->encrypt($data->getCcNumber()))
            ->setCcExpMonth($data->getCcExpMonth())
            ->setCcExpYear($data->getCcExpYear())
            ->setCcCid($data->getCcCid());
        return $this;
    }

    // テストカードを通すために、テストモードではバリデートしないようにする
    public function validate() {
        if( !$this->getConfigData('test') ) {
            return parent::validate();
        }
        return $this;
    }

    public function authorize(Varien_Object $payment, $amount) {
        $this->_entryExecTran($payment, $amount, 'AUTH');
        return $this;
    }

    protected function _entryExecTran(Varien_Object $payment, $amount, $jobcd) {
        $this->getOrderPayment($payment);
        $this->comm_helper = Mage::helper('ecggmo');
        $info = $this->getInfoInstance();
        $gmoOrderId = date('His').'-'.$this->getOrderId($payment);

        $entryInput = new EntryTranInput();
        $entryInput->setShopId($this->comm_helper->getCommonConfigData('shop_id'));
        $entryInput->setShopPass($this->comm_helper->getCommonConfigData('shop_pass'));
        $entryInput->setJobCd($jobcd);
        $entryInput->setOrderId($gmoOrderId);
        $entryInput->setAmount($this->comm_helper->getPriceText($amount));
        $entryInput->setTdFlag('0');
        //$entryInput->setItemCode("");
        //$entryInput->setTax(0);
        //$entryInput->setTdTenantName("");

        $execInput = new ExecTranInput();
        $execInput->setOrderId($gmoOrderId);
        $execInput->setMethod('1');
        $execInput->setCardNo($info->getCcNumber());
        $execInput->setExpire(substr($info->getCcExpYear(), -2).substr("00".$info->getCcExpMonth(), -2));
        $execInput->setSecurityCode($info->getCcCid());

        $input = new EntryExecTranInput();
        $input->setEntryTranInput( $entryInput );
        $input->setExecTranInput( $execInput );

        $exe = new EntryExecTran();
        $output = $exe->exec( $input );
        if( $exe->isExceptionOccured() ){
            Mage::throwException($this->__('GMO request failed'));
        }else{
            if( $output->isErrorOccurred() ){
                Mage::throwException($this->__('GMO request failed'));
            }
        }
        $this->orderPayment->setTransactionId($output->getTranId());
        $this->orderPayment->setIsTransactionClosed(false);
        $info->setGmoAccessId($output->getAccessId());
        $info->setGmoAccessPass($output->getAccessPass());
        $info->setGmoApprove($output->getApprovalNo());
        $info->setGmoTranId($output->getTranId());
        $info->setGmoOrderId($gmoOrderId);
        return $this;
    }

    public function capture(Varien_Object $payment, $amount){
        if( $this->getConfigData('payment_action') === 'authorize_capture' ) {
            $this->_entryExecTran($payment, $amount, 'CAPTURE');
        }else{
            $this->getOrderPayment($payment);
            $this->comm_helper = Mage::helper('ecggmo');
            $info = $this->getInfoInstance();

            $input = new AlterTranInput();
            $input->setShopId($this->comm_helper->getCommonConfigData('shop_id'));
            $input->setShopPass($this->comm_helper->getCommonConfigData('shop_pass'));
            $input->setAccessId($info->getGmoAccessId());
            $input->setAccessPass($info->getGmoAccessPass());
            $input->setJobCd('SALES');
            $input->setAmount($this->comm_helper->getPriceText($amount));
            $input->setMethod('1');

            $exe = new AlterTran();
            $output = $exe->exec( $input );
            if( $exe->isExceptionOccured() ){
                Mage::throwException($this->__('GMO request failed'));
            }else{
                if( $output->isErrorOccurred() ){
                    Mage::throwException($this->__('GMO request failed'));
                }
            }
            $this->orderPayment->setIsTransactionClosed(true);
            $info->setGmoAccessId($output->getAccessId());
            $info->setGmoAccessPass($output->getAccessPass());
            $info->setGmoApprove($output->getApprovalNo());
            $info->setGmoTranId($output->getTranId());
        }

        return $this;
    }

    protected function _alterTran(Varien_Object $payment, $jobcd){
        $this->getOrderPayment($payment);
        $this->comm_helper = Mage::helper('ecggmo');
        $info = $this->getInfoInstance();

        $input = new AlterTranInput();
        $input->setShopId($this->comm_helper->getCommonConfigData('shop_id'));
        $input->setShopPass($this->comm_helper->getCommonConfigData('shop_pass'));
        $input->setAccessId($info->getGmoAccessId());
        $input->setAccessPass($info->getGmoAccessPass());
        $input->setJobCd($jobcd);

        $exe = new AlterTran();
        $output = $exe->exec( $input );
        if( $exe->isExceptionOccured() ){
            Mage::throwException($this->__('GMO request failed'));
        }else{
            if( $output->isErrorOccurred() ){
                Mage::throwException($this->__('GMO request failed'));
            }
        }
        $this->orderPayment->setIsTransactionClosed(true);

        return $this;
    }
    
    public function cancel(Varien_Object $payment){
        $this->_alterTran($payment, 'VOID');
        return $this;
    }

    public function refund(Varien_Object $payment, $amount){
        $this->_alterTran($payment, 'RETURN');
        return $this;
    }

    protected function getOrderPayment(Varien_Object $payment) {
        $this->orderPayment = $payment->getOrder()->getPayment();
    }

    protected function getPaymentId(Varien_Object $payment) {
        if( empty($this->orderPayment) ){
            $this->getOrderPayment($payment);
        }
        $payment_id = $this->orderPayment->getParentTransactionId();
        return $payment_id;
    }

    protected function getOrderId($payment) {
        $order = $payment->getOrder();
        return $order->getIncrementId();
    }

    public function getVerificationRegEx()
    {
        $verificationExpList = array(
            'VI' => '/^[0-9]{3}$/', // Visa
            'MC' => '/^[0-9]{3}$/',       // Master Card
            'AE' => '/^[0-9]{4}$/',        // American Express
            'DI' => '/^[0-9]{3}$/',          // Discovery
            'SS' => '/^[0-9]{3,4}$/',
            'SM' => '/^[0-9]{3,4}$/', // Switch or Maestro
            'SO' => '/^[0-9]{3,4}$/', // Solo
            'OT' => '/^[0-9]{3,4}$/',
            'JCB' => '/^[0-9]{3,4}$/' //JCB
        );
        return $verificationExpList;
    }

}
