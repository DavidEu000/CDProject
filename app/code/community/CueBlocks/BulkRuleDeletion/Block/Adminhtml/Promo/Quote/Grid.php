<?php
/**
 * Created by Ravinder.
 * Date: 29/3/16
 * Time: 12:05 PM
 */ 
class CueBlocks_BulkRuleDeletion_Block_Adminhtml_Promo_Quote_Grid extends Mage_Adminhtml_Block_Promo_Quote_Grid {

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('rule_id');
        $this->getMassactionBlock()->setFormFieldName('rule');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => Mage::helper('rule')->__('Delete'),
            'url'      => $this->getUrl('*/bulkRule/massShoppingCartRuleDelete'),
            'confirm'  => Mage::helper('rule')->__('Are you sure?')
        ));
        return $this;
    }
    
}