<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\ThemeSettings\Block\Adminhtml\System;

/**
 * Export CSV button for shipping table rates
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Export extends \MGS\Fbuilder\Block\Adminhtml\System\Export
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;
	
	protected $collectionFactory;
	
	/**
     * Page factory
     *
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;
	
	protected $_request;
	
    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Backend\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Backend\Model\UrlInterface $backendUrl,
		\MGS\Fbuilder\Model\ResourceModel\Section\CollectionFactory $collectionFactory,
		\Magento\Cms\Model\PageFactory $pageFactory,
		\Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $backendUrl, $collectionFactory, $pageFactory, $data);
        $this->_backendUrl = $backendUrl;
		$this->collectionFactory = $collectionFactory;
		$this->_pageFactory = $pageFactory;
		$this->_request = $request;
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        /** @var \Magento\Backend\Block\Widget\Button $buttonBlock  */
		$collection = $this->collectionFactory->create();
		$collection->getSelect()->group('page_id');
		//echo $collection->getSelect(); die();
		
		$html = '<select id="fbuilder_export_page_id" name="groups[export][fields][page_id][value]" class="select admin__control-select" data-ui-id="select-groups-export-fields-page_id-value" style="width:210px; margin-right:10px">
		<option value="">'.__('Choose Page to Export').'</option>';
		if(count($collection)>0){
			foreach($collection as $section){
				$pageId = $section->getPageId();
				$page = $this->_pageFactory->create()->load($pageId);
				if($page->getId()){
					$html .= '<option value="'.$page->getId().'">'. $page->getTitle() .'</option>';
				}
			}
		}
		
		$html .= '</select>';
		
		
		
		if($storeId = $this->_request->getParam('store')){
			$url = $this->_backendUrl->getUrl("adminhtml/fbuilder/export", ['store'=>$storeId]);
		}elseif($websiteId = $this->_request->getParam('website')){
			$url = $this->_backendUrl->getUrl("adminhtml/fbuilder/export", ['website'=>$websiteId]);
		}else{
			$url = $this->_backendUrl->getUrl("adminhtml/fbuilder/export");
		}
		
		$html .= '<button type="button" class="action-default scalable" onclick="exportPage(\''.$url.'\')" data-ui-id="widget-button-0"><span>'.__('Export').'</span></button>';

        return $html;
    }
}
