<?php

class Alipaymate_Alipay_Block_Redirect extends Mage_Core_Block_Template
{
    /**
     * Constructor. Set template.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('alipaymate/alipay/redirect.phtml');
    }

    public function getAlipay()
    {
        $payment = Mage::getModel('alipay/payment');
        $config  = $payment->prepareConfig();
        $params  = $payment->prepareBizData();

        $alipay = Mage::getModel('alipay/core');
        $alipay->setConfig($config);
        $alipay->setBizParams($params);

        return $alipay;
    }
}
