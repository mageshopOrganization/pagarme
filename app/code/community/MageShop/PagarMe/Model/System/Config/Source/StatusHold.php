<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to web@tryideas.com.br so we can send you a copy immediately.
 *
 * @category   MageShop
 * @package    MageShop_PagarMe
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MageShop_PagarMe_Model_System_Config_Source_StatusHold
{
    protected $_stateStatuses = array(
        Mage_Sales_Model_Order::STATE_HOLDED,
        Mage_Sales_Model_Order::STATE_NEW,
        Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
        Mage_Sales_Model_Order::STATE_PROCESSING,
    );

    public function toOptionArray()
    {
        $options = array();
        $options[] = array(
               'value' => '',
               'label' => Mage::helper('adminhtml')->__('-- Please Select --')
            );
        foreach ($this->_stateStatuses as $state) {
            $options[] = array(
               'value' => $state,
               'label' => Mage::helper('adminhtml')->__($state)
            );
        }
        return $options;
    }
}