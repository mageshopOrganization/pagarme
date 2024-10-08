<?php

class MageShop_PagarMe_Model_Quote_Address_Discount extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    private $_helper;
    /**
     * Constructor that should initiaze
     */
    public function __construct()
    {
        $this->setCode('mageshop_pagarme_desconto'); //
        $this->_helper = Mage::helper("mageshop_pagarme/discount");
    }

    /**
     * Used each time when collectTotals is invoked
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Your_Module_Model_Total_Custom
     */

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        if ($address->getData('address_type') == 'billing') {
            return $this;
        }
        if (!$this->_helper->getDiscountActiveCreditCard()) {
            return $this;
        }
        $paymentCcPagarMe = $address->getQuote()->getPayment()->getMethod() == MageShop_PagarMe_Model_Method_Cc::CODE_PAYMENT;
        $ammount = $address->getQuote()->getPagarMeDiscount();
        if ($ammount < 0 && $ammount != null && $this->_helper->getSplitOk((int) $address->getQuote()->getPagarMeCcSplitNumber()) && $paymentCcPagarMe) {
            $this->_setBaseAmount($ammount);
            $this->_setAmount($address->getQuote()->getStore()->convertPrice($ammount, false));
            $address->setPagarMeDiscount($ammount);
            $address->setPagarMeBaseDiscount($ammount);
        } else {
            $this->_setBaseAmount(0.00);
            $this->_setAmount(0.00);
            $address->setPagarMeDiscount(0.00);
            $address->setPagarMeBaseDiscount(0.00);
        }
        return $this;
    }

    /**
     * Used each time when totals are displayed
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Your_Module_Model_Total_Custom
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        if ($address->getPagarMeDiscount() != 0 && $address->getAddressType() == 'shipping' && $this->_helper->getDiscountActiveCreditCard()) {
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => $this->_helper->getDiscountLabelCreditCard(),
                'value' => $address->getPagarMeDiscount(),
            ));
        }
    }
}
