<?php

$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();
$tableJob = $installer->getTable('mageshop_pagarme/job');
$connection->dropTable($tableJob);

$table = $connection->newTable($tableJob)
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true
    ], 'ID')
    ->addColumn('increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, [
        'nullable' => false
    ], 'Id Pedido')
    ->addColumn('notification_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, [
        'nullable' => false,
        'default'  => '',
        'collate'  => 'utf8_general_ci'
    ], 'Id da notificação')
    ->addColumn('payload', Varien_Db_Ddl_Table::TYPE_TEXT, null, [
        'nullable' => false,
        'default'  => '',
        'collate'  => 'utf8_general_ci'
    ], 'Carga útil')
    ->addColumn('attempts', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, [
        'nullable' => false
    ], 'Tentativas.')
    ->addColumn('obs', Varien_Db_Ddl_Table::TYPE_TEXT, null, [
        'nullable' => false,
        'default'  => '',
        'collate'  => 'utf8_general_ci'
    ], 'Observers.')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, [
        'nullable' => false
    ], 'Data')
    ->setComment('Controle de notificações');
$installer->getConnection()->createTable($table);

$tableTransaction = $installer->getTable('mageshop_pagarme/transaction');

$connection->dropTable($tableTransaction);

$table2 = $connection->newTable($tableTransaction)
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
            'identity' => true,
            'primary' => true,
            'nullable' => false
    ],'Internal ID.')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER,11,[
            'nullable' => false
    ], 'Reference Order ID.')
    ->addColumn('tid', Varien_Db_Ddl_Table::TYPE_VARCHAR, 225,[
            'nullable' => true
    ],'TID.')
    ->addColumn('charge_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, [
        'nullable' => false,
        'default'  => '',
        'collate'  => 'utf8_general_ci'
    ], 'Id da cobrança')
    ->addColumn('transaction_status',Varien_Db_Ddl_Table::TYPE_VARCHAR,100,[
            'nullable' => true
    ],'Transaction status from API.')
    ->addColumn('payment_method',Varien_Db_Ddl_Table::TYPE_VARCHAR,100,[
            'nullable' => true
    ],'Payment Method.')
    ->addColumn('amount', Varien_Db_Ddl_Table::TYPE_INTEGER,11,[
            'nullable' => true
    ],'Amount.')
    ->addColumn('capture_amount',Varien_Db_Ddl_Table::TYPE_INTEGER, 11,[
            'nullable' => true
    ],'Capture Amount.')
    ->addColumn('chargeback_amount', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, [
            'nullable' => true
    ],'Chargeback Amount.')
    ->addColumn('return_message', Varien_Db_Ddl_Table::TYPE_TEXT, null,[
            'nullable' => false
    ],'Return Message from API.')
    ->addColumn('created_date', Varien_Db_Ddl_Table::TYPE_DATETIME, null, [
        'nullable' => true
    ], 'Created date.');

$connection->createTable($table2);

$tableCharge = $installer->getTable('mageshop_pagarme/charge');
$connection->dropTable($tableCharge);

$table3 = $connection->newTable($tableCharge)
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true
    ], 'ID')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, [
        'nullable' => false,
        'default'  => 0,
    ], 'Id Pedido Magento')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, [
        'nullable' => false
    ], 'Id Pedido Pagarme')
    ->addColumn('charge_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, [
        'nullable' => false,
        'default'  => '',
        'collate'  => 'utf8_general_ci'
    ], 'Id da cobrança')
    ->addColumn('payment_method', Varien_Db_Ddl_Table::TYPE_TEXT, 255, [
        'nullable' => false,
        'default'  => '',
        'collate'  => 'utf8_general_ci'
    ], 'Método de Pagamento')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 255, [
        'nullable' => false,
        'default'  => '',
        'collate'  => 'utf8_general_ci'
    ], 'Status da cobrança')
    ->addColumn('payload', Varien_Db_Ddl_Table::TYPE_TEXT, null, [
        'nullable' => false,
        'default'  => '',
        'collate'  => 'utf8_general_ci'
    ], 'Carga útil')
    ->addColumn('amount', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'nullable' => false,
        'default' => 0,
    ], 'Amount')
    ->addColumn('capture_amount', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'nullable' => false,
        'default' => 0,
    ], 'Capture Amount')
    ->addColumn('void_amount', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'nullable' => false,
        'default' => 0,
    ], 'Void Amount')
    ->addColumn('chargeback_amount', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'nullable' => false,
        'default' => 0,
    ], 'Chargeback Amount')
    ->addColumn('attempts', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, [
        'nullable' => false,
        'default'  => 0,
    ], 'Tentativas.')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, [
        'nullable' => false
    ], 'Data')
    ->setComment('Cobrança de pagamento');
$installer->getConnection()->createTable($table3);

$tableHub = $installer->getTable('mageshop_pagarme/hub');
$connection->dropTable($tableHub);

$table4 = $connection->newTable($tableHub)
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true
    ], 'ID')
    ->addColumn('access_token', Varien_Db_Ddl_Table::TYPE_TEXT, 255, [
        'nullable' => false,
        'default'  => '',
        'collate'  => 'utf8_general_ci'
    ], 'Token de Integração')
    ->addColumn('account_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, [
        'nullable' => false,
        'default'  => '',
        'collate'  => 'utf8_general_ci'
    ], 'Id conta pagar.me')
    ->addColumn('account_public_key', Varien_Db_Ddl_Table::TYPE_TEXT, 255, [
        'nullable' => false,
        'default'  => '',
        'collate'  => 'utf8_general_ci'
    ], 'Token de Integração público')
    ->addColumn('install_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, [
        'nullable' => false,
        'default'  => '',
        'collate'  => 'utf8_general_ci'
    ], 'Install Id de Integração')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, [
        'nullable' => false,
        'default'  => 0,
    ], 'ID Loja')
    ->addColumn('user', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, [
        'nullable' => false,
        'default'  => 0,
    ], 'ID Admin')
    ->addColumn('payload', Varien_Db_Ddl_Table::TYPE_TEXT, null, [
        'nullable' => false,
        'default'  => '',
        'collate'  => 'utf8_general_ci'
    ], 'Carga útil')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, [
        'nullable' => false
    ], 'Data')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, [
        'nullable' => false
    ], 'Data Atualização')
    ->setComment('Data de criação');
$installer->getConnection()->createTable($table4);

$installer->endSetup();
