<?php

require_once MAGENTO_ROOT . DS . 'lib/serbanghita/Mobile_Detect.php';

class Alipaymate_Alipay_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    protected $_code  = 'alipay';
    protected $_formBlockType = 'alipay/form';

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
            // $request = $this->getRequest()->getQuery();
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
            '_input_charset'    => 'utf-8',
            'service'           => 'create_direct_pay_by_user', // $this->getConfigData('service_type'),
            'partner'           => $this->getConfigData('partner_id'),
            'seller_id'         => $this->getConfigData('partner_id'),
            'sign_type'         => 'MD5',
            'sign'              => $this->getConfigData('security_key'),
            'notify_url'        => $this->getNotifyURL(),
            'return_url'        => $this->getReturnURL(),
            'license'           => trim($this->getConfigData('license')),
            'error_notify_url'  => '',
            'anti_phishing_key' => '',
            'exter_invoke_ip'   => ''
        );

        if ($this->isMobileBrowser()) {
            $config['service'] = 'alipay.wap.create.direct.pay.by.user';
        }

        return $config;
    }


    public function prepareBizData()
    {
        $allow_currencies = array('CNY');

        $order_currency = $this->_getCurrency();
        $order_total    = $this->_getTotalFee();

        $currency  = '';
        $total_fee = 0.00;

        if (in_array($order_currency, $allow_currencies)) {
            $currency  = $order_currency;
            $total_fee = $order_total;
        } else { // convert to CNY
            $base_currency    = $this->getOrder()->getBaseCurrencyCode();
            $base_grand_total = $this->getOrder()->getBaseGrandTotal();

            $currency  = 'CNY';
            $total_fee = Mage::getModel('directory/currency')->load($base_currency)->convert($base_grand_total, $currency);
            $total_fee = sprintf("%.2f", $total_fee);
        }

        // shipping info
        $shipping_address = $this->getOrder()->getShippingAddress();

        if (!$shipping_address) {
            $shipping_address = $this->getOrder()->getBillingAddress();
        }

        $shipping_country_code = $shipping_address->getCountry();
        $shipping_country_name = Mage::getModel('directory/country')->loadByCode($shipping_country_code)->getName();

        $receive_name     = $shipping_address->getFirstname()    . ' ' . $shipping_address->getLastname();
        $receive_address  = $shipping_country_name               . ' ' .
                            $shipping_address->getData('region') . ' ' .
                            $shipping_address->getData('city')   . ' ' .
                            $shipping_address->getStreet(1)      . ' ' .
                            $shipping_address->getStreet(2);

        $param = array(
            'out_trade_no'       => $this->getOrder()->getRealOrderId(),
            'subject'            => $this->_getSubject(),
            'payment_type'       => '1',
            'extend_param'       => '',
         // 'price'              => $total_fee,
         // 'quantity'           => '1',
            'total_fee'          => $total_fee,
            'body'               => $this->_getBody(),
            'show_url'           => '',
            'receive_name'       => $receive_name,
            'receive_address'    => $receive_address,
            'receive_zip'        => $shipping_address->getPostcode(),
            'receive_phone'      => $shipping_address->getTelephone(),
            'receive_mobile'     => $shipping_address->getFax(),
            'extra_common_param' => '',
            'logistics_fee'      => '0.00',
            'logistics_payment'  => 'SELLER_PAY',
            'logistics_type'     => 'EXPRESS',
        );

        if ($this->isMobileBrowser()) {
            $param['logistics_fee']     = '';
            $param['logistics_payment'] = '';
            $param['logistics_type']    = '';
            $param['receive_name']      = '';
            $param['receive_address']   = '';
            $param['receive_zip']       = '';
            $param['receive_phone']     = '';
            $param['receive_mobile']    = '';
        }

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
        return Mage::getUrl('alipay/processing/redirect', array('_secure' => true));        
    }

	protected function getReturnURL()
	{
        return Mage::getUrl('alipay/processing/return/', array('_secure' => true));
	}

	protected function getNotifyURL()
	{
		return Mage::getUrl('alipay/processing/notify/', array('_secure' => true));
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

        $url = Mage::getUrl('alipay/processing/redirect', array('_secure' => true));
        $url .= '?orderId=' . $orderId;

        return $url;
    }
}