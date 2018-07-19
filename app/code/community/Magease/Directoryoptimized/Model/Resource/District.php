<?php
class Magease_Directoryoptimized_Model_Resource_District extends Mage_Core_Model_Resource_Db_Abstract
{

    protected $_districtNameTable;

    protected function _construct()
    {
        $this->_init('magease_directoryoptimized/country_district', 'district_id');
        $this->_districtNameTable = $this->getTable('magease_directoryoptimized/country_district_name');
    }
    
    protected function _loadByCity($object, $cityId, $value, $field)
    {
        $adapter        = $this->_getReadAdapter();
        $locale         = Mage::app()->getLocale()->getLocaleCode();
        $joinCondition  = $adapter->quoteInto('rname.district_id = district.district_id AND rname.locale = ?', $locale);
        $select         = $adapter->select()
            ->from(array('district' => $this->getMainTable()))
            ->joinLeft(
                array('rname' => $this->_districtNameTable),
                $joinCondition,
                array('name'))
            ->where('district.city_id = ?', $cityId);
         
           if(!empty($value)){
           	$select->where("district.{$field} = ?", $value);
           }

        $data = $adapter->fetchAll($select);
        if ($data) {
            $object->setData($data);
        }

        $this->_afterLoad($object);

        return $this;
    }
    
    public function loadById(Magease_Directoryoptimized_Model_District $district, $districtId, $cityId)
    {
        return $this->_loadByCity($district, $cityId, (int)$districtId, 'district_id');
    }

    public function loadByName(Magease_Directoryoptimized_Model_District $district, $districtName, $cityId)
    {
        return $this->_loadByCity($district, $cityId, (string)$districtName, 'default_name');
    }

}
