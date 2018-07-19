<?php
class Magease_Directoryoptimized_Helper_Data extends Mage_Core_Helper_Abstract
{
	
	protected $_regionCollection;
	
	protected $_cityCollection;
	
    public function getRegionCollection()
    {
        if (!$this->_regionCollection) {
            $this->_regionCollection = Mage::getModel('directory/region')->getResourceCollection();
        }
        return $this->_regionCollection;
    }
	
	public function getCityCollection()
    {
        if (!$this->_cityCollection) {
            $this->_cityCollection = Mage::getModel('magease_directoryoptimized/city')->getResourceCollection();
        }
        return $this->_cityCollection;
    }
    
    public function getCityJson()
    {

        Varien_Profiler::start('TEST: '.__METHOD__);
        if (!$this->_cityJson) {
            $cacheKey = 'DIRECTORY_CITY_JSON_STORE'.Mage::app()->getStore()->getId();
            if (Mage::app()->useCache('config')) {
                $json = Mage::app()->loadCache($cacheKey);
            }
            if (empty($json)) {
                $regionIds = array();
                foreach ($this->getRegionCollection() as $region) {
                    $regionIds[] = $region->getRegionId();
                }
                $collection = Mage::getModel('magease_directoryoptimized/city')->getResourceCollection()
                    ->addRegionFilter($regionIds)
                    ->load();
                $cities = array(
                    'config' => array(
                        'show_all_cities' => true,
                        'cities_required' => array()
                    )
                );
                foreach ($collection as $city) {
                    if (!$city->getCityId()) {
                        continue;
                    }
                    $cities[$city->getRegionId()][$city->getCityId()] = array(
                        'code' => $city->getCityId(),
                        'name' => $this->__($city->getName())
                    );
                }
                $json = Mage::helper('core')->jsonEncode($cities);

                if (Mage::app()->useCache('config')) {
                    Mage::app()->saveCache($json, $cacheKey, array('config'));
                }
            }
            $this->_cityJson = $json;
        }

        Varien_Profiler::stop('TEST: '.__METHOD__);
        return $this->_cityJson;
    }
    	
    public function getDistrictJson()
    {

        Varien_Profiler::start('TEST: '.__METHOD__);
        if (!$this->_districtJson) {
            $cacheKey = 'DIRECTORY_DISTRICT_JSON_STORE'.Mage::app()->getStore()->getId();
            if (Mage::app()->useCache('config')) {
                $json = Mage::app()->loadCache($cacheKey);
            }
            if (empty($json)) {
                $cityIds = array();
                foreach ($this->getCityCollection() as $city) {
                    $cityIds[] = $city->getCityId();
                }
                $collection = Mage::getModel('magease_directoryoptimized/district')->getResourceCollection()
                    ->addCityFilter($cityIds)
                    ->load();
                $districts = array(
                    'config' => array(
                        'show_all_districts' => true,
                        'districts_required' => array()
                    )
                );
                foreach ($collection as $district) {
                    if (!$district->getDistrictId()) {
                        continue;
                    }
                    $districts[$district->getCityId()][$district->getDistrictId()] = array(
                        'code' => $district->getDistrictId(),
                        'name' => $this->__($district->getName())
                    );
                }
                $json = Mage::helper('core')->jsonEncode($districts);

                if (Mage::app()->useCache('config')) {
                    Mage::app()->saveCache($json, $cacheKey, array('config'));
                }
            }
            $this->_districtJson = $json;
        }

        Varien_Profiler::stop('TEST: '.__METHOD__);
        return $this->_districtJson;
    }

    

}
