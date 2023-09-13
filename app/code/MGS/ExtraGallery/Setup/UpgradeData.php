<?php
/**
* Copyright © 2016 SW-THEMES. All rights reserved.
*/

namespace MGS\ExtraGallery\Setup;

use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
	private $eavSetupFactory;
 
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
		$this->eavSetupFactory = $eavSetupFactory;
    }
    
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
		
		if (version_compare($context->getVersion(), '2.1.0', '<=')) {
            // set new resource model paths
            /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
            
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
			$productTypes = [
				\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
				\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
				\Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE,
				\Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
				\Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE,
				\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE,
			];
			$productTypes = join(',', $productTypes);
			
			$eavSetup->addAttribute(
				\Magento\Catalog\Model\Product::ENTITY,
				'extragallery_glr_type',
				[
					'group' => 'MGS Theme Settings',
					'sort_order' => 150,
					'type' => 'int',
					'backend' => '',
					'frontend' => '',
					'label' => 'Product Gallery type',
					'input' => 'select',
					'class' => '',
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
					'source' => 'MGS\ExtraGallery\Model\Product\Attribute\Source\GalleryType',
					'visible' => true,
					'required' => false,
					'user_defined' => true,
					'searchable' => false,
					'filterable' => false,
					'comparable' => false,
					'visible_in_advanced_search' => false,
					'visible_on_front' => false,
					'used_in_product_listing' => true,
					'unique' => false,
					'apply_to' => $productTypes,
					'is_used_in_grid' => false,
					'is_visible_in_grid' => false,
					'is_filterable_in_grid' => false,
				]
			);
        }
        
        $setup->endSetup();
    }
}
