<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Lookbook\Block\Adminhtml\Edit\Tab;

/**
 * Sitemap edit form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
	/**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
	
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
		\Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
		$this->_objectManager = $objectManager;
        parent::__construct($context, $registry, $formFactory, $data);
    }
	
	protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('lookbook_lookbook');

        $form = $this->_formFactory->create();

        //$form->setHtmlIdPrefix('lookbook_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Lookbook Information')]);
		
		$data = $model->getData();

        if ($model->getId()) {
            $fieldset->addField('lookbook_id', 'hidden', ['name' => 'lookbook_id']);
        }
		
		
        $fieldset->addField(
            'name',
            'text',
            [
                'label' => __('Lookbook Name'),
                'name' => 'name',
                'required' => true,
                'value' => $model->getName()
            ]
        );
		
		
		$fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'name' => 'status',
                'required' => false,
                'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
            ]
        );
		
		$fieldset->addType('lookbookimage','\MGS\Lookbook\Block\Adminhtml\Edit\Tab\Image');
		$fieldset->addField('image', 'lookbookimage', 
			[
			  'label'     => __('Image'),
			  'name'      => 'image',
			  'required'  => true,       
			]
		);
		
		$fieldset->addField(
            'pins',
            'hidden',
            [
                'name' => 'pins'
            ]
        );
		
		
        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }

	/**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('General Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('General Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
