<?php

class MageShop_PagarMe_Model_Quote_Address_Interest extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    private $_helper;
    /**
     * Constructor that should initiaze
     */
    public function __construct()
    {
        $this->setCode('mageshop_pagarme_insterest'); //
        $this->_helper = Mage::helper("mageshop_pagarme/interest");
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
        $paymentCcPagarMe = $address->getQuote()->getPayment()->getMethod() == MageShop_PagarMe_Model_Method_Cc::CODE_PAYMENT;
        $apply = $this->_helper->applyInterest($address->getQuote()->getPagarMeCcSplitNumber());
        $ammount = $address->getQuote()->getPagarMeInterest();
        if ($ammount > 0 && $ammount != null && $apply && $paymentCcPagarMe) {
            $this->_setBaseAmount($ammount);
            $this->_setAmount($address->getQuote()->getStore()->convertPrice($ammount, false));
            $address->setPagarMeInterest($ammount);
            $address->setPagarMeBaseInterest($ammount);
        } else {
            $this->_setBaseAmount(0.00);
            $this->_setAmount(0.00);
            $address->setPagarMeInterest(0.00);
            $address->setPagarMeBaseInterest(0.00);
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
        if ($address->getPagarMeInterest() != 0 && $address->getAddressType() == 'shipping') {
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => $this->_helper->__("Juros de %d%%", $this->_helper->percentage),
                'value' => $address->getPagarMeInterest(),
            ));
        }
    }
}
