<?php

namespace MGS\Fbuilder\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Config;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var
     */
    private $eavSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        Config $eavConfig
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $this->upgradeVersionOneZeroThree($setup);
        }
        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $this->upgradeVersionOneZeroFour($setup);
        }
        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $this->upgradeVersionOneZeroFive($setup);
        }
        if (version_compare($context->getVersion(), '1.0.6', '<')) {
            $this->upgradeVersionOneZeroSix($setup);
        }
        $setup->endSetup();
    }

    
    /* Version 1.0.3 */
    public function upgradeVersionOneZeroThree(ModuleDataSetupInterface $setup)
    {
        /* Create customer attribute fbuilder_available_pages for front-end builder*/
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(Customer::ENTITY, 'fbuilder_available_pages', [
            'type' => 'text',
            'label' => 'Builder Available Pages  ',
            'input' => 'multiselect',
            'required' => false,
            'visible' => true,
            'source' => 'MGS\Fbuilder\Model\Entity\Attribute\Source\AvailablePages',
            'user_defined' => false,
            'is_user_defined' => false,
            'sort_order' => 1000,
            'is_used_in_grid' => false,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'is_searchable_in_grid' => false,
            'position' => 1000,
            'system' => 0,
        ]);

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'fbuilder_available_pages')
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer'],
            ]);

        $attribute->save();
    }

    /* Version 1.0.4 */
    public function upgradeVersionOneZeroFour(ModuleDataSetupInterface $setup)
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $customerSetup->updateAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'is_fbuilder_account',
            'frontend_input',
            'boolean'
        );
        $customerSetup->updateAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'is_fbuilder_account',
            'source_model',
            ''
        );
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'is_fbuilder_account');
        $attribute->addData([
                'used_in_forms' => ['adminhtml_customer'],
            ]);
        $attribute->save();
    }
    
    /* Version 1.0.5 */
    public function upgradeVersionOneZeroFive(ModuleDataSetupInterface $setup)
    {
        /* Create customer attribute fbuilder_can_edit_product for front-end builder*/
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(Customer::ENTITY, 'fbuilder_can_edit_product', [
            'type' => 'int',
            'label' => 'Can Edit Product Description',
            'input' => 'boolean',
            'required' => false,
            'visible' => true,
            'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'user_defined' => false,
            'is_user_defined' => false,
            'sort_order' => 1000,
            'is_used_in_grid' => false,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'is_searchable_in_grid' => false,
            'position' => 1000,
            'default' => 0,
            'system' => 0,
        ]);

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'fbuilder_can_edit_product')
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer'],
            ]);

        $attribute->save();
    }
    
    /* Version 1.0.6 */
    public function upgradeVersionOneZeroSix(ModuleDataSetupInterface $setup)
    {
        /* Create customer attribute fbuilder_can_edit_category for front-end builder*/
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(Customer::ENTITY, 'fbuilder_can_edit_category', [
            'type' => 'int',
            'label' => 'Can Edit Category Description',
            'input' => 'boolean',
            'required' => false,
            'visible' => true,
            'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'user_defined' => false,
            'is_user_defined' => false,
            'sort_order' => 1000,
            'is_used_in_grid' => false,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'is_searchable_in_grid' => false,
            'position' => 1000,
            'default' => 0,
            'system' => 0,
        ]);

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'fbuilder_can_edit_category')
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer'],
            ]);

        $attribute->save();
    }
}
