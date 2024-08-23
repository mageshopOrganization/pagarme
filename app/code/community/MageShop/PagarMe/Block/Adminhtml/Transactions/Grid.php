<?php

/**
 * Class MageShop_PagarMe_Block_Adminhtml_Transactions_Grid
 */
class MageShop_PagarMe_Block_Adminhtml_Transactions_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function _construct()
    {
        parent::_construct();

        $this->setDefaultSort('order_id');
        $this->setId('mageshop_pagarme_transactions_grid');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(false);
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('mageshop_pagarme/transaction_collection');
        Mage::getResourceModel('mageshop_pagarme/transaction')->appendRealOrderIdToTransactionCollection($collection);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $helper = Mage::helper('mageshop_pagarme');
        $this->addColumn(
            'order_id',
            array(
                'header' => $helper->__('Order'),
                'align' => 'left',
                'width' => '100px',
                'index' => 'real_order_id',
                'filter_index' => 'o.increment_id'
            )
        )->addColumn(
            'tid',
            array(
                'header' => $helper->__('TID'),
                'align' => 'center',
                'width' => '100px',
                'index' => 'tid'
            )
        )->addColumn(
            'created_date',
            array(
                'header' => $helper->__('Date/Hour'),
                'align' => 'left',
                'width' => '100px',
                'type' => 'datetime',
                'index' => 'created_date'
            )
        )->addColumn(
            'transaction_status',
            array(
                'header' => $helper->__('Status'),
                'align' => 'left',
                'width' => '200px',
                'index' => 'transaction_status',
                'type' => 'options',
                'options' => Mage::getModel('mageshop_pagarme/system_config_transaction_status')->toOptionArray(),
                'filter_index' => 'main_table.transaction_status'
            )
        )->addColumn(
            'charge_id',
            array(
                'header' => $helper->__('Charge ID'),
                'align' => 'left',
                'index' => 'charge_id'
            )

        )->addColumn(
            'payment_method',
            array(
                'header' => $helper->__('Forma de Pagamento'),
                'align' => 'left',
                'index' => 'payment_method'
            )
        );

        return parent::_prepareColumns();
    }

    /**
     * @param $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }
}
