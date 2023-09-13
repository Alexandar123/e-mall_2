<?php

namespace Dynamic\Newsletter\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
  public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
    $setup->startSetup();

    $table = $setup->getTable('newsletter_subscriber');

    $setup->getConnection()->addColumn(
      $table,
      'c_firstname',
      [
        'type' => Table::TYPE_TEXT,
        'nullable' => true,
        'comment' => 'First Name'
      ]
    );
    $setup->getConnection()->addColumn(
      $table,
      'c_surname',
      [
        'type' => Table::TYPE_TEXT,
        'nullable' => true,
        'comment' => 'Surname'
      ]
    );

    $setup->getConnection()->addColumn(
      $table,
      'c_ip',
      [
        'type' => Table::TYPE_TEXT,
        'nullable' => true,
        'comment' => 'Customer Ip'
      ]
    );

    $setup->endSetup();
  }
}