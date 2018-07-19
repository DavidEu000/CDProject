<?php
class Magease_Directoryoptimized_Model_Resource_City_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected $_cityNameTable;

    protected $_regionTable;

    protected function _construct()
    {
        $this->_init('magease_directoryoptimized/city');

        $this->_regionTable    = $this->getTable('directory/country_region');
        $this->_cityNameTable = $this->getTable('magease_directoryoptimized/country_city_name');

        $this->addOrder('city_id', Varien_Data_Collection::SORT_ORDER_ASC);
        $this->addOrder('default_name', Varien_Data_Collection::SORT_ORDER_ASC);
    }
    
    protected function _initSelect()
    {
    	parent::_initSelect();
    	$locale = Mage::app()->getLocale()->getLocaleCode();
    
    	$this->addBindParam(':city_locale', $locale);
    	$this->getSelect()->joinLeft(
    			array('rname' => $this->_cityNameTable),
    			'main_table.city_id = rname.city_id AND rname.locale = :city_locale',
    			array('name'));
    
    	return $this;
    }
    
    public function addRegionFilter($regionId)
    {
        if (!empty($regionId)) {
            if (is_array($regionId)) {
                $this->addFieldToFilter('main_table.region_id', array('in' => $regionId));
            } else {
                $this->addFieldToFilter('main_table.region_id', $regionId);
            }
        }
        return $this;
    }

    public function toOptionArray()
    {
    	$options = $this->_toOptionArray('city_id', 'default_name', array('title' => 'default_name'));
    	if (count($options) > 0) {
    		array_unshift($options, array(
    		'title '=> null,
    		'value' => '0',
    		'label' => Mage::helper('directoryoptimized')->__('-- Please select --')
    		));
    	}
    	return $options;
    }
}
