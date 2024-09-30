<?php

/**
 * Class MageShop_PagarMe_Block_Adminhtml_Order_Totals
 */
class MageShop_PagarMe_Block_Adminhtml_Order_Totals extends Mage_Adminhtml_Block_Sales_Order_Totals
{
    /**
     * @return $this|Mage_Sales_Block_Order_Totals
     */
    protected function _initTotals()
    {
        parent::_initTotals();

        if (! empty($this->getSource()->getPagarMeInterest())) {
            $data               = [];
            $data['code']       = 'juros';
            $data['strong']     = true;
            $data['value']      = $this->getSource()->getPagarMeInterest();
            $data['base_value'] = $this->getSource()->getPagarMeBaseInterest();
            $data['label']      = 'Juros';
            $data['area']       = 'footer';
            $this->addTotalBefore(new Varien_Object($data), 'grand_total');
        }

        if ($this->getSource()->getPagarMeDiscount() < 0) {
            $data               = [];
            $data['code']       = 'desconto';
            $data['strong']     = true;
            $data['value']      = $this->getSource()->getPagarMeDiscount();
            $data['base_value'] = $this->getSource()->getPagarMeBaseDiscount();
            $data['label']      = 'Desconto';
            $data['area']       = 'footer';
            $this->addTotalBefore(new Varien_Object($data), 'grand_total');
        }

        return $this;
    }
}