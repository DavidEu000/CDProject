<?php

class Alipaymate_Alipay_Block_RedirectIframe extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        $paymentUrl = Mage::getUrl('alipay/processing/payment', array('_secure' => true));
        $title = $this->__('Alipay');
                        
        $ajaxImgSrc = $this->getSkinUrl('images/alipaymate/ajax-loader.gif');

        $html = <<<EOT
        <!DOCTYPE html>
            <head>
                <title>{$title}</title>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <meta name="viewport" content="initial-scale=1.0, width=device-width" />

                <style>
                    body { overflow: hidden; width: 100%; margin:0; padding:0; }
                    #divLoading { widht:100%; margin:0 auto; text-align:center; }
                    #divLoading img { margin-top: 20%;}
                </style>

                <script type="text/javascript" src="//apps.bdimg.com/libs/jquery/1.7.2/jquery.min.js"></script>
                
                <script type="text/javascript">
                   function hideLoading() {
                        document.getElementById('divLoading').style.display = "none";
                        document.getElementById('divFrameHolder').style.display = "block";
                    }
                </script>
            </head>

            <body>
                <div id="divLoading">
                    <img src="{$ajaxImgSrc}" alt="" />
                </div>

                <div id="divFrameHolder" style="display:none;margin:0;padding:0;"> 
                <iframe src="{$paymentUrl}" id="aliframe" onload="hideLoading()" frameborder="0" marginheight="0" marginwidth="0" hspeace="0" vspace="0" style="width:100%;"></iframe>
                </div> 
                 
                <script type="text/javascript">
                //<![CDATA[
                    jQuery(document).ready(function($) {
                         $('#aliframe').css({width: '100%', height: $(window).height() });
                    });
                //]]>
                </script>
            </body>
        </html>
EOT;

        return $html;
    }
}
