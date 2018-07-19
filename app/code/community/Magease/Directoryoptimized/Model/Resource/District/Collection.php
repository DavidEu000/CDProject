<?php
class Magease_Directoryoptimized_Model_Resource_District_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    
    protected $_districtNameTable;

    protected $_cityTable;

    protected function _construct()
    {
        $this->_init('magease_directoryoptimized/district');

        $this->_cityTable    = $this->getTable('magease_directoryoptimized/country_city');
        $this->_districtNameTable = $this->getTable('magease_directoryoptimized/country_district_name');

        $this->addOrder('district_id', Varien_Data_Collection::SORT_ORDER_ASC);
        $this->addOrder('default_name', Varien_Data_Collection::SORT_ORDER_ASC);
    }
    
    public function addCityFilter($cityId)
    {
        if (!empty($cityId)) {
            if (is_array($cityId)) {
                $this->addFieldToFilter('main_table.city_id', array('in' => $cityId));
            } else {
                $this->addFieldToFilter('main_table.city_id', $cityId);
            }
        }
        return $this;
    }

    
}
