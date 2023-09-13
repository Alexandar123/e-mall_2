<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Lookbook\Setup;

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
         * Create table 'mgs_lookbook'
         */
        
		$table = $installer->getConnection()->newTable(
            $installer->getTable('mgs_lookbook')
        )->addColumn(
            'lookbook_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Lookbook Id'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => ''],
            'Name'
        )->addColumn(
            'image',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => ''],
            'Image'
        )->addColumn(
            'pins',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Pins info'
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => 1],
            'Status'
        );
		
		$installer->getConnection()->createTable($table);

        /**
         * Create table 'mgs_lookbook_slide'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mgs_lookbook_slide')
        )->addColumn(
            'slide_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Slider Id'
        )->addColumn(
            'title',
            Table::TYPE_TEXT,
            255,
            [],
            'Title'
        )->addColumn(
            'custom_class',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => ''],
            'Custom Class'
        )->addColumn(
            'auto_play',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => 1],
            'Autoplay'
        )->addColumn(
            'auto_play_timeout',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => ''],
            'Autoplay Timeout'
        )->addColumn(
            'stop_auto',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => 1],
            'Stop autopaly when mouseover'
        )->addColumn(
            'navigation',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => 1],
            'Show Navigation'
        )->addColumn(
            'pagination',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => 1],
            'Show Pagination'
        )->addColumn(
            'loop',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => 1],
            'Loop'
        )->addColumn(
            'next_image',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => ''],
            'Next Icon'
        )->addColumn(
            'prev_image',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => ''],
            'Previous Icon'
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => 1],
            'Status'
        );

        $installer->getConnection()->createTable($table);
		
		/**
         * Create table 'mgs_lookbook_slide_items'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mgs_lookbook_slide_items')
        )->addColumn(
            'item_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Item Id'
        )->addColumn(
            'slide_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'primary' => true],
            'Slider ID'
        )->addColumn(
            'lookbook_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'primary' => true],
            'Lookbook ID'
        )->addColumn(
            'position',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => '0'],
            'Position'
        );
		
        $installer->getConnection()->createTable($table);

        $installer->endSetup();

    }
}
