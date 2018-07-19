<?php
require_once ("app/Mage.php");
umask(0);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
ini_set('memory_limit','1024M');
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

Mage::getModel('catalog/product')->load(Mage::getModel('catalog/product')->getIdBySku('JCWHB651H2'))->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)->save();

?>