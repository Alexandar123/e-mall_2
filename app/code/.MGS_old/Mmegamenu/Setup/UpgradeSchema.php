<?php
/**
* Copyright © 2016 MGS-THEMES. All rights reserved.
*/

namespace MGS\Mmegamenu\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.0.1', '<=')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('mgs_megamenu_cache')
            )->addColumn(
                'cache_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Cache ID'
            )->addColumn(
                'parent_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 0],
                'Parent Id'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store ID'
            )->addColumn(
                'html',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '10M',
                ['unsigned' => true],
                'Menu Html'
            )->addColumn(
                'creation_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Menu Creation Time'
            )->setComment(
                'Menu Log'
            );
            $installer->getConnection()->createTable($table);
        }

        $intaller = $setup;
        $intaller->startSetup();
        if (version_compare($context->getVersion(), '1.0.5') < 0 ) {
             $table = $intaller->getConnection()
                ->newTable($intaller->getTable('mgs_megamenu_update'))
                ->addColumn(
                    'megamenu_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => false],
                    'Post Id'
                )
                ->addColumn(
                    'scope_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable'=>false],
                    'Scope Id'
                )
                ->addColumn(
                    'field',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable'=>false],
                    'Field'
                )
                ->addColumn(
                    'value',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable'=>false],
                    'Value'
                )
                ->addForeignKey(
                    $intaller->getFkName('mgs_megamenu_update', 'megamenu_id', 'mgs_megamenu', 'megamenu_id'),
                    'megamenu_id',
                    $intaller->getTable('mgs_megamenu'),
                    'megamenu_id',
                    Table::ACTION_CASCADE
                )
                
                ->setComment('Megamenu Update');
            $intaller->getConnection()->createTable($table);
        $installer->endSetup();
    }
}
}