<?php

/**
 * Class MageShop_PagarMe_Adminhtml_Sales_ChargesController
 */
class MageShop_PagarMe_Adminhtml_Sales_ChargesController extends Mage_Adminhtml_Controller_Action
{
    private $_helper = null;

    /**
     * Da acesso para qualquer usuario adm
     *
     * @return void
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * @param null $orderId
     *
     * @return bool|Mage_Core_Model_Abstract
     * @throws Mage_Core_Exception
     */
    protected function _initOrder($orderId = null)
    {

        if (empty($orderId)) {
            $orderId = $this->getRequest()->getParam('order_id');
        }
        if (!$orderId) {
            $this->_forward('noRoute');
            return false;
        }

        $order = Mage::getModel('sales/order')->load($orderId);


        if (!$order->getId()) {
            $this->_forward('noRoute');
            return false;
        }



        if (!Mage::registry('current_order')) {
            Mage::register('current_order', $order);
        }

        return $order;
    }

    protected function initCharge($chargeId = null)
    {
        if (empty($chargeId)) {
            $chargeId = $this->getRequest()->getParam('order_id');
        }

        if (!$chargeId) {
            $this->_forward('noRoute');
            return false;
        }

        $charge = Mage::getModel('mageshop_pagarme/charge')->loadByIdCharge($chargeId);


        if (!$charge->getOrderId()) {
            $this->_forward('noRoute');
            return false;
        }

        if (!Mage::registry('current_charge')) {
            Mage::register('current_charge', $charge);
        }

        return $charge;
    }

    private function _amount($handler)
    {
        $amount = $this->getRequest()->getPost('amount');

        if (!$amount) {
            return  $handler->getTransactionCharge('amount');
        }

        if (strpos($amount, '.') !== false || strpos($amount, ',') !== false) {
            $amount = str_replace(".", "", $amount);
            $amount = str_replace(",", ".", $amount);
            $amount = $this->_getHelper()->amount($amount);
        }
        return $amount;
    }
    public function orderAction()
    {
        try {
            $orderId = $this->getRequest()->getParam('order_pagarme');
            if (!$orderId) {
                $this->_forward('noRoute');
                return false;
            }
            $order = $this->_initOrder();
            $handler = Mage::getModel('mageshop_pagarme/orders_orderHandler');
            $handler->order($orderId);
            $handler->process($order);
            $this->getAdminSession()->addSuccess($this->__("Successful update."));
        } catch (\Exception $e) {
            $this->getAdminSession()->addError($e->getMessage());
        }
        // Redirecionar para a página de visualização do pedido
        $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
    }

    public function voidAction()
    {
        try {
            $charge = $this->initCharge();
            $order = $this->_initOrder($charge->getOrderId());

            $handler = Mage::getModel('mageshop_pagarme/orders_orderHandler');

            $handler->charge($charge->getChargeId());
            $status = $handler->getTransactionCharge()->getStatus();

            if ($handler->getStatus($status) == MageShop_PagarMe_Model_Orders_Transaction::PENDING) {
                $handler->canceled($handler->getTransactionCharge()->getId(), $this->_amount($handler));
            }

            $comment = $handler->pagarmeComment("%s", $handler->noteActionAdmin('capture'));
            $handler->addHistoryCommentOrder($order, $comment);

            $orderCharge = $handler->getTransactionCharge()->getOrder();
            if ($orderCharge && isset($orderCharge['id'])) {
                $handler->order($orderCharge['id']);
                $handler->process($order);
            }

            $handler->_updateCharge($charge, $handler->getTransactionCharge());

            // Definir a mensagem de sucesso
            $this->getAdminSession()->addSuccess($this->__('The charge has been canceled.'));
        } catch (\Exception $e) {
            $this->getAdminSession()->addError($e->getMessage());
        }

        // Redirecionar para a página de visualização do pedido
        $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
    }

    public function captureAction()
    {
        try {
            $charge = $this->initCharge();
            $order = $this->_initOrder($charge->getOrderId());

            $handler = Mage::getModel('mageshop_pagarme/orders_orderHandler');

            $handler->charge($charge->getChargeId());

            $status = $handler->getTransactionCharge()->getStatus();
            $orderCharge = $handler->getTransactionCharge()->getOrder();

            $comment = $handler->pagarmeComment("%s", $handler->noteActionAdmin('capture'));
            $handler->addHistoryCommentOrder($order, $comment);

            if ($handler->getStatus($status) == MageShop_PagarMe_Model_Orders_Transaction::PENDING) {
                $handler->capture($handler->getTransactionCharge()->getId(), $this->_amount($handler));
            }

            if ($orderCharge && isset($orderCharge['id'])) {
                $handler->order($orderCharge['id']);
                $handler->process($order);
            }

            $handler->_updateCharge($charge, $handler->getTransactionCharge());

            // Definir a mensagem de sucesso
            $this->getAdminSession()->addSuccess($this->__('The charge has been successfully captured.'));
        } catch (\Exception $e) {
            $this->getAdminSession()->addError($e->getMessage());
        }

        // Redirecionar para a página de visualização do pedido
        $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
    }

    public function getAdminSession()
    {
        return Mage::getSingleton('adminhtml/session');
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
