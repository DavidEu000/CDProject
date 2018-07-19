<?php

class Alipaymate_Unionpay_Block_Redirect extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        $payment = Mage::getModel('unionpay/payment');
        $config  = $payment->prepareConfig();
        $params  = $payment->prepareBizData();

        $unionpay = Mage::getModel('unionpay/core');
        $unionpay->setConfig($config);
        $unionpay->setBizParams($params);

        $action        = $unionpay->getUnionpayUrl();        
          
        $requestHtml   = $unionpay->createRequestHtml();
        $redirectImg   = $this->getSkinUrl('images/alipaymate/redirect.gif');
        $redirectText  = $this->__('You will be redirected to the Unionpay website in a few seconds ...');
        $redirectTitle = $this->__('Redirect to Unionpay');

        $html = <<<EOT
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="initial-scale=1.0, width=device-width" />
        <title>{$redirectTitle}</title>
        <style>
            .container {
                font-family: Tahoma,Verdana,Arial;
                font-size: 13px;
                margin:0 auto;
                width: 100%;
            }

            .container p {
                border: 1px solid #efefef;
                width: 50%;
                vertical-align: middle;
                padding: 15px;
                text-align: center;
                margin:0 auto;
                margin-top: 45px;
            }

            .container img {
                vertical-align: middle;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <p><img alt="" src="{$redirectImg}" />&nbsp;&nbsp;{$redirectText}</p>

            <form name="unionpaysubmit" id="unionpaysubmit" method="post" action="{$action}">
                {$requestHtml}
            </form>
        </div>

        <script type="text/javascript">
            document.getElementById("unionpaysubmit").submit();
        </script>
    </body>
</html>
EOT;
        return $html;
    }
}
