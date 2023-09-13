<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Mmegamenu\Setup;

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
         * Create table 'mgs_megamenu_parent'
         */
        
		$table = $installer->getConnection()->newTable(
            $installer->getTable('mgs_megamenu_parent')
        )->addColumn(
            'parent_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Parent Id'
        )->addColumn(
            'title',
            Table::TYPE_TEXT,
            255,
            [],
            'Title'
        )->addColumn(
            'menu_type',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => 1],
            'Menu types'
        )->addColumn(
            'custom_class',
            Table::TYPE_TEXT,
            255,
            [],
            'Custom class'
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => 1],
            'Status'
        );
		
		$installer->getConnection()->createTable($table);

        /**
         * Create table 'mgs_megamenu'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mgs_megamenu')
        )->addColumn(
            'megamenu_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Megamenu Id'
        )->addColumn(
            'title',
            Table::TYPE_TEXT,
            255,
            [],
            'Title'
        )->addColumn(
            'menu_type',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => 1],
            'Menu types'
        )->addColumn(
            'url',
            Table::TYPE_TEXT,
            255,
            [],
            'Item url'
        )->addColumn(
            'category_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => 0],
            'Category Id'
        )->addColumn(
            'sub_category_ids',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Category Ids'
        )->addColumn(
            'position',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => 0],
            'Position'
        )->addColumn(
            'columns',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => 0],
            'Columns'
        )->addColumn(
            'align_menu',
            Table::TYPE_TEXT,
            255,
            [],
            'Align of menu item'
        )->addColumn(
            'align_dropdown',
            Table::TYPE_TEXT,
            255,
            [],
            'Align of dropdown'
        )->addColumn(
            'max_level',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => true],
            'Max level of sub category'
        )->addColumn(
            'dropdown_position',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => 1],
            'Dropdown Position'
        )->addColumn(
            'use_thumbnail',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => 2],
            'Thumbnail of category'
        )->addColumn(
            'special_class',
            Table::TYPE_TEXT,
            255,
            [],
            'Custom class'
        )->addColumn(
            'static_content',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Static content'
        )->addColumn(
            'top_content',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Top content'
        )->addColumn(
            'bottom_content',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Bottom content'
        )->addColumn(
            'left_content',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Left content'
        )->addColumn(
            'left_col',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => true],
            'columns of left'
        )->addColumn(
            'right_content',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Right content'
        )->addColumn(
            'right_col',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => true],
            'columns of right'
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => 1],
            'Status'
        )->addColumn(
            'from_date',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true],
            'From date'
        )->addColumn(
            'to_date',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true],
            'To date'
        )->addColumn(
            'parent_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => 0],
            'Parent Id'
        )->addColumn(
            'store',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => 0],
            'Store Id'
        )->addColumn(
            'html_label',
            Table::TYPE_TEXT,
            255,
            [],
            'Html label'
        );

        $installer->getConnection()->createTable($table);
		
		/**
         * Create table 'mgs_megamenu_store'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mgs_megamenu_store')
        )->addColumn(
            'megamenu_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'primary' => true],
            'Megamenu ID'
        )->addColumn(
            'store_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store ID'
        );
		
        $installer->getConnection()->createTable($table);

        $installer->endSetup();

    }
}
