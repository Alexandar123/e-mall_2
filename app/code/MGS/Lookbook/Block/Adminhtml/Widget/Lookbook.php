<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Lookbook\Block\Adminhtml\Widget;

/**
 * CMS page chooser for Wysiwyg CMS widget
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Lookbook extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $_lookbook;

    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_lookbookFactory;

    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface
     */
    protected $pageLayoutBuilder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \MGS\Lookbook\Model\Lookbook $lookbook
     * @param \MGS\Lookbook\Model\LookbookFactory $lookbookFactory
     * @param \MGS\Lookbook\Model\ResourceModel\Lookbook\CollectionFactory $collectionFactory
     * @param \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \MGS\Lookbook\Model\Lookbook $lookbook,
        \MGS\Lookbook\Model\LookbookFactory $lookbookFactory,
        \MGS\Lookbook\Model\ResourceModel\Lookbook\CollectionFactory $collectionFactory,
        \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder,
        array $data = []
    ) {
        $this->pageLayoutBuilder = $pageLayoutBuilder;
        $this->_lookbook = $lookbook;
        $this->_lookbookFactory = $lookbookFactory;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Block construction, prepare grid params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setDefaultSort('lookbook_id');
		$this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setDefaultFilter(['chooser_is_active' => '1']);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element Form Element
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function prepareElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $uniqId = $this->mathRandom->getUniqueHash($element->getId());
        $sourceUrl = $this->getUrl('adminhtml/lookbook/chooser', ['uniq_id' => $uniqId]);

        $chooser = $this->getLayout()->createBlock(
            'Magento\Widget\Block\Adminhtml\Widget\Chooser'
        )->setElement(
            $element
        )->setConfig(
            $this->getConfig()
        )->setFieldsetId(
            $this->getFieldsetId()
        )->setSourceUrl(
            $sourceUrl
        )->setUniqId(
            $uniqId
        );

        if ($element->getValue()) {
            $lookbook = $this->_lookbookFactory->create()->load((int)$element->getValue());
            if ($lookbook->getId()) {
                $chooser->setLabel($this->escapeHtml($lookbook->getName()));
            }
        }

        $element->setData('after_element_html', $chooser->toHtml());
        return $element;
    }

    /**
     * Grid Row JS Callback
     *
     * @return string
     */
    public function getRowClickCallback()
    {
        $chooserJsObject = $this->getId();
        $js = '
            function (grid, event) {
                var trElement = Event.findElement(event, "tr");
                var lookbookName = trElement.down("td").next().innerHTML;
                var lookbookId = trElement.down("td").innerHTML.replace(/^\s+|\s+$/g,"");
                ' .
            $chooserJsObject .
            '.setElementValue(lookbookId);
                ' .
            $chooserJsObject .
            '.setElementLabel(lookbookName);
                ' .
            $chooserJsObject .
            '.close();
            }
        ';
        return $js;
    }

    /**
     * Prepare pages collection
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare columns for pages grid
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'chooser_id',
            [
                'header' => __('ID'),
                'index' => 'lookbook_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
		
		$this->addColumn(
            'chooser_image',
            [
                'header' => __('Image'),
                'index' => 'image',
                'header_css_class' => 'col-title',
                'column_css_class' => 'col-title',
				'sortable' => false,
                'filter' => false,
				'renderer' => 'MGS\Lookbook\Block\Adminhtml\Grid\Renderer\Image',
            ]
        );

        $this->addColumn(
            'chooser_title',
            [
                'header' => __('Name'),
                'index' => 'name',
                'header_css_class' => 'col-title',
                'column_css_class' => 'col-title'
            ]
        );

        $this->addColumn(
            'chooser_is_active',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => [
					1 => __('Enabled'),
					0 => __('Disabled')
				],
                'header_css_class' => 'col-status',
                'column_css_class' => 'col-status'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('adminhtml/lookbook/chooser', ['_current' => true]);
    }
}
