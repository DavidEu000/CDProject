<?php

class Alipaymate_Alipay_Model_Source_ServiceType
{
    public function toOptionArray()
    {
        return array(
        	array('value' => 'create_direct_pay_by_user',     'label' => Mage::helper('alipay')->__('Prompt Arrival')),
            array('value' => 'create_partner_trade_by_buyer', 'label' => Mage::helper('alipay')->__('Secured Transactions')),
         // array('value' => 'trade_create_by_buyer',         'label' => Mage::helper('alipay')->__('Dual Functions')),
        );
    }
}
