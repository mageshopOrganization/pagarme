<?php

/**
 * Class MageShop_PagarMe_Block_Sales_Order_Totals
 */
class MageShop_PagarMe_Block_Sales_Order_Totals extends Mage_Sales_Block_Order_Totals
{
    /**
     * Initialize order totals array
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _initTotals()
    {
        parent::_initTotals();
        $data = [];
        $data['code']  = 'juros';
        $data['field'] = 'juros';
        $data['value'] = 11;
        $data['label'] = 'Juros: ';
        $this->addTotalBefore(new Varien_Object($data), 'grand_total');

        if ($this->getSource()->getPagarMeInterest() > 0) {
            $data          = [];
            $data['code']  = 'juros';
            $data['field'] = 'juros';
            $data['value'] = $this->getSource()->getPagarMeInterest();
            $data['label'] = 'Juros: ';
            $this->addTotalBefore(new Varien_Object($data), 'grand_total');
        }

        if ($this->getSource()->getPagarMeDiscount() < 0) {
            $data          = [];
            $data['code']  = 'desconto';
            $data['field'] = 'desconto';
            $data['value'] = $this->getSource()->getPagarMeDiscount();
            $data['label'] = 'Desconto: ';
            $this->addTotalBefore(new Varien_Object($data), 'grand_total');
        }

        return $this;
    }
}
