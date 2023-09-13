<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mmegamenu\Block\Adminhtml\Edit;

/**
 * Sitemap edit form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
	/**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;
	
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
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('mmegamenu__form');
        $this->setTitle(__('Mmegamenu Information'));
    }

    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('mmegamenu_parents');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getUrl('adminhtml/mmegamenu/saveparent'), 'method' => 'post', 'enctype' => 'multipart/form-data']]
        );

        $fieldset = $form->addFieldset('add_mmegamenu_form', ['legend' => __('Menu Information')]);

        if ($model->getId()) {
            $fieldset->addField('parent_id', 'hidden', ['name' => 'parent_id']);
        }

        $fieldset->addField(
            'title',
            'text',
            [
                'label' => __('Title'),
                'name' => 'title',
                'required' => true,
                'value' => $model->getTitle()
            ]
        );
		
		if($this->getRequest()->getParam('id')==1){
			$fieldset->addField(
				'menu_type',
				'select',
				[
					'label' => __('Menu Type'),
					'name' => 'menu_type',
					'required' => false,
					'disabled'  => true,
					'options' => ['1' => __('Horizontal'), '2' => __('Vertical')]
				]
			);
		}else{
			$fieldset->addField(
				'menu_type',
				'select',
				[
					'label' => __('Menu Type'),
					'name' => 'menu_type',
					'required' => false,
					'options' => ['1' => __('Horizontal'), '2' => __('Vertical')]
				]
			);
		}
		
		$fieldset->addField(
            'custom_class',
            'text',
            [
                'label' => __('Custom Class'),
                'name' => 'custom_class',
                'value' => $model->getCustomClass()
            ]
        );
        
		if($this->getRequest()->getParam('id')==1){
			$fieldset->addField(
				'status',
				'select',
				[
					'label' => __('Status'),
					'name' => 'status',
					'required' => false,
					'disabled'  => true,
					'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
				]
			);
		}else{
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
		}
		

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
