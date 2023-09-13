<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Protabs\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
	/**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /**
         * Fill table mgs_protabs
         */
        $data = [
            ['Details', 'attribute', 'description', 1, 'default', 0],
            ['More Information', 'product.attributes', '', 2, 'default', 0],
            ['Reviews', 'reviews.tab', '', 3, 'default', 0],
        ];

        $columns = ['title', 'tab_type', 'value', 'position', 'scope', 'scope_id'];
        $setup->getConnection()->insertArray($setup->getTable('mgs_protabs'), $columns, $data);
    }
}
