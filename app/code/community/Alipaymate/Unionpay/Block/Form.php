<?php

class Alipaymate_Unionpay_Block_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('alipaymate/unionpay/form.phtml');
    }

    public function showLogo() {
        return Mage::getStoreConfig('payment/unionpay/show_logo');
    }
}