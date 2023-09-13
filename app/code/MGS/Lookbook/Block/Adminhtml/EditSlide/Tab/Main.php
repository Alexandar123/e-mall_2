<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Lookbook\Block\Adminhtml\EditSlide\Tab;

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
        $model = $this->_coreRegistry->registry('lookbook_slide');

        $form = $this->_formFactory->create();


        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Information')]);
		
		$data = $model->getData();
		
		if($model->getId()){
			$data['slider_status'] = $data['status'];
		}
			
        if ($model->getId()) {
            $fieldset->addField('slide_id', 'hidden', ['name' => 'slide_id']);
        }
		
		
        $fieldset->addField(
            'title',
            'text',
            [
                'label' => __('Slide Name'),
                'name' => 'title',
                'required' => true
            ]
        );
		
		$fieldset->addField(
            'custom_class',
            'text',
            [
                'label' => __('Custom class'),
                'name' => 'custom_class'
            ]
        );
		
		$fieldset->addField(
            'navigation',
            'select',
            [
                'label' => __('Show navigation'),
                'name' => 'navigation',
                'required' => false,
                'options' => ['0' => __('Use general config'), '1' => __('Yes'), '2'=> __('No')]
            ]
        );
		
		$fieldset->addField(
            'pagination',
            'select',
            [
                'label' => __('Show pagination'),
                'name' => 'pagination',
                'required' => false,
                'options' => ['0' => __('Use general config'), '1' => __('Yes'), '2'=> __('No')]
            ]
        );
		
		$fieldset->addField(
            'auto_play',
            'select',
            [
                'label' => __('Autoplay'),
                'name' => 'auto_play',
                'required' => false,
                'options' => ['0' => __('Use general config'), '1' => __('Yes'), '2'=> __('No')]
            ]
        );
		
		$fieldset->addField(
            'auto_play_timeout',
            'text',
            [
                'label' => __('Autoplay timeout'),
                'name' => 'auto_play_timeout',
				'after_element_html' => '<script>require(["jquery"], function(jQuery){(function($) {$("#auto_play_timeout").attr("placeholder", "'.__('Blank to use general config.').'")})(jQuery)})</script>',
            ]
        );
		
		$fieldset->addField(
            'stop_auto',
            'select',
            [
                'label' => __('Pause on mouse hover'),
                'name' => 'stop_auto',
                'required' => false,
                'options' => ['0' => __('Use general config'), '1' => __('Yes'), '2'=> __('No')]
            ]
        );
		
		$fieldset->addField(
            'loop',
            'select',
            [
                'label' => __('Infinity loop'),
                'name' => 'loop',
                'required' => false,
                'options' => ['0' => __('Use general config'), '1' => __('Yes'), '2'=> __('No')]
            ]
        );
		
		$fieldset->addField(
            'next_image',
            'image',
            [
				'name' => 'next_image', 
				'label' => __('Next icon'),
				'required' => false,
				'note' => __('Blank fo use general config.')
			]
        );
		
		$fieldset->addField(
            'prev_image',
            'image',
            [
				'name' => 'prev_image', 
				'label' => __('Previous icon'),
				'required' => false,
				'note' => __('Blank fo use general config.')
			]
        );
		
		
		$fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'name' => 'slider_status',
                'required' => false,
                'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
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
