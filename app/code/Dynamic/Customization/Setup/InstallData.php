<?php

namespace Dynamic\Customization\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class InstallData implements InstallDataInterface
{

    private $eavSetupFactory;

    /**
     * Constructor
     *
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'bottom_description',
            [
                'type' => 'text',
                'label' => 'Description',
                'input' => 'textarea',
                'required' => false,
                'sort_order' => 5,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'group' => 'General Information',
            ]
        );

        $attributeSet = $eavSetup->getDefaultAttributeSetId(\Magento\Catalog\Model\Category::ENTITY);
        $attributeGroup = $eavSetup->getDefaultAttributeGroupId(\Magento\Catalog\Model\Category::ENTITY);
        $eavSetup->addAttributeToGroup(
            \Magento\Catalog\Model\Category::ENTITY,
            $attributeSet,
            $attributeGroup,
            $eavSetup->getAttributeId(\Magento\Catalog\Model\Category::ENTITY, 'bottom_description')
        );
    }
}
