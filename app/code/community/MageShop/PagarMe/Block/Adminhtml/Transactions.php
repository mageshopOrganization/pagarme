<?php

/**
 * Class MageShop_PagarMe_Block_Adminhtml_Transactions
 */
class MageShop_PagarMe_Block_Adminhtml_Transactions extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function _construct()
    {
        $this->_blockGroup = 'mageshop_pagarme';
        $this->_controller = 'adminhtml_transactions';
        $this->_headerText = $this->escapeHtml(Mage::helper('mageshop_pagarme')->__('Transactions'));

        parent::_construct();
    }

    /**
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $this->_removeButton('add');
        return parent::_prepareLayout();
    }
}