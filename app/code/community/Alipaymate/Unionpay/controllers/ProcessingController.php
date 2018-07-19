<?php
class Alipaymate_Unionpay_ProcessingController extends Mage_Core_Controller_Front_Action
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
            $this->_helper = Mage::helper('unionpay');
        }

        return $this->_helper;
    }
    /**
     * when customer selects Unionpay payment method
     */
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

            $this->getResponse()->setBody($this->getLayout()->createBlock('unionpay/redirect')->toHtml());

            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getCheckout()->addError($e->getMessage());
        } catch(Exception $e) {
            Mage::logException($e);
        }

        $this->_redirect('checkout/cart');
    }

    /**
     * Unionpay Return
     */
    public function returnAction()
    {
        $request = $this->getRequest()->getPost();

        $this->_getHelper()->setReturnLog()->log('unionpay-return', $request);

        try {
            $errorMessage = Mage::helper('unionpay')->__('Sorry, Unionpay payment failed, Please contact us!');

            if (empty($request) || !isset($request['signature'])) {
                Mage::throwException($errorMessage);
            }

            $unionpay = Mage::getModel('unionpay/payment');
            $config = $unionpay->prepareConfig();

            $unionpay_core = Mage::getModel('unionpay/core');
            $unionpay_core->setConfig($config);
            $result = $unionpay_core->verifyResponse($request);

            if (!$result) {
                Mage::throwException($errorMessage);
            }

            $orderId = $request['orderId'];
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

            if ($order->getStatus() == 'pending') {
                // make invoice
                if ($order->canInvoice()) {
                    $invoice = $order->prepareInvoice();
                    $invoice->register()->capture();
                    Mage::getModel('core/resource_transaction')
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder())
                        ->save();
                }
                
                $status = Mage::getStoreConfig('payment/unionpay/order_status_payment_accepted');
                
                if (! $status) {
                    $status = Mage_Sales_Model_Order::STATE_PROCESSING;
                }
                
                $order->addStatusToHistory($status, Mage::helper('unionpay')->__('Order status: payment successful'));
                $order->sendNewOrderEmail();
                $order->setEmailSent(true);
                $order->setIsCustomerNotified(true);
                $order->save();
            }

            header('Location: ' . Mage::getUrl('checkout/onepage/success', array('_secure' => true)));
            exit();
        } catch (Exception $e) {
            Mage::logException($e);
            header('Location: ' . Mage::getUrl('checkout/onepage/failure', array('_secure' => true)));
            exit();
        }
    }

    /**
     * Unionpay notify
     */
    public function notifyAction()
    {
        $request = null;

        if ($this->getRequest()->isPost()) {
            $request = $this->getRequest()->getPost();
        }

        $helper = $this->_getHelper()->setNotifyLog();
        $helper->log('unionpay-notify', $request);

        if (empty($request) || !isset($request['signature'])) {
            $this->error();
        }

        // check notify
        $unionpay = Mage::getModel('unionpay/payment');
        $config = $unionpay->prepareConfig();

        $unionpay_core = Mage::getModel('unionpay/core');
        $unionpay_core->setConfig($config);
        $result = $unionpay_core->verifyResponse($request);

        if (!$result) {
            $this->error();
        }

        // update order status
        $orderId = $request['orderId'];

        try {
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

            if ($order->getStatus() == 'pending') {
                // make invoice
                if ($order->canInvoice()) {
                    $invoice = $order->prepareInvoice();
                    $invoice->register()->capture();
                    Mage::getModel('core/resource_transaction')
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder())
                        ->save();
                }
                
                $status = Mage::getStoreConfig('payment/unionpay/order_status_payment_accepted');
                
                if (! $status) {
                    $status = Mage_Sales_Model_Order::STATE_PROCESSING;
                }
                
                $order->addStatusToHistory($status, Mage::helper('unionpay')->__('Order status: payment successful'));
                $order->sendNewOrderEmail();
                $order->setEmailSent(true);
                $order->setIsCustomerNotified(true);
                $order->save();
            }
            
            $this->success();
        } catch (Exception $e) {
            Mage::logException($e);
            $helper->log('unionpay-notify Exception', $e->getMessage());
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