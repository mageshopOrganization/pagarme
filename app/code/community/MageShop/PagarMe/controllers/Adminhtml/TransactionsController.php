<?php

/**
 * Class MageShop_PagarMe_Adminhtml_TransactionsController
 */
class MageShop_PagarMe_Adminhtml_TransactionsController extends Mage_Adminhtml_Controller_Action
{
    protected $_helper = null;

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('mageshop_pagarme/mageshop_pagarme_transactions');
    }
    
    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    public function viewAction()
    {
        $this->_initTransacao();

        $this->_initAction()
            ->renderLayout();
    }

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('mageshop_pagarme/mageshop_pagarme_transactions')
            ->_title($this->_getHelper()->__('pagar.me'))->_title($this->_getHelper()->__('Transactions'))
            ->_addBreadcrumb($this->_getHelper()->__('pagar.me'), $this->_getHelper()->__('pagar.me'))
            ->_addBreadcrumb($this->_getHelper()->__('Transactions'), $this->_getHelper()->__('Transactions'));

        return $this;
    }

    protected function _initTransacao()
    {
        $helper = Mage::helper('mageshop_pagarme');
        $id = $this->getRequest()->getParam('id');

        if (!$id) {
            $this->_forward('noRoute');
            return;
        }

        $log = Mage::getModel('mageshop_pagarme/transaction')->load($id);

        if (!$log->getId()) {
            $helper->getAdminSession()->addError($this->_getHelper()->__('This pagar.me transaction was not found.'));
            $this->_redirectReferer();
            return;
        }

        $order = Mage::getModel('sales/order')->load($log->getOrderId());

        if (!$order->getId()) {
            $helper->getAdminSession()->addError($this->_getHelper()->__('The related order was not found.'));
            $this->_redirectReferer();
            return;
        }

        Mage::register('current_transacao', $log);
    }

    /**
     * @return Mage_Adminhtml_Helper_Data|Mage_Core_Helper_Abstract|null
     */
    protected function _getHelper()
    {
        if (!$this->_helper) {
            $this->_helper = Mage::helper('mageshop_pagarme');
        }
        return $this->_helper;
    }
}