<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mmegamenu\Setup;

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
         * Fill table magev2_protabs
         */
        $data = [
            ['Main Menu', 1, NULL, 1]
        ];

        $columns = ['title', 'menu_type', 'custom_class', 'status'];
        $setup->getConnection()->insertArray($setup->getTable('mgs_megamenu_parent'), $columns, $data);
    }
}
