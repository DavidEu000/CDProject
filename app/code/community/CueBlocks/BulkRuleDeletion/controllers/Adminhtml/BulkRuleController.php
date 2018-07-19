<?php
/**
 * Created by Ravinder.
 * Date: 29/3/16
 * Time: 12:07 PM
 */

class CueBlocks_BulkRuleDeletion_Adminhtml_BulkRuleController extends Mage_Adminhtml_Controller_Action
{
    public function massShoppingCartRuleDeleteAction()
    {
        $rulesIds = $this->getRequest()->getParam('rule');
        if(!is_array($rulesIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select rule(s).'));
        } else {
            try {
                $model = Mage::getModel('salesrule/rule');
                foreach ($rulesIds as $rulesId) {
                    $model->load($rulesId);
                    $model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were deleted.', count($rulesIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/promo_quote/index');
    }

    public function massCatalogRuleDeleteAction()
    {
        $rulesIds = $this->getRequest()->getParam('rule');
        if(!is_array($rulesIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select rule(s).'));
        } else {
            try {
                $model = Mage::getModel('catalogrule/rule');
                foreach ($rulesIds as $rulesId) {
                    $model->load($rulesId);
                    $model->delete();
                    Mage::app()->saveCache(1, 'catalog_rules_dirty');
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were deleted.', count($rulesIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/promo_catalog/index');
    }
}