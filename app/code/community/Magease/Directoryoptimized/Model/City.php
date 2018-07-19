<?php
class Magease_Directoryoptimized_Model_City extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('magease_directoryoptimized/city');
    }

    public function getName()
    {
        $name = $this->getData('name');
        if (is_null($name)) {
            $name = $this->getData('default_name');
        }
        return $name;
    }

    public function loadById($cityId, $regionId)
    {
    	$this->_getResource()->loadById($this, $cityId, $regionId);
        return $this;
    }

    public function loadByName($cityName, $regionId)
    {
        $this->_getResource()->loadByName($this, $cityName, $regionId);
        return $this;
    }
    public function getDistrictCollection()
    {
    	$collection = Mage::getResourceModel('magease_directoryoptimized/district_collection');
    	$collection->addCityFilter($this->getId());
    	return $collection;
    }

}
