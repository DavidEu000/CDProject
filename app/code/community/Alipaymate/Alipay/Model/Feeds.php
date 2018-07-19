<?php

class Alipaymate_Alipay_Model_Feeds extends Mage_AdminNotification_Model_Feed
{
	public function getFeedUrl()
	{
	    $feedUrl = 'http://alipaymate.com/update/alipay/feed.rss';

	    return $feedUrl;
	}

	public function check()
	{
	    return Mage::getModel('alipay/feeds')->checkUpdate();
	}

    public function getFrequency()
    {
        return 3600 * 24 * 3;
    }

    public function getLastUpdate()
    {
        return Mage::app()->loadCache('alipaymate_alipay_notifications_lastcheck');
    }

    public function setLastUpdate()
    {
        Mage::app()->saveCache(time(), 'alipaymate_alipay_notifications_lastcheck');

        return $this;
    }
}