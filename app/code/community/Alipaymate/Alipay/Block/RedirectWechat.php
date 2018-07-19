<?php

class Alipaymate_Alipay_Block_RedirectWechat extends Mage_Core_Block_Template
{
    /**
     * Constructor. Set template.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('alipaymate/alipay/redirect_wechat.phtml');
    }

    public function getOrderUrl()
    {
        $orderId = isset($_GET['orderId']) ? $_GET['orderId'] : '';

        if ($orderId) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            $url = Mage::getUrl('sales/order/view/order_id/' . $order->getId(), array('_secure' => true));
        } else {
            $url = $this->getOrderListUrl();
        }

        return $url;
    }

    public function getOrderListUrl()
    {
        $url = Mage::getUrl('sales/order/history/', array('_secure' => true));
        return $url;
    }
}
