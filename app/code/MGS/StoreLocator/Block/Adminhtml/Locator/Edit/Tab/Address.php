<?php
namespace MGS\StoreLocator\Block\Adminhtml\Locator\Edit\Tab;

/**
 * Store locator edit form address tab
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Address extends \Magento\Backend\Block\Widget\Form\Generic {

    protected $_storeFactory;
    protected $_countryFactory;

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
            \Magento\Directory\Model\Config\Source\Country $countryFactory,
            array $data = []
    ) {
        $this->_countryFactory = $countryFactory;
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

        $addressFieldset = $form->addFieldset('address_fieldset', ['legend' => __('Store Address')]);

        $addressFieldset->addField(
                'street_address', 'text', [
            'name' => 'street_address',
            'label' => __('Street Address'),
            'id' => 'street_address',
            'title' => __('Street Address'),
            'required' => true
                ]
        );
        $countryArray = $this->_countryFactory->toOptionArray();
        $addressFieldset->addField(
                'country', 'select', [
            'name' => 'country',
            'label' => __('Country'),
            'id' => 'country',
            'title' => __('Country'),
            'required' => true,
                    'values' => $countryArray
                ]
        );
        $addressFieldset->addField(
                'state', 'text', [
            'name' => 'state',
            'label' => __('State'),
            'id' => 'state',
            'title' => __('State'),
            'required' => true
                ]
        );
        $addressFieldset->addField(
                'city', 'text', [
            'name' => 'city',
            'label' => __('City'),
            'id' => 'city',
            'title' => __('City'),
            'required' => true
                ]
        );
        $addressFieldset->addField(
                'zipcode', 'text', [
            'name' => 'zipcode',
            'label' => __('Zipcode'),
            'id' => 'zipcode',
            'title' => __('Zipcode'),
            'required' => true
                ]
        );
        $addressFieldset->addField('btn_getmap_address', 'note',
            [
                'label'     => '',
                'after_element_html' => '<button id="getmap_address" type="button"><span><span><span>'.__('Get Map').'</span></span></span></button>',
            ]
        );
        
        
        $locationFieldset = $form->addFieldset('location_fieldset', ['legend' => __('Store Location')]);

        $locationFieldset->addField(
                'lat', 'text', [
            'name' => 'lat',
            'label' => __('Latitude'),
            'id' => 'lat',
            'title' => __('Latitude'),
            'required' => true
                ]
        );
        $locationFieldset->addField(
                'lng', 'text', [
            'name' => 'lng',
            'label' => __('Longitude'),
            'id' => 'lng',
            'title' => __('Longitude'),
            'required' => true
                ]
        );
        $locationFieldset->addField('btn_getmap_location', 'note',
            [
                'label'     => '',
                'after_element_html' => '<button id="getmap_location" type="button"><span><span><span>'.__('Get Map').'</span></span></span></button>',
            ]
        );
        
        $mapFieldset = $form->addFieldset('form_map', array('legend'=>''));
        $data = $model->getData();
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
