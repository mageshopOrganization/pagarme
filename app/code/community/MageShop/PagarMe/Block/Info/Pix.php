<?php

class MageShop_PagarMe_Block_Info_Pix extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mageshop/pagarme/payment/info/pix.phtml');
    }

    /**
     * Retrieve payment info data
     *
     * @return mixed
     */
    public function getPaymentInfo()
    {
        $payment = $this->getInfo();
        $order = $payment->getOrder();
        $pixInformation = $this->getTransaction($payment);
        return $pixInformation;
    }

    public function getTransaction($payment)
    {
        $obj = $payment->getAdditionalInformation('transaction');
        return $obj ? json_decode($obj, true) : null;
    }
    /**
     * @return mixed
     */
    public function getUpdateOrderUrl($orderIdMagento, $orderIdPagarme)
    {
        return Mage::helper('adminhtml')->getUrl('*/sales_charges/order', array('order_id' => $orderIdMagento, "order_pagarme" => $orderIdPagarme));
    }
}
