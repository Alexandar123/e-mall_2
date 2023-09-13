<?php

namespace MGS\StoreLocator\Block;

use MGS\StoreLocator\Model\StoreFactory;
use Magento\Framework\Session\SessionManager as SessionManager;

class Stores extends \Magento\Framework\View\Element\Template
{
    protected $_countryFactory;
    protected $_storeFactory;
    protected $_sessionManager;
    protected $_helperImage;
    protected $_storeCollection = null;
    protected $_perPage = 6;
	protected $_logo; 
	/**
     * Url Fragment for pagination
     *
     * @var string|null
     */
    protected $_fragment = null;
	
	
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Model\Config\Source\Country $countryFactory,
        \Magento\Catalog\Helper\Image $helperImage,
		\Magento\Theme\Block\Html\Header\Logo $logo,
        StoreFactory $storeFactory,
        SessionManager $sessionManager,
        array $data = []
    )
    {
        $this->_countryFactory = $countryFactory;
        $this->_storeFactory = $storeFactory;
        $this->_helperImage = $helperImage;
		 $this->_logo = $logo;
        $this->_sessionManager = $sessionManager;
        parent::__construct($context, $data);
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getCountries()
    {
        return $this->_countryFactory->toOptionArray();
    }

    protected function _getStoreCollection()
    {
        $collection = $this->_storeFactory->create()->getCollection();
        $collection->addFieldToFilter('status', 1)
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setOrder('created_at', 'desc');
        return $collection;
    }

    public function getStoreCollection()
    {
        $data = $this->getRequest()->getParams();
        if (is_null($this->_storeCollection)) {
            $this->_storeCollection = $this->_getStoreCollection();
            $this->_storeCollection->setPageSize($this->_perPage)
                ->setCurPage($this->getCurrentPage());
            if(!empty($data['country'])) {
                $this->_storeCollection->addFieldToFilter('country', $data['country']);
            }
            if(!empty($data['state'])) {
                $this->_storeCollection->addFieldToFilter('state', $data['state']);
            }
            if(!empty($data['city'])) {
                $this->_storeCollection->addFieldToFilter('city', $data['city']);
            }
            if(!empty($data['zipcode'])) {
                $this->_storeCollection->addFieldToFilter('zipcode', $data['zipcode']);
            }

            if(!empty($data['lat_search']) && !empty($data['long_search'])){
                $this->_storeCollection->addDistanceFilter($data);
            }
        }
        return $this->_storeCollection;
    }

    public function getImageUrl($image = null) {
        if(empty($image)) {
            return $this->_logo->getLogoSrc();
        }
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$image;
    }

    /**
     * Fetch the current page for the stores list
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->getRequest()->getParam('p', false) ? $this->getRequest()->getParam('p') : 1;
    }
    /**
     * Get a pager
     *
     * @return string|null
     */
    public function getPager()
    {
        $pager = $this->getChildBlock('store.list.pager');
        if ($pager) {
            $storesPerPage = $this->_perPage;
            $pager->setAvailableLimit(array($storesPerPage => $storesPerPage));
            $pager->setTotalNum($this->getStoreCollection()->getSize());
            $pager->setCollection($this->getStoreCollection());
            $pager->setShowPerPage(true);
            $pager->setShowAmounts(true);
            return $pager->toHtml();
        }

        return null;
    }

    /**
     * Return store radius/default radius
     *
     * @param MGS_Storelocator_Model_Store $storelocator
     * @return int
     */
    public function getRadius($storelocator = NULL)
    {
        //Return store radius
        if (!is_null($storelocator)) {
            if ($storelocator->getRadius()) {
                return $storelocator->getRadius();
            } else {
                // Return default config radius
                return 100;
            }
        } else {
            // Return default config radius
            return 100;
        }
        return;
    }

    /**
     * Return store zoom level/default zoom level
     *
     * @param MGS_Storelocator_Model_Storelocator $storelocator
     * @return int
     */
    public function getZoomLevel($storelocator=NULL)
    {
        //Return store zoom level
        if(!is_null($storelocator)) {
            if($storelocator->getZoomLevel()){
                return $storelocator->getZoomLevel();
            } else {
                // Return default config zoom level
                return 14;
            }
        } else {
            // Return default config zoom level
            return 14;
        }
        return;
    }

	
    public function getStoreUrlKey($store)
    {
		$storeUrl = trim($store->getStoreUrl(), ' ');
		if($storeUrl!=''){
			return $this->getUrl('storelocator/'.$storeUrl);
		}
		return $this->getUrl('storelocator/index/view', ['id'=>$store->getId()]);
		
	}
    

}
