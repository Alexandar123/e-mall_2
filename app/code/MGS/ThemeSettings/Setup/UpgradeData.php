<?php
/**
* Copyright © 2016 SW-THEMES. All rights reserved.
*/

namespace MGS\ThemeSettings\Setup;

use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;
	private $eavSetupFactory;

    /**
     * Init
     *
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(CategorySetupFactory $categorySetupFactory, EavSetupFactory $eavSetupFactory)
    {
        $this->categorySetupFactory = $categorySetupFactory;
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
        if (version_compare($context->getVersion(), '1.0.0', '<=')) {
            // set new resource model paths
            /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */

            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
            $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
            $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);

			/* MGS Theme Settings section */
            $settingAttributes = [
				'full_width' => [
                    'type' => 'int',
                    'label' => 'Full Width Layout',
                    'input' => 'select',
                    'required' => false,
                    'sort_order' => 1,
					'default' => '0',
					'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
					'used_in_product_listing' => true,
                    'group' => 'MGS Theme Settings'
                ],
                'image_dimention_grid' => [
                    'type' => 'varchar',
                    'label' => 'Product Image Dimention (Grid Mode)',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 10,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'note' => 'WidthxHeight. Ex: 240x300',
					'used_in_product_listing' => true,
                    'group' => 'MGS Theme Settings'
                ],
				'image_dimention_list' => [
                    'type' => 'varchar',
                    'label' => 'Product Image Dimention (List Mode)',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 20,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
					'note' => 'WidthxHeight. Ex: 240x300',
					'used_in_product_listing' => true,
                    'group' => 'MGS Theme Settings'
                ],
				'per_row' => [
                    'type' => 'varchar',
					'label' => 'Number of Product per row (Desktop)',
					'input' => 'select',
					'source' => 'MGS\ThemeSettings\Model\Category\Attribute\Source\Perrow',
					'required' => false,
					'sort_order' => 30,
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
					'used_in_product_listing' => true,
					'group' => 'MGS Theme Settings',
                ],
				'per_row_tablet' => [
                    'type' => 'varchar',
					'label' => 'Number of Product per row (Tablet)',
					'input' => 'select',
					'source' => 'MGS\ThemeSettings\Model\Category\Attribute\Source\Perrow',
					'required' => false,
					'sort_order' => 40,
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
					'used_in_product_listing' => true,
					'group' => 'MGS Theme Settings',
                ],
				'per_row_mobile' => [
                    'type' => 'varchar',
					'label' => 'Number of Product per row (Mobile)',
					'input' => 'select',
					'source' => 'MGS\ThemeSettings\Model\Category\Attribute\Source\Perrow',
					'required' => false,
					'sort_order' => 50,
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
					'used_in_product_listing' => true,
					'group' => 'MGS Theme Settings',
                ]
            ];

            foreach($settingAttributes as $item => $data) {
                $categorySetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, $item, $data);
            }

            $idg =  $categorySetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'MGS Theme Settings');

            foreach($settingAttributes as $item => $data) {
                $categorySetup->addAttributeToGroup(
                    $entityTypeId,
                    $attributeSetId,
                    $idg,
                    $item,
                    $data['sort_order']
                );
            }

			/* MGS Landing section */
			$landingAttributes = [
				'is_landing' => [
					'type' => 'int',
					'label' => 'Is Landing Page',
					'input' => 'select',
					'required' => false,
					'sort_order' => 10,
					'default' => '0',
					'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
					'used_in_product_listing' => true,
					'group' => 'MGS Category Landing'
				],
				'cate_landing_type' => [
					'type' => 'varchar',
					'label' => 'Landing Template',
					'input' => 'select',
					'source' => 'MGS\ThemeSettings\Model\Category\Attribute\Source\Template',
					'required' => false,
					'sort_order' => 30,
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
					'used_in_product_listing' => true,
					'group' => 'MGS Category Landing',
				],
				'landing_per_row' => [
                    'type' => 'varchar',
					'label' => 'Number of Item per row (Desktop)',
					'input' => 'select',
					'source' => 'MGS\ThemeSettings\Model\Category\Attribute\Source\Perrow',
					'required' => false,
					'sort_order' => 40,
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
					'used_in_product_listing' => true,
					'group' => 'MGS Category Landing',
                ],
				'landing_per_row_tablet' => [
                    'type' => 'varchar',
					'label' => 'Number of Item per row (Tablet)',
					'input' => 'select',
					'source' => 'MGS\ThemeSettings\Model\Category\Attribute\Source\Perrow',
					'required' => false,
					'sort_order' => 50,
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
					'used_in_product_listing' => true,
					'group' => 'MGS Category Landing',
                ],
				'landing_per_row_mobile' => [
                    'type' => 'varchar',
					'label' => 'Number of Item per row (Mobile)',
					'input' => 'select',
					'source' => 'MGS\ThemeSettings\Model\Category\Attribute\Source\Perrow',
					'required' => false,
					'sort_order' => 60,
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
					'used_in_product_listing' => true,
					'group' => 'MGS Category Landing',
                ],
				'hide_description' => [
					'type' => 'int',
					'label' => 'Show Description Text',
					'input' => 'select',
					'required' => false,
					'sort_order' => 70,
					'default' => '1',
					'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
					'used_in_product_listing' => true,
					'group' => 'MGS Category Landing'
				],
				'truncate_description' => [
                    'type' => 'varchar',
                    'label' => 'Summary character count',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 80,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
					'used_in_product_listing' => true,
                    'group' => 'MGS Category Landing'
                ]
            ];

            foreach($landingAttributes as $item => $data) {
                $categorySetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, $item, $data);
            }

            $idg =  $categorySetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'MGS Category Landing');

            foreach($landingAttributes as $item => $data) {
                $categorySetup->addAttributeToGroup(
                    $entityTypeId,
                    $attributeSetId,
                    $idg,
                    $item,
                    $data['sort_order']
                );
            }
        }

		if (version_compare($context->getVersion(), '2.0.0', '<=')) {
            // set new resource model paths
            /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
			$productTypes = [
				\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
				\Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE,
				\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
				\Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
				\Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE,
				\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE,
			];
			$productTypes = join(',', $productTypes);

			$eavSetup->addAttribute(
				\Magento\Catalog\Model\Product::ENTITY,
				'mgs_template',
				[
					'group' => 'MGS Theme Settings',
					'sort_order' => 150,
					'type' => 'int',
					'backend' => '',
					'frontend' => '',
					'label' => 'Template',
					'input' => 'select',
					'class' => '',
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
					'source' => 'MGS\ThemeSettings\Model\Product\Attribute\Source\Template',
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

			$eavSetup->addAttribute(
				\Magento\Catalog\Model\Product::ENTITY,
				'mgs_image_dimention',
				[
					'group' => 'MGS Theme Settings',
					'sort_order' => 160,
					'type' => 'varchar',
					'backend' => '',
					'frontend' => '',
					'label' => 'Product Image Dimention (Main Image)',
					'input' => 'text',
					'class' => '',
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
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
					'note' => 'WidthxHeight. Ex: 240x300'
				]
			);

			$eavSetup->addAttribute(
				\Magento\Catalog\Model\Product::ENTITY,
				'mgs_image_dimention_more_view',
				[
					'group' => 'MGS Theme Settings',
					'sort_order' => 170,
					'type' => 'varchar',
					'backend' => '',
					'frontend' => '',
					'label' => 'Product Image Dimention (More View)',
					'input' => 'text',
					'class' => '',
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
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
					'note' => 'WidthxHeight. Ex: 88x110'
				]
			);

			$eavSetup->addAttribute(
				\Magento\Catalog\Model\Product::ENTITY,
				'mgs_j360',
				[
					'group' => 'MGS Theme Settings',
					'sort_order' => 200,
					'default' => '0',
					'type' => 'int',
					'backend' => '',
					'frontend' => '',
					'label' => '360 Degrees Image View',
					'input' => 'boolean',
					'class' => '',
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
					'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
					'visible' => true,
					'required' => false,
					'user_defined' => true,
					'searchable' => false,
					'filterable' => false,
					'comparable' => false,
					'used_in_product_listing' => true,
					'unique' => false,
					'apply_to' => $productTypes,
					'is_used_in_grid' => false,
					'is_visible_in_grid' => false,
					'is_filterable_in_grid' => false
				]
			);
        }

        if (version_compare($context->getVersion(), '2.0.1', '<=')) {
            // set new resource model paths
            /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $productTypes = [
                \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE,
                \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
                \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
                \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE,
                \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE,
            ];
            $productTypes = join(',', $productTypes);
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'mgs_arimage',
                [
                    'group' => 'MGS Theme Settings',
                    'sort_order' => 210,
                    'default' => '0',
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => '3D Image View',
                    'input' => 'boolean',
                    'class' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => $productTypes,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.0.2', '<=')) {
            $eavSetup = $this->eavSetupFactory->create();
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'thumb_ar_image',
                [
                    'type' => 'varchar',
                    'label' => '3D Thumbnail',
                    'input' => 'media_image',
                    'required' => false,
                    'sort_order' => 1100,
                    'frontend' => \Magento\Catalog\Model\Product\Attribute\Frontend\Image::class,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'used_in_product_listing' => true,
                    'user_defined' => true,
                    'visible' => true,
                    'visible_on_front' => true
                ]
            );
            $arId = $eavSetup->getAttributeId(
                \Magento\Catalog\Model\Product::ENTITY,
                'thumb_ar_image'
            );
            $attributeSetId = $eavSetup->getDefaultAttributeSetId(\Magento\Catalog\Model\Product::ENTITY);
            $eavSetup->addAttributeToGroup(\Magento\Catalog\Model\Product::ENTITY, $attributeSetId, 'image-management', $arId, 1000);
        }
        $setup->endSetup();
    }
}
