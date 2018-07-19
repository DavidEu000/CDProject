<?php
class Magease_Directoryoptimized_Model_Resource_City extends Mage_Core_Model_Resource_Db_Abstract
{

    protected $_cityNameTable;

    protected function _construct()
    {
        $this->_init('magease_directoryoptimized/country_city', 'city_id');
        $this->_cityNameTable = $this->getTable('magease_directoryoptimized/country_city_name');
    }
    
    protected function _loadByRegion($object, $regionId, $value, $field)
    {
        $adapter        = $this->_getReadAdapter();
        $locale         = Mage::app()->getLocale()->getLocaleCode();
        $joinCondition  = $adapter->quoteInto('rname.city_id = city.city_id AND rname.locale = ?', $locale);
        $select         = $adapter->select()
            ->from(array('city' => $this->getMainTable()))
            ->joinLeft(
                array('rname' => $this->_cityNameTable),
                $joinCondition,
                array('name'))
            ->where('city.region_id = ?', $regionId);
            
    		if(!empty($value)){
           		$select->where("city.{$field} = ?", $value);
           	}

        $data = $adapter->fetchAll($select);
        if ($data) {
            $object->setData($data);
        }

        $this->_afterLoad($object);

        return $this;
    }
    
    public function loadById(Magease_Directoryoptimized_Model_City $city, $cityId, $regionId)
    {
        return $this->_loadByRegion($city, $regionId, (int)$cityId, 'city_id');
    }

    public function loadByName(Magease_Directoryoptimized_Model_City $city, $cityName, $regionId)
    {
        return $this->_loadByRegion($city, $regionId, (string)$cityName, 'default_name');
    }

}
