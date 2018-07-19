<?php

class Alipaymate_Alipay_ProcessingController extends Mage_Core_Controller_Front_Action
{
    private $_helper;
    
    /**
     * Get singleton of Checkout Session Model
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    protected function _getHelper()
    {
        if (! $this->_helper) {
            $this->_helper = Mage::helper('alipay');
        }

        return $this->_helper;
    }

    /**
     * when customer selects Alipay payment method
     */
    public function redirectAction()
    {
        $this->_prepareInWechat();
        $this->paymentAction();
    }


    public function _prepareInWechat()
    {
        if (stripos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            if (empty($_GET['orderId'])) { // redirect to repay url
                $orderId = $this->_getCheckout()->getLastRealOrderId();
                $redirectUrl = Mage::getUrl('alipay/processing/redirect', array('_secure' => true));
                $redirectUrl .= '?orderId=' . $orderId;

                header('Location: ' . $redirectUrl);
                exit();
            }

            // $this->getResponse()->setBody();
            $html = $this->getLayout()->createBlock('alipay/redirectWechat')->toHtml();
            exit($html);
        }

        return;
    }

    /**
     * when customer selects Alipay payment method
     */
    public function paymentAction()
    {
        try {
            $request = $this->getRequest()->getQuery();

            if (isset($request['orderId']) && $request['orderId'] > '') {
                $orderId = $request['orderId'];
            } else {
                $session = $this->_getCheckout();
                $orderId = $session->getLastRealOrderId();
            }

            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

            if (!$order->getId()) {
                Mage::throwException('No order for processing found');
            }

            $this->getResponse()->setBody($this->getLayout()->createBlock('alipay/redirect')->toHtml());

            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getCheckout()->addError($e->getMessage());
        } catch(Exception $e) {
            Mage::logException($e);
        }

        $this->_redirect('checkout/cart');
    }


    /**
     * when customer selects Alipay payment method
     */
    /*
    public function redirectAction()
    {
        try {
            $request = $this->getRequest()->getQuery();

            if (isset($request['orderId']) && $request['orderId'] > '') {
                $orderId = $request['orderId'];
            } else {
                $session = $this->_getCheckout();
                $orderId = $session->getLastRealOrderId();
            }

            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

            if (!$order->getId()) {
                Mage::throwException('No order for processing found');
            }

            $this->getResponse()->setBody($this->getLayout()->createBlock('alipay/redirect')->toHtml());

            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getCheckout()->addError($e->getMessage());
        } catch(Exception $e) {
            Mage::logException($e);
        }

        $this->_redirect('checkout/cart');
    }
    */



    /**
     * Alipay Return
     */
    public function returnAction()
    {
        $request = $this->getRequest()->getQuery();

        $this->_getHelper()->setReturnLog()->log('alipay-return', $request);

        try {
            $errorMessage = Mage::helper('alipay')->__('Sorry, Alipay payment failed, Please contact us!');

            if (empty($request['trade_status'])) {
                Mage::throwException($errorMessage);
            }

            $alipay = Mage::getModel('alipay/payment');
            $config = $alipay->prepareConfig();

            $alipay_core = Mage::getModel('alipay/core');
            $alipay_core->setConfig($config);
            $result = $alipay_core->verifyResponse($request);

            if (!$result) {
                Mage::throwException($errorMessage);
            }

            $success = Mage::helper('alipay')->__('Your Payment was Successful!');
            $this->_getCheckout()->addSuccess($success);
            header('Location: ' . Mage::getUrl('checkout/onepage/success', array('_secure' => true)));
            exit();
        } catch (Exception $e) {
            Mage::logException($e);
            header('Location: ' . Mage::getUrl('checkout/onepage/failure', array('_secure' => true)));
            exit();
        }
    }

    /**
     * Alipay notify
     */
    public function notifyAction()
    {
        $request = null;

        if ($this->getRequest()->isPost()) {
            $request = $this->getRequest()->getPost();
        }

        $this->_getHelper()->setNotifyLog()->log('alipay-notify', $request);

        if (empty($request) || !isset($request['trade_status'])) {
            $this->error();
        }

        // check notify
        $alipay = Mage::getModel('alipay/payment');
        $config = $alipay->prepareConfig();

        $alipay_core = Mage::getModel('alipay/core');
        $alipay_core->setConfig($config);
        $result = $alipay_core->verifyResponse($request);

        if (!$result) {
            $this->error();
        }

        // update order status
        $trade_status = $request['trade_status'];
        $orderId      = $request['out_trade_no'];

        $state   = '';
        $message = '';

        switch ($trade_status) {
            case 'WAIT_BUYER_PAY':
                $message = 'Order status: waitting for pay';
                break;
            case 'WAIT_SELLER_SEND_GOODS':
                $state   = Mage_Sales_Model_Order::STATE_PROCESSING;
                $message = 'Order status: payment successful';
                break;
            case 'WAIT_BUYER_CONFIRM_GOODS':
                $message = 'Order status: shipped';
                break;
            case 'TRADE_SUCCESS':
                $state   = Mage_Sales_Model_Order::STATE_PROCESSING;
                $message = 'Order status: success';
                break;
            case 'TRADE_FINISHED':
                $state   = Mage_Sales_Model_Order::STATE_PROCESSING;
                $message = 'Order status: finished';
                break;
            case 'TRADE_CLOSED':
                $state   = Mage_Sales_Model_Order::STATE_CLOSED;
                $message = 'Order status: closed';
                break;
            default:
                break;
        }

        try {
            $order = Mage::getModel('sales/order');
            $order->loadByIncrementId($orderId);

            if (empty($state)) {
                $state = $order->getStatus();
            }

            if ($state == Mage_Sales_Model_Order::STATE_PROCESSING) {
                if ($order->getStatus() == 'pending') {
                    // make invoice
                    if ($order->canInvoice() && !$order->hasInvoices()) {
                        $invoice = $order->prepareInvoice();
                        $invoice->register()->capture();
                        Mage::getModel('core/resource_transaction')
                            ->addObject($invoice)
                            ->addObject($invoice->getOrder())
                            ->save();
                    }

                    // add transactionid
                    if (!empty($request['trade_no'])) {
                        $order->getPayment()
                              ->setTransactionId($request['trade_no'])
                              ->setCurrencyCode() // $request['currency']
                              ->setParentTransactionId()
                              ->setShouldCloseParentTransaction(true)
                              ->setIsTransactionClosed(0)
                              ->registerCaptureNotification(); /* $request['total_fee'] */
                    }

                    $status = Mage::getStoreConfig('payment/alipay/order_status_payment_accepted');

                    if (! $status) {
                        $status = Mage_Sales_Model_Order::STATE_PROCESSING;
                    }

                    $order->addStatusToHistory($status, Mage::helper('alipay')->__('Order status: payment successful'));
                    $order->sendNewOrderEmail();
                    $order->setEmailSent(true);
                    $order->setIsCustomerNotified(true);
                }
            }

            $order->addStatusToHistory($state, Mage::helper('alipay')->__($message));
            $order->save();
            $this->success();
        } catch (Exception $e) {
            Mage::logException($e);
            $this->error();
        }
    }


    private function success()
    {
        echo 'success';
        exit();
    }


    private function error()
    {
        echo 'fail';
        exit();
    }
}