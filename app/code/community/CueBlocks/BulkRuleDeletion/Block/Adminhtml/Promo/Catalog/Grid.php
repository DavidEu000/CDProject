<?php
/**
 * Created by Ravinder.
 * Date: 29/3/16
 * Time: 12:54 PM
 */ 
class CueBlocks_BulkRuleDeletion_Block_Adminhtml_Promo_Catalog_Grid extends Mage_Adminhtml_Block_Promo_Catalog_Grid {

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('rule_id');
        $this->getMassactionBlock()->setFormFieldName('rule');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => Mage::helper('rule')->__('Delete'),
            'url'      => $this->getUrl('*/bulkRule/massCatalogRuleDelete'),
            'confirm'  => Mage::helper('rule')->__('Are you sure?')
        ));
        return $this;
    }

}