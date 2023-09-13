<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Protabs\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'mgs_protabs'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mgs_protabs')
        )->addColumn(
            'tab_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Protabs Id'
        )->addColumn(
            'title',
            Table::TYPE_TEXT,
            255,
            [],
            'Title'
        )->addColumn(
            'tab_type',
            Table::TYPE_TEXT,
            255,
            [],
            'Type'
        )->addColumn(
            'value',
            Table::TYPE_TEXT,
            255,
            [],
            'Value'
        )->addColumn(
            'position',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Position'
        )->addColumn(
            'scope',
            Table::TYPE_TEXT,
            8,
            [],
            'Scope'
        )->addColumn(
            'scope_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => 0],
            'Scope Id'
        );

        $installer->getConnection()->createTable($table);

        $installer->endSetup();

    }
}
