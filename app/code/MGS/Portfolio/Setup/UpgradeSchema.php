<?php
namespace MGS\Portfolio\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
class UpgradeSchema implements  UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup,
                            ModuleContextInterface $context){

        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'mgs_portfolio_item_store'
         */
        if(version_compare($context->getVersion(), '1.0.0.1', '>=')){
            $table = $installer->getConnection()->newTable(
                $installer->getTable('mgs_portfolio_item_store')
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Portfolio Store Id'
            )->addColumn(
                'portfolio_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Portfolio Id'
            )->addColumn(
                'store_id',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Store Id'
            );

            $installer->getConnection()->createTable($table);
        }

        if(version_compare($context->getVersion(), '1.0.0.2', '>=')){
            $table = $installer->getConnection()->newTable(
                $installer->getTable('mgs_portfolio_item_multi_store')
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Portfolio Store Id'
            )->addColumn(
                'portfolio_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Portfolio Id'
            )->addColumn(
                'store_id',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Store Id'
            );

            $installer->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '1.0.4') < 0 ) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mgs_portfolio_items_update'))
                ->addColumn(
                    'portfolio_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => false],
                    'Portfolio Id'
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
                    $installer->getFkName('mgs_portfolio_multi_store', 'portfolio_id', 'mgs_portfolio_items', 'portfolio_id'),
                    'portfolio_id',
                    $installer->getTable('mgs_portfolio_items'),
                    'portfolio_id',
                    Table::ACTION_CASCADE
                )

                ->setComment('Portfolio Update');

            $installer->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '1.0.0.5') < 0 ) {
            if ($installer->getConnection()->tableColumnExists('mgs_portfolio_items', 'base_image')){
                $definition = [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'size' => 2048,
                    'comment' => 'Title'
                ];
                $installer->getConnection()->modifyColumn(
                    $setup->getTable('mgs_portfolio_items'),
                    'base_image',
                    $definition
                );
            }
            $installer->getConnection()->createTable($table);
        }


        $installer->endSetup();
    }
}
