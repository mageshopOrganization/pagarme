<?php

$installer = Mage::getResourceModel('sales/setup', 'default_setup');

$installer->startSetup();

$installer->addAttribute('order', 'pagar_me_base_interest', ['label' => 'Base Juros', 'type' => 'decimal']);
$installer->addAttribute('order', 'pagar_me_interest', ['label' => 'Juros', 'type' => 'decimal']);
$installer->addAttribute('quote', 'pagar_me_base_interest', ['label' => 'Base Juros', 'type' => 'decimal']);
$installer->addAttribute('quote_address', 'pagar_me_base_interest', ['label' => 'Base Juros', 'type'  => 'decimal']);
$installer->addAttribute('quote_address', 'pagar_me_interest', ['label' => 'Juros', 'type' => 'decimal']);
$installer->addAttribute('quote_address', 'pagar_me_base_discount', ['label' => 'Base Desconto', 'type'  => 'decimal']);
$installer->addAttribute('quote_address', 'pagar_me_discount', ['label' => 'Desconto', 'type'  => 'decimal']);
$installer->addAttribute('quote_address', 'pagar_me_status', ['label' => 'Status', 'type' => 'varchar']);
$installer->addAttribute('quote', 'pagar_me_base_discount', ['label' => 'Base Desconto', 'type'  => 'decimal']);
$installer->addAttribute('quote', 'pagar_me_discount', ['label' => 'Desconto', 'type'  => 'decimal']);
$installer->addAttribute('order', 'pagar_me_base_discount', ['label' => 'Base Desconto', 'type'  => 'decimal']);
$installer->addAttribute('order', 'pagar_me_discount', ['label' => 'Desconto', 'type'  => 'decimal']);

$installer->endSetup();