<?php
class Magease_Directoryoptimized_Model_District extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('magease_directoryoptimized/district');
    }

    public function getName()
    {
        $name = $this->getData('name');
        if (is_null($name)) {
            $name = $this->getData('default_name');
        }
        return $name;
    }

    public function loadById($districtId, $cityId)
    {
    	$this->_getResource()->loadById($this, $districtId, $cityId);
        return $this;
    }

    public function loadByName($districtName, $cityId)
    {
        $this->_getResource()->loadByName($this, $districtName, $cityId);
        return $this;
    }

}
