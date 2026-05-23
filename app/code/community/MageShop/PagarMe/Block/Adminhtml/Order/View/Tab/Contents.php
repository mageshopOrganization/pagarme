<?php

class MageShop_PagarMe_Block_Adminhtml_Order_View_Tab_Contents
extends Mage_Adminhtml_Block_Template
implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
  /**
   * Simple construct
   */
  public function _construct()
  {
    parent::_construct();
    $this->setTemplate('mageshop/pagarme/order/view/tab/contents.phtml');
  }

  public function canShowTab()
  {
    return true;
  }

  public function isHidden()
  {
    return false;
  }

  /**
   * Function to get tab label
   */
  public function getTabLabel()
  {
    return $this->__('Charges');
  }

  /**
   * Function to get tab title
   */
  public function getTabTitle()
  {
    return $this->__('Charges');
  }

  /**
   * Function to get Order
   */
  public function getOrder()
  {
    return Mage::registry('current_order');
  }

  public function getTransactions()
  {
    // Método para obter as transações do pedido
    return Mage::getModel('sales/order_payment_transaction')
      ->getCollection()
      ->addOrderIdFilter($this->getOrder()->getId());
  }

  public function getCharges()
  {
    // Método para obter as transações do pedido
    return Mage::getModel('mageshop_pagarme/charge')
      ->getCollection()
      ->addFieldToFilter('order_id', $this->getOrder()->getId());
  }

  public function canCapture($status)
  {
    return (bool) (MageShop_PagarMe_Model_System_Config_Transaction_Status::PENDING === $status || MageShop_PagarMe_Model_System_Config_Transaction_Status::PROCESSING === $status);
  }

  public function canVoid($status)
  {
    return (bool) (MageShop_PagarMe_Model_System_Config_Transaction_Status::PENDING === $status || MageShop_PagarMe_Model_System_Config_Transaction_Status::PROCESSING === $status);
  }

  /**
   * @return mixed
   */
  public function getCaptureUrl($id)
  {
    return Mage::helper('adminhtml')->getUrl('*/sales_charges/capture', array('order_id' => $id));
  }


  /**
   * @return mixed
   */
  public function getVoidUrl($id)
  {
    return Mage::helper('adminhtml')->getUrl('*/sales_charges/void', array('order_id' => $id));
  }

  public function canAct($method, $status)
  {
    return ($this->canCapture($status) || $this->canVoid($status)) && $this->isCapture($method);
  }

  public function isCapture($method)
  {
    return MageShop_PagarMe_Model_Orders_Payment_CreditCard::PAYMENT_METHOD == $method;
  }
  /**
   * Retorna o payload da cobrança
   *
   * @param string $payload
   * @return array
   */
  public function payload($payload)
  {
    if (gettype(json_decode($payload, true)) == 'array') {
      $payload = json_decode($payload, true);
    }
    return $payload;
  }

  public function convertBr($amount)
  {
    return number_format( $amount / 100 , 2, ",", ".");
  }
}
