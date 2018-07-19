<?php

require_once MAGENTO_ROOT . DS . 'lib/serbanghita/Mobile_Detect.php';

class Alipaymate_Unionpay_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    protected $_code  = 'unionpay';
    protected $_formBlockType = 'unionpay/form';

    protected $_isGateway               = false;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;
    protected $_canRefund               = false;

    protected $_order;

    public function isMobileBrowser()
    {
        $detect = new Mobile_Detect;

        if ($detect->isMobile() || $detect->isTablet()) {
            return true;
        }

        return false;
    }

    /**
     * Get order model
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
		if (!$this->_order) {
            $request = $_GET;

            if (isset($request['orderId']) && $request['orderId'] > '') {
                $orderId = $request['orderId'];
                $this->_order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
                return $this->_order;
            }

			$orderIncrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
			$this->_order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
		}

		return $this->_order;
    }

    /**
     * Capture payment
     *
     * @param   Varien_Object $orderPayment
     * @return  Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $payment->setStatus(self::STATUS_APPROVED)
            ->setLastTransId($this->getTransactionId());

        return $this;
    }

    public function prepareConfig()
    {
        $config = array(
            'version'           => '5.0.0',
            'encoding'          => 'utf-8',
            'merId'             => $this->getConfigData('merchant_id'),
            'txnType'           => '01',
            'txnSubType'        => '01',
            'bizType'           => '000201',
            'backUrl'           => $this->getNotifyURL(),
            'frontUrl'          => $this->getReturnURL(),
            'signMethod'        => '01',
            'channelType'       => '07',
            'accessType'        => '0',
            'sandbox'           => '0',
            'gateway_url'       => trim($this->getConfigData('gateway_url')),
            'license'           => trim($this->getConfigData('license')),
            'cert_password'     => trim($this->getConfigData('certificate_password')),
        );

        if ($this->isMobileBrowser()) {
            $config['channelType'] = '08';
        }

        // sandbox setting
        $sandbox = trim($this->getConfigData('sandbox'));

        if ($sandbox > 0) {
            $config['sandbox'] = '1';
            $config['gateway_url'] = trim($this->getConfigData('sandbox_gateway_url'));
        }

        return $config;
    }


    public function prepareBizData()
    {
        $allow_currencies = array('CNY');

        $order_currency = $this->_getCurrency();
        $order_total    = $this->_getTotalFee();

        $currency  = '';
        $total_fee = 0;

        if (in_array($order_currency, $allow_currencies)) {
            $currency  = $order_currency;
            $total_fee = $order_total;
        } else { // convert to CNY
            $base_currency    = $this->getOrder()->getBaseCurrencyCode();
            $base_grand_total = $this->getOrder()->getBaseGrandTotal();

            $currency  = 'CNY';
            $total_fee = Mage::getModel('directory/currency')->load($base_currency)->convert($base_grand_total, $currency);
        }

        $total_fee = (int)($total_fee * 100);

        date_default_timezone_set('Asia/Shanghai');
        $txnTime = date('YmdHis');

        $param = array(
            'currencyCode'       => '156',
            'orderId'            => $this->getOrder()->getRealOrderId(),
            'txnTime'            => $txnTime,
            'txnAmt'             => $total_fee,
        );

        return $param;
    }

    protected function _getTotalFee()
    {
        $total = $this->getOrder()->getGrandTotal();

        $total = sprintf("%.2f", $total);
        return $total;
    }

    protected function _getCurrency()
    {
        $currency = $this->getOrder()->getOrderCurrencyCode();
        return $currency;
    }

    protected function _getSubject()
    {
        return 'Order No: #' . $this->getOrder()->getRealOrderId();
    }

    protected function _getBody() {
        return 'Order No: #' . $this->getOrder()->getRealOrderId();
    }


    /**
     * Return Order place redirect url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('unionpay/processing/redirect', array('_secure' => true));
    }

	protected function getReturnURL()
	{
        return Mage::getUrl('unionpay/processing/return/', array('_secure' => true));
	}

	protected function getNotifyURL()
	{
		return Mage::getUrl('unionpay/processing/notify/', array('_secure' => true));
	}


    private function isModuleActive($module)
    {
        if (!Mage::helper('core')->isModuleEnabled($module)) {
            return false;
        }

        if (!Mage::getConfig()->getModuleConfig($module)->is('active', 'true')) {
            return false;
        }

        return true;
    }

    public function getCode()
    {
        return $this->_code;
    }

    public function canRepay($order)
    {
        try {
            if ($this->getConfigData('enable_repay') <= 0) {
                return false;
            }
            if ($this->getConfigData('active') <= 0) {
                return false;
            }

            if ($order->getStatus() != 'pending') {
                return false;
            }

            if ($order->getPayment()->getMethodInstance()->getCode() != $this->getCode()) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function getRepayUrl($order)
    {
        $orderId = $order->getRealOrderId();

        $url = Mage::getUrl('unionpay/processing/redirect', array('_secure' => true));
        $url .= '?orderId=' . $orderId;

        return $url;
    }
}