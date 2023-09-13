<?php

namespace MGS\Brand\Block\Adminhtml\Brand\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Cms\Model\Wysiwyg\Config;

class Meta extends Generic implements TabInterface
{
    protected $_wysiwygConfig;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        array $data = []
    )
    {
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getTabLabel()
    {
        return __('Meta Information');
    }

    public function getTabTitle()
    {
        return __('Meta Information');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_brand');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('brand_meta_');
        $fieldset = $form->addFieldset('meta_fieldset', ['legend' => __('Meta Information')]);
        $fieldset->addField(
            'meta_keywords',
            'textarea',
            ['name' => 'brand[meta_keywords]', 'label' => __('Meta Keywords'), 'title' => __('Meta Keywords'), 'required' => false]
        );
        $fieldset->addField(
            'meta_description',
            'textarea',
            ['name' => 'brand[meta_description]', 'label' => __('Meta Description'), 'title' => __('Meta Description'), 'required' => false]
        );
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
