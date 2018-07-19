<?php
/*------------------------------------------------------------------------
 # SM Shop By - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Shopby_Model_Catalog_Config extends Mage_Catalog_Model_Config
{
    public function getAttributeUsedForSortByArray()
    {
        return array_merge(
            parent::getAttributeUsedForSortByArray(),
            array('most_viewed' => Mage::helper('catalog')->__('Most Viewed'))
        );
    }
}