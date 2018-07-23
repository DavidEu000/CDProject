<?php

$installer = $this;
$installer->startSetup();
try {
	$installer->run("

		ALTER TABLE {$this->getTable('sm_menu_items')} ADD `show_hot` smallint(6) NOT NULL default '2';

	");
} catch (Exception $e) {

}

$installer->endSetup();