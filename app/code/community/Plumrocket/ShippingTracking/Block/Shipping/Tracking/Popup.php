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


class Plumrocket_ShippingTracking_Block_Shipping_Tracking_Popup extends Mage_Shipping_Block_Tracking_Popup
{
    public function getTrackingInfo()
    {
        $_results = parent::getTrackingInfo();
        //$order = Mage::getModel('sales/order')->load(Mage::registry('current_shipping_info')->getOrderId());
        if (Mage::helper('shippingtracking')->moduleEnabled() && count($_results) > 0) {

            foreach($_results as $shipid => $_result) {
                foreach($_result as $key => $track) {

                    $_results[$shipid][$key] = array(
                        'order_id' => $track['order_id'],
                        'track_number' => $track['track_number'],
                        'title' => $track['title'],
                        'carrier_code' => $track['carrier_code']
                    );

                }
            }
        }

        return $_results;
    }

    public function getPopUpTrackingInfo()
    {
        $_results = parent::getTrackingInfo();
        //$order = Mage::getModel('sales/order')->load(Mage::registry('current_shipping_info')->getOrderId());
        if (Mage::helper('shippingtracking')->moduleEnabled() && count($_results) > 0) {
            foreach($_results as $shipid => $_result) {

                $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipid);
                $order = Mage::getModel('sales/order')->load($shipment->getOrderId());
                $trackingInfo = $this->_getTrackingInfoByOrder($order);

                foreach($trackingInfo as $key => $track) {

                    $_results[$shipid][$key] = array(
                        'order_id' => $track['order_id'],
                        'track_number' => $track['track_number'],
                        'title' => $track['title'],
                        'carrier_code' => $track['carrier_code']
                    );

                }
            }
        }

        return $_results;
    }

    protected function _getTrackingInfoByOrder($order)
    {
        $shipTrack = array();
            $shipments = $order->getShipmentsCollection();
            foreach ($shipments as $shipment){
                $increment_id = $shipment->getIncrementId();
                $tracks = $shipment->getTracksCollection();
                $trackingInfos=array();
                foreach ($tracks->getData() as $track){
                    $trackingInfos[] = $track;
                }

                $shipTrack= $trackingInfos;
            }

        
        return $shipTrack;
    }

}
