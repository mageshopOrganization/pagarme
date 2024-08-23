<?php

class MageShop_PagarMe_Model_Method_Bankslip extends Mage_Payment_Model_Method_Abstract
{
    const CODE_PAYMENT = 'mageshop_pagarme_bankslip';
    protected $_code = MageShop_PagarMe_Model_Method_Bankslip::CODE_PAYMENT;
    protected $_formBlockType = 'mageshop_pagarme/form_bankslip';
    protected $_infoBlockType = 'mageshop_pagarme/info_bankslip';
    protected $_isGateway = false;
    protected $_canAuthorize = false;
    protected $_canCapture = false;
    protected $_canOrder = true;
    protected $_helper;

    public function order(Varien_Object $payment, $amount)
    {
        parent::order($payment, $amount);
        try {
            if ($this->canOrder()) {

                // classe responsavel por criar 
                $modelOrder = Mage::getModel('mageshop_pagarme/orders_order')
                    ->setPaymentMethod(MageShop_PagarMe_Model_Orders_Payment_Bankslip::PAYMENT_METHOD)
                    ->setPayment($payment)
                    ->setInfo($this->getInfoInstance());

                $modelOrder->getHelper('mageshop_pagarme/validation');
                $orderData = $modelOrder
                    ->transaction()
                    ->customer()
                    ->contacts()
                    ->address()
                    ->shippingAddress()
                    ->paymentBankslip()
                    ->items()
                    ->metaData()
                    ->getIp();

                $processor = Mage::getModel('mageshop_pagarme/orders_payment');
                $processor->setDataRequest($orderData->getData());
                $processor->setInfoInstance($orderData->getInfo());
                $processor->process($payment, $amount);
                $payment->setSkipOrderProcessing(true);
            }
        } catch (\Exception $e) {
            Mage::throwException($e->getMessage());
        }
        return $this;
    }


    /**
     * @param mixed $data
     *
     * @return Mage_Payment_Model_Method_Abstract
     * @throws Mage_Core_Exception
     */
    public function assignData($data)
    {
        $results = parent::assignData($data);
        return $results;
    }
}
