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
class MageShop_PagarMe_Model_Resource_Hub
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
        $this->_init('mageshop_pagarme/hub', 'id');
    }

    public function loadByStoreId(MageShop_PagarMe_Model_Hub $object, $storeId)
    {
        $adapter = $this->_getReadAdapter();
        $binds = array(':store_id' => (int) $storeId);
        $select = $adapter->select()
            ->from($this->getMainTable())
            ->where('store_id = :store_id');

        $data = $adapter->fetchRow($select, $binds);
        if ($data) {
            $object->setData($data);
        } else {
            $object->setData(array());
        }
        return $this;
    }

    public function loadByInstallId(MageShop_PagarMe_Model_Hub $object, $installId)
    {
        $adapter = $this->_getReadAdapter();
        $binds = array(':install_id' => (string) $installId);
        $select = $adapter->select()
            ->from($this->getMainTable())
            ->where('install_id = :install_id');

        $data = $adapter->fetchRow($select, $binds);
        if ($data) {
            $object->setData($data);
        } else {
            $object->setData(array());
        }
        return $this;
    }
    
}
