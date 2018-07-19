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
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_ShippingTracking_Model_Shunfeng
	extends Mage_Shipping_Model_Carrier_Abstract
    
{

      /**
     * Carrier's code, as defined in parent class
     *
     * @var string
     */
    protected $_code = 'shunfeng';
	/**
     * Check if carrier has shipping tracking option available
     *
     * @return boolean
     */
	public function isTrackingAvailable(){
		return true;
	}

     /**
     * Collect and get rates
     *
     * @abstract
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result|bool|null
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request){
        parent::collectRates();
    }

}