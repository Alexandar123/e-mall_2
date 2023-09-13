<?php

namespace MGS\StoreLocator\Block\Adminhtml\Locator\Edit\Tab;

/**
 * Store locator edit form main tab
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic {

    protected $_storeFactory;
    protected $_wysiwygConfig;
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
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
     * Prepare form fields
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm() {

        $model = $this->_coreRegistry->registry('locator');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('locator_');

        $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Store Information')]);
        if ($model->getId()) {
            $baseFieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $baseFieldset->addField(
            'name', 'text', [
            'name' => 'name',
            'label' => __('Store Name'),
            'id' => 'name',
            'title' => __('Store Name'),
            'required' => true
                ]
        );
        $baseFieldset->addField(
            'route', 'text', [
            'name' => 'route',
            'label' => __('Store Route'),
            'id' => 'name',
            'title' => __('Store Route'),
            'required' => false
                ]
        );
        $baseFieldset->addField(
                'image', 'image', [
            'name' => 'image',
            'label' => __('Store Logo'),
            'id' => 'image',
            'title' => __('Store Logo'),
            'note' => __('Allowed extensions are jpg, jpeg, gif, png'),
            'required' => false
                ]
        );

        $baseFieldset->addField(
                'email', 'text', [
            'name' => 'email',
            'label' => __('Email'),
            'id' => 'email',
            'title' => __('Email'),
            'required' => false
                ]
        );

        $baseFieldset->addField(
                'phone_number', 'text', [
            'name' => 'phone_number',
            'label' => __('Phone Number'),
            'id' => 'phone_number',
            'title' => __('Phone Number'),
            'required' => false
                ]
        );

        $baseFieldset->addField(
                'fax', 'text', [
            'name' => 'fax',
            'label' => __('Fax'),
            'id' => 'fax',
            'title' => __('Fax'),
            'required' => false
                ]
        );

        $baseFieldset->addField(
                'website', 'text', [
            'name' => 'website',
            'label' => __('Website Url'),
            'id' => 'website',
            'title' => __('Website Url'),
            'required' => false
                ]
        );
        $baseFieldset->addField(
                'status', 'select', [
            'label' => __('Status'),
            'title' => __('Store Status'),
            'name' => 'status',
            'required' => true,
            'options' => [1 => __('Enable'), 0 => __('Disable')]
                ]
        );
        
        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $storeField = $baseFieldset->addField(
                'store_id',
                'multiselect',
                [
                    'name' => 'stores[]',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'values' => $this->_systemStore->getStoreValuesForForm(false, true)
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $storeField->setRenderer($renderer);
        } else {
            $baseFieldset->addField(
                'store_id',
                'hidden',
                ['name' => 'stores[]', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }
        
        $descFieldset = $form->addFieldset('desc_fieldset', ['legend' => __('Store Description')]);
        $descFieldset->addField(
            'description',
            'editor',
            [
                'name' => 'description',
                'label' => __('Store Description'),
                'title' => __('Store Description'),
                'style' => 'height:30em',
                'required' => false,
                'config' => $this->_wysiwygConfig->getConfig()
            ]
        );
        $descFieldset->addField(
            'trading_hours',
            'editor',
            [
                'name' => 'trading_hours',
                'label' => __('Store Openning Hours'),
                'title' => __('Store Openning Hours'),
                'style' => 'height:30em',
                'required' => false,
                'config' => $this->_wysiwygConfig->getConfig()
            ]
        );

        $data = $model->getData();
        $data['store_id'] = $model->getStores();
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
