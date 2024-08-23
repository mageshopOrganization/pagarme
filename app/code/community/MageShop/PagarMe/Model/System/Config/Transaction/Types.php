<?php

/**
 * Class Rede_Adquirencia_Model_System_Config_Transaction_Types
 */
class MageShop_PagarMe_Model_System_Config_Transaction_Types
{
    protected $_options = array();

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (empty($this->_options)) {
            $helper = Mage::helper('mageshop_pagarme');
            $this->_options[] = array(
                'value' => MageShop_PagarMe_Model_Method_Cc::ACTION_AUTHORIZE_CAPTURE,
                'label' => $helper->__('With automatic capture')
            );
            $this->_options[] = array(
                'value' => MageShop_PagarMe_Model_Method_Cc::ACTION_AUTHORIZE,
                'label' => $helper->__('With subsequent capture')
            );
        }
        return $this->_options;
    }
}