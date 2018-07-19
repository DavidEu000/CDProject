<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please 
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Shipping_Tracking
 * @copyright   Copyright (c) 2014 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_ShippingTracking_Helper_Data extends Plumrocket_ShippingTracking_Helper_Main
{
	protected $_trackingInfo = array();

	public function moduleEnabled($store = null)
    {
       return (bool)Mage::getStoreConfig('shippingtracking/general/enabled', $store);
    }

	public function getShippingTrackingUrl()
	{
		return Mage::getUrl('shippingtracking');
	}


	protected function _getDecryptedConfig($key)
	{
		if (empty($key)) {
            return false;
        }
        return Mage::helper('core')->decrypt(
        	Mage::getStoreConfig('shippingtracking/'.$key));
	}


	public function getKuaiDiTrackingInfo($number, $express_code)
	{
		if (!empty($number) && !empty($express_code)) {
			$post_data = array();
			// $post_data["customer"] = Mage::getStoreConfig('shippingtracking/kuaidi100_api/customer');
			// $key = Mage::getStoreConfig('shippingtracking/kuaidi100_api/key');
			// $url = Mage::getStoreConfig('shippingtracking/kuaidi100_api/trckingurl');

			if (empty($post_data["customer"]) && empty($key) && empty($url)) {
				return array('error' => 'Error');
			}

			$post_data["param"] = '{"com":"'.$express_code.'","num":"'.$number.'"}';
			$post_data["sign"] = md5($post_data["param"].$key.$post_data["customer"]);
			$post_data["sign"] = strtoupper($post_data["sign"]);

			$o=""; 

			foreach ($post_data as $k=>$v){
			    $o.= "$k=".urlencode($v)."&";
			}

			$post_data=substr($o,0,-1);


			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_TIMEOUT,10);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
			$output = curl_exec($ch);
			$data = str_replace("\&quot;",'"',$output );
			$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			return $this->_trackingInfo[$number] = json_decode($data,true);
			
		}

		return $this->_trackingInfo[$number];
	}

}
