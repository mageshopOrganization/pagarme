<?php

/**
 * This source file is subject to the MIT License.
 * It is also available through http://opensource.org/licenses/MIT
 *
 * @category  Mageshop
 * @package   MageShop_PagarMe
 * @author    Vitor Costa <vitor@tryideas.com.br>
 * @copyright 2023 Vitor Costa
 * @license   http://opensource.org/licenses/MIT MIT
 * @link      https://github.com/csvitor/MageShop_PagarMe
 */
class MageShop_PagarMe_Model_Resource_Transaction
    extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Resource initialization
     *
     * @see Mage_Core_Model_Resource_Abstract::_construct()
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageshop_pagarme/transaction', 'id');
    }

    public function loadByIncrementId(MageShop_PagarMe_Model_Transaction $object, $incrementId)
    {
        $adapter = $this->_getReadAdapter();
        $binds = array(':increment_id' => (string)$incrementId);
        $select = $adapter->select()
            ->from($this->getMainTable())
            ->where('increment_id = :increment_id');

        $data = $adapter->fetchRow($select, $binds);
        if ($data) {
            $object->setData($data);
        } else {
            $object->setData(array());
        }
        return $this;
    }

    public function loadByTId(MageShop_PagarMe_Model_Transaction $object, $TId)
    {
        $adapter = $this->_getReadAdapter();
        $binds = array(':tid' => (string)$TId);
        $select = $adapter->select()
            ->from($this->getMainTable())
            ->where('tid = :tid');

        $data = $adapter->fetchRow($select, $binds);
        if ($data) {
            $object->setData($data);
        } else {
            $object->setData(array());
        }
        return $this;
    }
    
    /**
     * @param MageShop_PagarMe_Model_Resource_Transaction_Collection $collection
     *
     * @return $this
     */
    public function appendRealOrderIdToTransactionCollection(
        MageShop_PagarMe_Model_Resource_Transaction_Collection &$collection
    ) {
        $collection->getSelect()
            ->join(
                array('o' => 'sales_flat_order'),
                'o.entity_id = main_table.order_id',
                array(
                    'real_order_id' => 'o.increment_id',
                )
            );

        return $this;
    }
}
