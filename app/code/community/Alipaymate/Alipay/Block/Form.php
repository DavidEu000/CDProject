<?php

class Alipaymate_Alipay_Block_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('alipaymate/alipay/form.phtml');
    }

    public function showLogo() {
        return Mage::getStoreConfig('payment/alipay/show_logo');
    }
}