<?php

class MageShop_PagarMe_Block_Form_Bankslip extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mageshop/pagarme/form/bankslip.phtml');
    }
    
    public function getInstructions()
    {
        return $this->helper('mageshop_pagarme/data')->getConfigData("instructions_checkout", "mageshop_pagarme_bankslip");
    }
}
