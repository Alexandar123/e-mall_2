<?php
/**
 * Copyright © 2016 MGS-THEMES. All rights reserved.
 */

namespace MGS\Fbuilder\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.0', '<=')) {
            $connection = $setup->getConnection();
            
            $sectionTable = $setup->getTable('mgs_fbuilder_section');
            
            if ($connection->isTableExists($sectionTable) == true) {
                $connection->addColumn($sectionTable, 'no_padding', [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'after' => 'fullwidth',
                    'comment' => 'Is No Padding'
                ]);
            }
        }
        
        if (version_compare($context->getVersion(), '1.0.1', '<=')) {
            $connection = $setup->getConnection();
            
            $sectionTable = $setup->getTable('mgs_fbuilder_section');
            
            if ($connection->isTableExists($sectionTable) == true) {
                $connection->addColumn($sectionTable, 'hide_desktop', [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'after' => 'no_padding',
                    'comment' => 'Hide on Desktop'
                ]);
                
                $connection->addColumn($sectionTable, 'hide_tablet', [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'after' => 'hide_desktop',
                    'comment' => 'Hide on Tablet'
                ]);
                
                $connection->addColumn($sectionTable, 'hide_mobile', [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'after' => 'hide_tablet',
                    'comment' => 'Hide on Mobile'
                ]);
            }
            
            $childTable = $setup->getTable('mgs_fbuilder_child');
            
            if ($connection->isTableExists($childTable) == true) {
                $connection->addColumn($childTable, 'hide_desktop', [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'after' => 'background_cover',
                    'comment' => 'Hide on Desktop'
                ]);
                
                $connection->addColumn($childTable, 'hide_tablet', [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'after' => 'hide_desktop',
                    'comment' => 'Hide on Tablet'
                ]);
                
                $connection->addColumn($childTable, 'hide_mobile', [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'after' => 'hide_tablet',
                    'comment' => 'Hide on Mobile'
                ]);
            }
        }
        
        if (version_compare($context->getVersion(), '1.0.5', '<=')) {
            $connection = $setup->getConnection();
            
            $sectionTable = $setup->getTable('mgs_fbuilder_section');
            
            if ($connection->isTableExists($sectionTable) == true) {
                $connection->addColumn($sectionTable, 'product_id', [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'after' => 'page_id',
                    'comment' => 'Product ID'
                ]);
                
                $connection->addColumn($sectionTable, 'page_type', [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'after' => 'product_id',
                    'comment' => 'Page Type'
                ]);
                
                $setup->getConnection()->dropForeignKey(
                    $sectionTable,
                    $setup->getFkName(
                        'mgs_fbuilder_section',
                        'page_id',
                        'cms_page',
                        'page_id'
                    )
                );
                
                $removeAutoIncrementQuery = "ALTER TABLE ".$sectionTable." CHANGE `block_id` `block_id` INT(10) UNSIGNED NOT NULL COMMENT 'Block Id'";
                $setup->getConnection()->query($removeAutoIncrementQuery);
                
                $setup->getConnection()->dropIndex($sectionTable, 'MGS_FBUILDER_SECTION_PAGE_ID');
                $setup->getConnection()->dropIndex($sectionTable, 'primary');
                
                $addPrimaryKeyQuery = "ALTER TABLE ".$sectionTable." ADD PRIMARY KEY(`block_id`)";
                $setup->getConnection()->query($addPrimaryKeyQuery);
                
                $addAutoIncrementQuery = "ALTER TABLE ".$sectionTable." CHANGE `block_id` `block_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Block Id'";
                $setup->getConnection()->query($addAutoIncrementQuery);
                
                $addNullQuery = "ALTER TABLE ".$sectionTable." CHANGE `page_id` `page_id` SMALLINT(6) NULL COMMENT 'Page ID'";
                $setup->getConnection()->query($addNullQuery);
                
                $updateQuery = "UPDATE ".$sectionTable." SET page_type='cms' where page_type IS NULL";
                $setup->getConnection()->query($updateQuery);
                
            }
            
            $childTable = $setup->getTable('mgs_fbuilder_child');
            
            if ($connection->isTableExists($childTable) == true) {
                $connection->addColumn($childTable, 'product_id', [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'after' => 'page_id',
                    'comment' => 'Product ID'
                ]);
                
                $connection->addColumn($childTable, 'page_type', [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'after' => 'product_id',
                    'comment' => 'Page Type'
                ]);
                
                $setup->getConnection()->dropForeignKey(
                    $childTable,
                    $setup->getFkName(
                        'mgs_fbuilder_child',
                        'page_id',
                        'cms_page',
                        'page_id'
                    )
                );
                
                $removeAutoIncrementQuery = "ALTER TABLE ".$childTable." CHANGE `child_id` `child_id` INT(10) UNSIGNED NOT NULL COMMENT 'Child Id'";
                $setup->getConnection()->query($removeAutoIncrementQuery);
                
                $setup->getConnection()->dropIndex($childTable, 'MGS_FBUILDER_CHILD_PAGE_ID');
                $setup->getConnection()->dropIndex($childTable, 'primary');
                
                $addPrimaryKeyQuery = "ALTER TABLE ".$childTable." ADD PRIMARY KEY(`child_id`)";
                $setup->getConnection()->query($addPrimaryKeyQuery);
                
                $addAutoIncrementQuery = "ALTER TABLE ".$childTable." CHANGE `child_id` `child_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Child Id'";
                $setup->getConnection()->query($addAutoIncrementQuery);
                
                $addNullQuery = "ALTER TABLE ".$childTable." CHANGE `page_id` `page_id` SMALLINT(6) NULL COMMENT 'Page ID'";
                $setup->getConnection()->query($addNullQuery);
                
                $updateQuery = "UPDATE ".$childTable." SET page_type='cms' where page_type IS NULL";
                $setup->getConnection()->query($updateQuery);
            }
        }
        
        if (version_compare($context->getVersion(), '1.0.6', '<=')) {
            $connection = $setup->getConnection();
            
            $blockTable = $setup->getTable('mgs_fbuilder_child');
            
            if ($connection->isTableExists($blockTable) == true) {
                $connection->addColumn($blockTable, 'col_tablet', [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'after' => 'col',
                    'comment' => 'Column for Tablet'
                ]);
                
                $connection->addColumn($blockTable, 'col_mobile', [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'after' => 'col_tablet',
                    'comment' => 'Column for Mobile'
                ]);
            }
        }
        
        if (version_compare($context->getVersion(), '1.0.7', '<=')) {
            $connection = $setup->getConnection();
            
            $productTextTable = $setup->getTable('catalog_product_entity_text');
            if ($connection->isTableExists($productTextTable) == true) {
                $updateQuery = "ALTER TABLE ".$productTextTable." CHANGE `value` `value` LONGTEXT NULL DEFAULT NULL COMMENT 'Value'";
                $setup->getConnection()->query($updateQuery);
            }
            
            $categoryTextTable = $setup->getTable('catalog_category_entity_text');
            if ($connection->isTableExists($categoryTextTable) == true) {
                $updateQuery = "ALTER TABLE ".$categoryTextTable." CHANGE `value` `value` LONGTEXT NULL DEFAULT NULL COMMENT 'Value'";
                $setup->getConnection()->query($updateQuery);
            }
        }
        
        $setup->endSetup();
    }
}
