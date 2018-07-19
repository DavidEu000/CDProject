<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Customer
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Address abstract model
 *
 * @category   Mage
 * @package    Mage_Customer
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Customer_Model_Address_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * Possible customer address types
     */
    const TYPE_BILLING  = 'billing';
    const TYPE_SHIPPING = 'shipping';

    /**
     * Prefix of model events
     *
     * @var string
     */
    protected $_eventPrefix = 'customer_address';

    /**
     * Name of event object
     *
     * @var string
     */
    protected $_eventObject = 'customer_address';

    /**
     * Directory country models
     *
     * @var array
     */
    static protected $_countryModels = array();

    /**
     * Directory region models
     *
     * @var array
     */
    static protected $_regionModels = array();
    static protected $_cityModels = array();
    static protected $_districtModels = array();
    

    /**
     * Get full customer name
     *
     * @return string
     */
    public function getName()
    {
        $name = '';
        $config = Mage::getSingleton('eav/config');
        if ($config->getAttribute('customer_address', 'prefix')->getIsVisible() && $this->getPrefix()) {
            $name .= $this->getPrefix() . ' ';
        }
        $name .= $this->getFirstname();
        if ($config->getAttribute('customer_address', 'middlename')->getIsVisible() && $this->getMiddlename()) {
            $name .= ' ' . $this->getMiddlename();
        }
        $name .=  ' ' . $this->getLastname();
        if ($config->getAttribute('customer_address', 'suffix')->getIsVisible() && $this->getSuffix()) {
            $name .= ' ' . $this->getSuffix();
        }
        return $name;
    }

    /**
     * get address street
     *
     * @param   int $line address line index
     * @return  string
     */
    public function getStreet($line=0)
    {
        $street = parent::getData('street');
        if (-1 === $line) {
            return $street;
        } else {
            $arr = is_array($street) ? $street : explode("\n", $street);
            if (0 === $line || $line === null) {
                return $arr;
            } elseif (isset($arr[$line-1])) {
                return $arr[$line-1];
            } else {
                return '';
            }
        }
    }

    public function getStreet1()
    {
        return $this->getStreet(1);
    }

    public function getStreet2()
    {
        return $this->getStreet(2);
    }

    public function getStreet3()
    {
        return $this->getStreet(3);
    }

    public function getStreet4()
    {
        return $this->getStreet(4);
    }

    public function getStreetFull()
    {
        return $this->getData('street');
    }

    public function setStreetFull($street)
    {
        return $this->setStreet($street);
    }

    /**
     * set address street informa
     *
     * @param unknown_type $street
     * @return unknown
     */
    public function setStreet($street)
    {
        if (is_array($street)) {
            $street = trim(implode("\n", $street));
        }
        $this->setData('street', $street);
        return $this;
    }

    /**
     * Create fields street1, street2, etc.
     *
     * To be used in controllers for views data
     *
     */
    public function explodeStreetAddress()
    {
        $streetLines = $this->getStreet();
        foreach ($streetLines as $i=>$line) {
            $this->setData('street'.($i+1), $line);
        }
        return $this;
    }

    /**
     * To be used when processing _POST
     */
    public function implodeStreetAddress()
    {
        $this->setStreet($this->getData('street'));
        return $this;
    }

    /**
     * Retrieve region name
     *
     * @return string
     */
    public function getRegion()
    {
        $regionId = $this->getData('region_id');
        $region   = $this->getData('region');

        if ($regionId) {
               if ($this->getRegionModel($regionId)->getCountryId() == $this->getCountryId()) {
                   $region = $this->getRegionModel($regionId)->getName();
                $this->setData('region', $region);
            }
        }

        if (!empty($region) && is_string($region)) {
            $this->setData('region', $region);
        }
        elseif (!$regionId && is_numeric($region)) {
            if ($this->getRegionModel($region)->getCountryId() == $this->getCountryId()) {
                $this->setData('region', $this->getRegionModel($region)->getName());
                $this->setData('region_id', $region);
            }
        }
        elseif ($regionId && !$region) {
               if ($this->getRegionModel($regionId)->getCountryId() == $this->getCountryId()) {
                $this->setData('region', $this->getRegionModel($regionId)->getName());
            }
        }

        return $this->getData('region');
    }
    
    
public function getCity()
    {
        $cityId = $this->getData('city_id');
    	$city   = $this->getData('city');

        if ($cityId) {
               if ($this->getCityModel($cityId)->getRegionId() == $this->getRegionId()) {
                   $city = $this->getCityModel($cityId)->getName();
                $this->setData('city', $city);
            }
        }

        if (!empty($city) && is_string($city)) {
            $this->setData('city', $city);
        }
        elseif (!$cityId && is_numeric($city)) {
            if ($this->getCityModel($city)->getRegionId() == $this->getRegionId()) {
                $this->setData('city', $this->getCityModel($city)->getName());
                $this->setData('city_id', $city);
            }
        }
        elseif ($cityId && !$city) {
               if ($this->getCityModel($cityId)->getRegionId() == $this->getRegionId()) {
                $this->setData('city', $this->getCityModel($cityId)->getName());
            }
        }

        return $this->getData('city');
    }
    public function getDistrict()
    {
    	$districtId = $this->getData('district_id');
    	$district   = $this->getData('district');
    
    	if ($districtId) {
    		if ($this->getDistrictModel($districtId)->getCityId() == $this->getCityId()) {
    			$district = $this->getDistrictModel($districtId)->getName();
    			$this->setData('district', $district);
    		}
    	}
    
    	if (!empty($district) && is_string($district)) {
    		$this->setData('district', $district);
    	}
    	elseif (!$districtId && is_numeric($district)) {
    		if ($this->getDistrictModel($district)->getCityId() == $this->getCityId()) {
    			$this->setData('district', $this->getDistrictModel($district)->getName());
    			$this->setData('district_id', $district);
    		}
    	}
    	elseif ($districtId && !$district) {
    		if ($this->getDistrictModel($districtId)->getCityId() == $this->getCityId()) {
    			$this->setData('district', $this->getDistrictModel($districtId)->getName());
    		}
    	}
    
    	return $this->getData('district');
    }

    /**
     * Return 2 letter state code if available, otherwise full region name
     *
     */
    public function getRegionCode()
    {
        $regionId = $this->getData('region_id');
        $region   = $this->getData('region');

        if (!$regionId && is_numeric($region)) {
            if ($this->getRegionModel($region)->getCountryId() == $this->getCountryId()) {
                $this->setData('region_code', $this->getRegionModel($region)->getCode());
            }
        }
        elseif ($regionId) {
            if ($this->getRegionModel($regionId)->getCountryId() == $this->getCountryId()) {
                $this->setData('region_code', $this->getRegionModel($regionId)->getCode());
            }
        }
        elseif (is_string($region)) {
            $this->setData('region_code', $region);
        }
        return $this->getData('region_code');
    }
    
    public function getCityId()
    {
    	$cityId = $this->getData('city_id');
    	$city   = $this->getData('city');
    
    	if (!$cityId && is_numeric($city)) {
    		if ($this->getCityModel($city)->getRegionId() == $this->getRegionId()) {
    			$this->setData('city_id', $this->getCityModel($city)->getCityId());
    		}
    	}
    	elseif ($cityId) {
    		if ($this->getCityModel($cityId)->getRegionId() == $this->getRegionId()) {
    			$this->setData('city_id', $this->getCityModel($cityId)->getCityId());
    		}
    	}
    	elseif (is_string($city)) {
    		$this->setData('city_id', $city);
    	}
    	return $this->getData('city_id');
    }
    public function getDistrictId()
    {
    	$districtId = $this->getData('district_id');
    	$district   = $this->getData('district');
    
    	if (!$districtId && is_numeric($district)) {
    		if ($this->getDistrictModel($district)->getCityId() == $this->getCityId()) {
    			$this->setData('district_id', $this->getDistrictModel($district)->getDistrictId());
    		}
    	}
    	elseif ($districtId) {
    		if ($this->getDistrictModel($districtId)->getCityId() == $this->getCityId()) {
    			$this->setData('district_id', $this->getDistrictModel($districtId)->getDistrictId());
    		}
    	}
    	elseif (is_string($district)) {
    		$this->setData('district_id', $district);
    	}
    	return $this->getData('district_id');
    }

    public function getRegionId()
    {
        $regionId = $this->getData('region_id');
        $region   = $this->getData('region');
        if (!$regionId) {
            if (is_numeric($region)) {
                $this->setData('region_id', $region);
                $this->unsRegion();
            } else {
                $regionModel = Mage::getModel('directory/region')
                    ->loadByCode($this->getRegionCode(), $this->getCountryId());
                $this->setData('region_id', $regionModel->getId());
            }
        }
        return $this->getData('region_id');
    }
    
   

    public function getCountry()
    {
        /*if ($this->getData('country_id') && !$this->getData('country')) {
            $this->setData('country', Mage::getModel('directory/country')
                ->load($this->getData('country_id'))->getIso2Code());
        }
        return $this->getData('country');*/
        $country = $this->getCountryId();
        return $country ? $country : $this->getData('country');
    }

    /**
     * Retrive country model
     *
     * @return Mage_Directory_Model_Country
     */
    public function getCountryModel()
    {
        if(!isset(self::$_countryModels[$this->getCountryId()])) {
            self::$_countryModels[$this->getCountryId()] = Mage::getModel('directory/country')
                ->load($this->getCountryId());
        }

        return self::$_countryModels[$this->getCountryId()];
    }

    /**
     * Retrive country model
     *
     * @return Mage_Directory_Model_Country
     */
    public function getRegionModel($region=null)
    {
        if(is_null($region)) {
            $region = $this->getRegionId();
        }

        if(!isset(self::$_regionModels[$region])) {
            self::$_regionModels[$region] = Mage::getModel('directory/region')->load($region);
        }

        return self::$_regionModels[$region];
    }
    
    public function getCityModel($city=null)
    {
    	if(is_null($city)) {
    		$city = $this->getCityId();
    	}
    
    	if(!isset(self::$_cityModels[$city])) {
    		self::$_cityModels[$city] = Mage::getModel('magease_directoryoptimized/city')->load($city);
    	}
    
    	return self::$_cityModels[$city];
    }
    
    public function getDistrictModel($district=null)
    {
    	if(is_null($district)) {
    		$district = $this->getDistrictId();
    	}
    
    	if(!isset(self::$_districtModels[$district])) {
    		self::$_districtModels[$district] = Mage::getModel('magease_directoryoptimized/district')->load($district);
    	}
    
    	return self::$_districtModels[$district];
    }

    /**
     * @deprecated for public function format
     */
    public function getHtmlFormat()
    {
        return $this->getConfig()->getFormatByCode('html');
    }

    /**
     * @deprecated for public function format
     */
    public function getFormated($html=false)
    {
        return $this->format($html ? 'html' : 'text');
        //Mage::getModel('directory/country')->load($this->getCountryId())->formatAddress($this, $html);
    }

    public function format($type)
    {
        if(!($formatType = $this->getConfig()->getFormatByCode($type))
            || !$formatType->getRenderer()) {
            return null;
        }
        Mage::dispatchEvent('customer_address_format', array('type' => $formatType, 'address' => $this));
        return $formatType->getRenderer()->render($this);
    }

    /**
     * Retrive address config object
     *
     * @return Mage_Customer_Model_Address_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('customer/address_config');
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();
        $this->getRegion();
        return $this;
    }

    /**
     * Validate address attribute values
     *
     * @return bool
     */
    public function validate()
    {
        $errors = array();
        $this->implodeStreetAddress();
        if (!Zend_Validate::is($this->getFirstname(), 'NotEmpty')) {
            $errors[] = Mage::helper('customer')->__('Please enter the first name.');
        }

        // if (!Zend_Validate::is($this->getLastname(), 'NotEmpty')) {
        //     $errors[] = Mage::helper('customer')->__('Please enter the last name.');
        // }

        if (!Zend_Validate::is($this->getStreet(1), 'NotEmpty')) {
            $errors[] = Mage::helper('customer')->__('Please enter the street.');
        }

//         if (!Zend_Validate::is($this->getCity(), 'NotEmpty')) {
//             $errors[] = Mage::helper('customer')->__('Please enter the city.');
//         }

        if (!Zend_Validate::is($this->getTelephone(), 'NotEmpty')) {
            $errors[] = Mage::helper('customer')->__('Please enter the telephone number.');
        }

        $_havingOptionalZip = Mage::helper('directory')->getCountriesWithOptionalZip();
        if (!in_array($this->getCountryId(), $_havingOptionalZip)
            && !Zend_Validate::is($this->getPostcode(), 'NotEmpty')
        ) {
            $errors[] = Mage::helper('customer')->__('Please enter the zip/postal code.');
        }

        if (!Zend_Validate::is($this->getCountryId(), 'NotEmpty')) {
            $errors[] = Mage::helper('customer')->__('Please enter the country.');
        }

        if ($this->getCountryModel()->getRegionCollection()->getSize()
               && !Zend_Validate::is($this->getRegionId(), 'NotEmpty')
               && Mage::helper('directory')->isRegionRequired($this->getCountryId())
        ) {
            $errors[] = Mage::helper('customer')->__('Please enter the state/province.');
        }
        if ($this->getRegionModel()->getCityCollection()->getSize()
        		&& !Zend_Validate::is($this->getCityId(), 'NotEmpty')
        		
        ) {
        	$errors[] = Mage::helper('customer')->__('Please enter the city.');
        }
        
        if ($this->getCityModel()->getDistrictCollection()->getSize()
        		&& !Zend_Validate::is($this->getDistrictId(), 'NotEmpty')
        		
        ) {
        	$errors[] = Mage::helper('customer')->__('Please enter the District.');
        }

        if (empty($errors) || $this->getShouldIgnoreValidation()) {
            return true;
        }
        return $errors;
    }
}
