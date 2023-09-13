<?php

namespace Dynamic\FTPImportExport\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Setup\CategorySetupFactory;

class InstallData implements InstallDataInterface
{

	private $eavSetupFactory;

	/**
	 * Category setup factory
	 *
	 * @var CategorySetupFactory
	 */
	private $categorySetupFactory;

	public function __construct(
		EavSetupFactory $eavSetupFactory,
		CategorySetupFactory $categorySetupFactory
	) {
		$this->eavSetupFactory = $eavSetupFactory;
		$this->categorySetupFactory = $categorySetupFactory;
	}

	public function install(
		ModuleDataSetupInterface $setup,
		ModuleContextInterface $context
	) {

		/** @var CategorySetup $categorySetup */

		$categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);

		$categorySetup->addAttribute(
			\Magento\Catalog\Model\Category::ENTITY,
			'dd_acclassif',
			[
				'group' => 'General Information',
				'label' => 'Text Unique ID of Product Category(acClassif)',
				'input' => 'text',
				'type' => 'varchar',
				'required' => false,
				'sort_order' => 100,
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
			]
		);

        $categorySetup->addAttribute(
			\Magento\Catalog\Model\Category::ENTITY,
			'dd_anqid',
			[
				'group' => 'General Information',
				'label' => 'Numeric Unique ID of Product Category(anQId)',
				'input' => 'text',
				'type' => 'varchar',
				'required' => false,
				'sort_order' => 110,
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
			]
		);
	}
}
