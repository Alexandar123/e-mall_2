<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Lookbook\Block\Widget;

/**
 * Widget to display link to CMS page
 */
class Slider extends \MGS\Lookbook\Block\AbstractLookbook implements \Magento\Widget\Block\BlockInterface
{

	/**
     * @var \MGS\Lookbook\Model\ResourceModel\Lookbook\CollectionFactory
     */
    protected $lookbookCollectionFactory;
	
	/**
     * @var \MGS\Lookbook\Model\SlideFactory
     */
    protected $slideFactory;
	
    protected $_slider;
	
    protected $_lookbooks;
	
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		\Magento\Catalog\Block\Product\Context $productContext,
		\MGS\Lookbook\Helper\Data $_helper,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $_productCollectionFactory,
		\Magento\Framework\Url\Helper\Data $urlHelper,
		\MGS\Lookbook\Model\ResourceModel\Lookbook\CollectionFactory $lookbookCollectionFactory,
		\MGS\Lookbook\Model\SlideFactory $slideFactory,
        array $data = []
    ) {
        parent::__construct($context, $productContext, $_helper, $_productCollectionFactory, $urlHelper, $data);
		$this->lookbookCollectionFactory = $lookbookCollectionFactory;
		$this->slideFactory = $slideFactory;
		
		$sliderId = $this->getData('slider_id');
		
		if ($sliderId) {
            $slider = $this->slideFactory->create()->load($sliderId);
            if ($slider->getStatus()==1) {
				
				$lookbooks = $this->lookbookCollectionFactory->create()
					->addFieldToFilter('status', 1);
					
				$lookbooks->addSliderFilter($sliderId);
				
				if($lookbooks->getSize()>0){
					$this->_lookbooks = $lookbooks;
				}

				$this->_slider = $slider;
            }
        }
    }

	public function getSlider(){
		return $this->_slider;
	}
	
	public function getLookbooks(){
		return $this->_lookbooks;
	}
	
	/* Show slider navigation or not */
	public function getNavigation(){
		$value = $this->_slider->getNavigation();
		if(!$value){
			$value = $this->_helper->getStoreConfig('lookbook/slider/navigation');
		}
		return $this->convertValue($value);
	}
	
	/* Show slider pagination or not */
	public function getPagination(){
		$value = $this->_slider->getPagination();
		if(!$value){
			$value = $this->_helper->getStoreConfig('lookbook/slider/pagination');
		}
		return $this->convertValue($value);
	}
	
	/* Autoplay or not */
	public function getAutoPlay(){
		$value = $this->_slider->getAutoPlay();
		if(!$value){
			$value = $this->_helper->getStoreConfig('lookbook/slider/auto_play');
		}
		return $this->convertValue($value);
	}
	
	/* Autoplay timeout */
	public function getAutoplayTimeout(){
		$value = $this->_slider->getAutoPlayTimeout();
		if($value==''){
			$value = $this->_helper->getStoreConfig('lookbook/slider/auto_play_timeout');
		}
		return $value;
	}
	
	/* Stop autoplay when mouseover */
	public function getStopAuto(){
		$value = $this->_slider->getStopAuto();
		if(!$value){
			$value = $this->_helper->getStoreConfig('lookbook/slider/stop_auto');
		}
		return $this->convertValue($value);
	}
	
	/* Loop or not */
	public function getLoop(){
		$value = $this->_slider->getLoop();
		if(!$value){
			$value = $this->_helper->getStoreConfig('lookbook/slider/loop');
		}
		return $this->convertValue($value);
	}
	
	/* next Icon Url */
	public function getNextIcon(){
		$value = $this->_slider->getNextImage();
		$img = '';
		if($value !== ''){
			$img = '<img src="'.$this->getControlImageUrl($value).'"/>';
		}
		
		return $img;
	}
	
	/* Previous Icon Url */
	public function getPrevIcon(){
		$value = $this->_slider->getPrevImage();
		$img = '';
		if($value !== ''){
			$img = '<img src="'.$this->getControlImageUrl($value).'"/>';
		}
		
		return $img;
	}
	
	/* Convert to owl carousel option value: true/false */
	public function convertValue($value){
		if($value == 1){
			return 'true';
		}
		return 'false';
	}
	
	public function getControlImageUrl($value){
		return $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]).$value;
	}
}
