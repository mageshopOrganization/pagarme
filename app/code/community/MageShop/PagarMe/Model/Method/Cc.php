<?php

class MageShop_PagarMe_Model_Method_Cc extends Mage_Payment_Model_Method_Abstract
{
    const CODE_PAYMENT = 'mageshop_pagarme_cc';
    protected $_code = MageShop_PagarMe_Model_Method_Cc::CODE_PAYMENT;
    protected $_formBlockType = 'mageshop_pagarme/form_creditCard';
    protected $_infoBlockType = 'mageshop_pagarme/info_creditCard';
    protected $_isGateway = false;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canRefund = true;
    protected $_canOrder = true;
    protected $_canVoid = true;
    protected $_helper;

    const ACTION_AUTHORIZE_CAPTURE = 'authorize_capture';
    const ACTION_AUTHORIZE = 'authorize';

    public function order(Varien_Object $payment, $amount)
    {
        parent::order($payment, $amount);
        try {
            if ($this->canOrder()) {
                // criação do corpo do post cc
                $modelOrder = Mage::getModel('mageshop_pagarme/orders_order')
                    ->setPaymentMethod(MageShop_PagarMe_Model_Orders_Payment_CreditCard::PAYMENT_METHOD)
                    ->setPayment($payment)
                    ->setInfo($this->getInfoInstance());

                $modelOrder->getHelper('mageshop_pagarme/validation');
                $orderData = $modelOrder->transaction()
                    ->customer()
                    ->contacts()
                    ->address()
                    ->shippingAddress()
                    ->paymentCc()
                    ->items()
                    ->metaData()
                    ->getIp()
                    ->getSessionId()
                    ->getAntifraudEnabled();

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

        $info = $this->getInfoInstance();
        $info->setCheckNo($data->getCheckNo())->setCheckDate($data->getCheckDate());
        $info->getQuote()->setPagarMeDiscount(0.0);
        $info->getQuote()->setPagarMeInterest(0.0);
        $info->getQuote()->setTotalsCollectedFlag(false)->collectTotals();

        $split_number = @$data->getMageshopPagarmeCcInstallments();

        if (!empty($split_number)) {
            $info->getQuote()->setPagarMeCcSplitNumber($split_number);
        }

        $_interest_helper = Mage::helper("mageshop_pagarme/interest");
        if ($_interest_helper->applyInterest($split_number)) {
            $_interest_helper->setInterestCc($info);
        }

        $_discount_helper = Mage::helper("mageshop_pagarme/discount");
        if ($_discount_helper->getDiscountActiveCreditCard()) {
            $_discount_helper->setDiscountCc($info);
        }

        $results = parent::assignData($data);
        $dataCc = $this->saveCcAssignData($data);
        $info->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
        $this->getInfoInstance()->getQuote()->setMageShopPagarmeCc($dataCc);
        return $results;
    }

    /**
     * Function to save assign data
     * @var array|mixed
     * @return array
     */
    public function saveCcAssignData($data)
    {
        return [
            "mageshop_pagarme_cc_token"        => $data['mageshop_pagarme_cc_token'],
            "mageshop_pagarme_cc_installments" => $data['mageshop_pagarme_cc_installments'],
        ];
    }
}
