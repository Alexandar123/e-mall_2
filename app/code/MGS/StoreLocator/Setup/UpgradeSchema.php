<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\StoreLocator\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface {

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();
        
        if (version_compare($context->getVersion(), '0.0.1') < 0) {
            $setup->getConnection()->addColumn(
                    $setup->getTable('store_locator'), 'lat', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 50,
                'nullable' => true,
                'default' => '',
                'comment' => 'Latitude'
                    ]
            );
            $setup->getConnection()->addColumn(
                    $setup->getTable('store_locator'), 'lng', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 50,
                'nullable' => true,
                'default' => '',
                'comment' => 'Longitude'
                    ]
            );
        }
        
        if (version_compare($context->getVersion(), '0.0.2') < 0) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable('store_locator_store'))
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Store Locator Store Id'
                )
                ->addColumn(
                    'storelocator_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Store Locator Id'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Store Id'
                )
                ->addIndex(
                    $setup->getIdxName('store_locator_store', ['storelocator_id']),
                    ['storelocator_id']
                )
                ->addIndex(
                    $setup->getIdxName('store_locator_store', ['store_id']),
                    ['store_id']
                )->addForeignKey(
                    $setup->getFkName('store_locator_store', 'storelocator_id', 'store_locator', 'id'),
                    'storelocator_id',
                    $setup->getTable('store_locator'),
                    'id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )->addForeignKey(
                    $setup->getFkName('store_locator_store', 'store_id', 'store', 'store_id'),
                    'store_id',
                    $setup->getTable('store'),
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )->setComment('Store Locators Store');
            $setup->getConnection()->createTable($table);
        }
		if (version_compare($context->getVersion(), '1.0.0') <= 0) {
            $setup->getConnection()->addColumn(
                    $setup->getTable('store_locator'), 'store_url', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 250,
                'nullable' => true,
                'default' => '',
                'comment' => 'Store Url'
                    ]
            );
			 $setup->getConnection()->addColumn(
                    $setup->getTable('store_locator'), 'logo_store', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 250,
                'nullable' => true,
                'default' => '',
                'comment' => 'Store Logo'
                    ]
            );
        }

        if(version_compare($context->getVersion(), '1.0.2') < 0) {
            $setup->getConnection()->addColumn(
            $setup->getTable('store_locator'), 'route', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 250,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'Route'
                ]
            );
        }

        $setup->endSetup();
    }

}
