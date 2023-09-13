<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\StoreLocator\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'newsletter_subscriber'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('store_locator'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Store Locator Id'
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                250,
                ['nullable' => false],
                'Store Name'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Created At'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Updated At'
            )
            ->addColumn(
                'image',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                250,
                [],
                'Store Image'
            )
            ->addColumn(
                'email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                250,
                [],
                'Store Email'
            )
            ->addColumn(
                'phone_number',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                [],
                'Store Phone Number'
            )
            ->addColumn(
                'fax',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                [],
                'Store Fax'
            )
            ->addColumn(
                'website',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                250,
                [],
                'Store website'
            )
            ->addColumn(
                'street_address',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'Address'
            )
            ->addColumn(
                'country',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                250,
                [],
                'Country'
            )
            ->addColumn(
                'state',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                250,
                [],
                'State'
            )
            ->addColumn(
                'city',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                250,
                [],
                'City'
            )
            ->addColumn(
                'zipcode',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                250,
                [],
                'Zip Code'
            )
            ->addColumn(
                'description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'Description'
            )
            ->addColumn(
                'trading_hours',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'Trading Hours'
            )
            ->addColumn(
                'radius',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                64,
                [],
                'Radius'
            )
            ->addColumn(
                'zoom_level',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                64,
                [],
                'Zoom Level'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => '0'],
                'Store Status'
            )
            ->addIndex(
                $installer->getIdxName(
                    'store_locator',
                    ['name'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['name'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->setComment('Store Locators');
        $installer->getConnection()->createTable($table);
        
        
        

        $installer->endSetup();

    }
}
