<?php

class Alipaymate_Unionpay_Model_Feeds extends Mage_AdminNotification_Model_Feed
{
	public function getFeedUrl()
	{
	    $feedUrl = 'http://alipaymate.com/update/unionpay/feed.rss';

	    return $feedUrl;
	}

	public function check()
	{
	    return Mage::getModel('unionpay/feeds')->checkUpdate();
	}

    public function getFrequency()
    {
        return 3600 * 24 * 3;
    }

    public function getLastUpdate()
    {
        return Mage::app()->loadCache('alipaymate_unionpay_notifications_lastcheck');
    }

    public function setLastUpdate()
    {
        Mage::app()->saveCache(time(), 'alipaymate_unionpay_notifications_lastcheck');

        return $this;
    }
}