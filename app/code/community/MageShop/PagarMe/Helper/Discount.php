<?php

class MageShop_PagarMe_Helper_Discount extends MageShop_PagarMe_Helper_Data
{
    const MS_PAGARME_CC_DISCOUNT_ENABLE = "discount_active";
    const MS_PAGARME_CC_DISCOUNT_PERCENTAGE = "discount_percentage";
    const MS_PAGARMEY_CC_DISCOUNT_INSTALLMENT = "discount_installment";
    const MS_PAGARME_CC_DISCOUNT_LABEL = "discount_label";

    public function getDiscountActiveCreditCard()
    {
        return (bool) $this->getConfigData(self::MS_PAGARME_CC_DISCOUNT_ENABLE, 'mageshop_pagarme_cc');
    }
    public function getDiscountPercentageCreditCard()
    {
        return (float) $this->getConfigData(self::MS_PAGARME_CC_DISCOUNT_PERCENTAGE, 'mageshop_pagarme_cc');
    }
    public function getDiscountInstallmentCreditCard()
    {
        return (float) $this->getConfigData(self::MS_PAGARMEY_CC_DISCOUNT_INSTALLMENT, 'mageshop_pagarme_cc');
    }
    public function getDiscountLabelCreditCard()
    {
        return $this->__($this->getConfigData(self::MS_PAGARME_CC_DISCOUNT_LABEL, 'mageshop_pagarme_cc'), $this->getDiscountPercentageCreditCard());
    }

    public function setDiscountCc($info)
    {
        $value_discount = 0;
        if ($this->percentage()) {
            $value_discount = $this->getDiscountCc($info->getQuote()->getSubtotal(), false);
        }
        if ($value_discount < 0 && $this->getSplitOk((int) $info->getQuote()->getPagarMeCcSplitNumber())) {
            $info->getQuote()->setPagarMeDiscount($value_discount);
        } else {
            $info->getQuote()->setPagarMeDiscount(0.0);
        }
    }

    public function getSplitOk($split)
    {
        return (bool) (!empty($split) && $split <= $this->getDiscountInstallmentCreditCard());
    }
    public function percentage()
    {
        return (bool) ($this->getDiscountPercentageCreditCard() > 0 && $this->getDiscountPercentageCreditCard() < 100);
    }
    public function getDiscountCc($total, $convert = true)
    {
        return (float) $this->monetize((!$convert ? -1 : 1) * $this->getDiscountPercentageCreditCard() * 0.01 * $total);
    }
}
