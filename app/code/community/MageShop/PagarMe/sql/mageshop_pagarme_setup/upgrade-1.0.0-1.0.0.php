<?php
$installer = $this;
$installer->startSetup();

$table = $installer->getConnection();

if (!$table->tableColumnExists($installer->getTable('sales/order_payment'), 'pagarme_order_id')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('sales/order_payment'),
        'pagarme_order_id',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 255,
            'nullable' => true,
            'comment' => 'Transaction ID Pagarme'
        )
    );
}

if (!$table->tableColumnExists($installer->getTable('sales/order_payment'), 'pagarme_order_payload')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('sales/order_payment'),'pagarme_order_payload', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => null,
            'nullable' => true,
            'default' => '',
            'comment' => 'Transaction Pagarme JSON'
        )
    );
}

$installer->endSetup();