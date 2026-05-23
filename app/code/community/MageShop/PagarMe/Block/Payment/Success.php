<?php 

class MageShop_PagarMe_Block_Payment_Success extends Mage_Payment_Block_Info{

    private $order;

    public function _construct()
    {
        parent::_construct();
        $this->initOrder();
        return $this;
    }
    public function initOrder()
    {
        $orderId = Mage::getSingleton("checkout/session")->getLastOrderId();
        $order = Mage::getModel("sales/order")->load($orderId);
        Mage::register('current_order', $order);
        $this->setOrder($order);
        return $this;
    }

    public function payment()
    {
        return $this->order()->getPayment();
    }

    /**
     * Get the value of order
     */
    public function order()
    {
        return $this->order;
    }

    /**
     * Set the value of order
     */
    public function setOrder($order): self
    {
        $this->order = $order;

        return $this;
    }
}