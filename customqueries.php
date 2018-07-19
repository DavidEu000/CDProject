<?php 

require 'app/Mage.php';
Mage::app();

Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

echo 'Nothing to do....';
// $district = Mage::getSingleton('eav/config')->getAttribute('customer_address', 'district');
// $district->setData('used_in_forms', array(
// 		'adminhtml_checkout',
// 		'adminhtml_customer',
// 		'adminhtml_customer_address'
// ));
// $district->save();



