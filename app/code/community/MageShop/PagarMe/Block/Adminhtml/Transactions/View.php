<?php

/**
 * Class MageShop_PagarMe_Block_Adminhtml_Transactions_View
 */
class MageShop_PagarMe_Block_Adminhtml_Transactions_View extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_transacao = null;

    protected $_order = null;

    protected $pagarme = null;

    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'mageshop_pagarme/adminhtml_transactions';
        $this->_mode = 'view';

        parent::__construct();

        $this->_removeButton('delete');
        $this->_removeButton('reset');
        $this->_removeButton('save');
        $this->setId('transactions_view');
    }

    /**
     * @return mixed|null
     */
    public function getTransacao()
    {
        if (empty($this->_transacao)) {
            $this->_transacao = Mage::registry('current_transacao');
        }
        return $this->_transacao;
    }

    /**
     * @return Mage_Core_Model_Abstract|null
     */
    public function getOrder()
    {
        if (empty($this->_order) && $this->hasTransacao()) {
            $this->_order = Mage::getModel('sales/order')->load($this->getTransacao()->getOrderId());
        }
        return $this->_order;
    }

    /**
     * @return bool
     */
    public function hasTransacao()
    {
        $transacao = $this->getTransacao();
        return !empty($transacao);
    }

    /**
     * @return bool
     */
    public function hasOrder()
    {
        $order = $this->getOrder();
        return !empty($order);
    }

    /**
     * @return string
     */
    public function getHeaderText()
    {
        if (!$this->hasTransacao()) {
            return '';
        }

        $header = $this->pagarme()->__(
            'Pagar.me Transaction # %s | %s', $this->getTransacao()->getId(), $this->formatDate(
                $this->getTransacao()->getCreatedDate(), 'medium', true
        )
        );
        return $this->escapeHtml($header);
    }

    /**
     * @return string
     */
    public function getInfoText()
    {
        if (!$this->hasTransacao()) {
            return "";
        }

        $info = $this->pagarme()->__('Pagar.me Transaction # %s | Informations', $this->getTransacao()->getId());
        return $this->escapeHtml($info);
    }

    /**
     * @return mixed
     */
    public function getStatusDescription()
    {
        $array = Mage::getModel('mageshop_pagarme/system_config_transaction_status')->toOptionArray();
        return $array[$this->getTransacao()->getTransactionStatus()];
    }

    /**
     * @return Mage_Core_Helper_Abstract|null
     */
    protected function pagarme()
    {
        if (!$this->pagarme) {
            $this->pagarme = Mage::helper('mageshop_pagarme');
        }
        return $this->pagarme;
    }

    public function convertBr($amount)
    {
      return number_format( $amount / 100 , 2, ",", ".");
    }
}