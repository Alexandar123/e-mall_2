<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Portfolio\Block;

use Magento\Framework\View\Element\Template;

/**
 * Main contact form block
 */
class Widget extends Template
{
	/**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     *
     */

    protected $_storeManager;
    
	
    /**
     * @param Template\Context $context
     * @param array $data
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
		Template\Context $context, 
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		array $data = []
	)
    {
        parent::__construct($context, $data);
		$this->_objectManager = $objectManager;
		$this->_storeManager = $storeManager;
    }
	
	public function getModel(){
		return $this->_objectManager->create('MGS\Portfolio\Model\Portfolio');
	}
	
	public function getPortfolio(){
		return $this->getModel()->load($this->getRequest()->getParam('id'));
	}
	
	public function getBaseImage($portfolio){
		$filePath = 'mgs/portfolio/image/'.$portfolio->getBaseImage();
		if($filePath!=''){
			$imageUrl = $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $filePath;
			return $imageUrl;
		}
		return 0;
	}
	
	public function getCategories($portfolio){
		$collection = $this->_objectManager->create('MGS\Portfolio\Model\Stores')
			->getCollection()
			->addFieldToFilter('portfolio_id', $portfolio->getId());
		
		$resourceModel = $this->_objectManager->create('MGS\Portfolio\Model\ResourceModel\Stores');
		$collection = $resourceModel->joinFilter($collection);
		return $collection;
	}
	
	public function getPortfolioAddress($portfolio){
		$identifier = $portfolio->getIdentifier();
		if($identifier!=''){
			return $this->getUrl('portfolio/'.$identifier);
		}
		return $this->getUrl('portfolio/index/view', ['id'=>$portfolio->getId()]);
	}
	
	public function getThumbnailSrc($portfolio){
		$filePath = 'mgs_portfolio/'.$portfolio->getThumbnailImage();
		if($filePath!=''){
			$thumbnailUrl = $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $filePath;
			return $thumbnailUrl;
		}
		return 0;
	}
	
	public function getCategoriesText($portfolio){
		$collection = $this->getCategories($portfolio);
		
		if(count($collection)>0){
			$arrResult = [];
			foreach($collection as $item){
				$arrResult[] = $item->getName();
			}
			return implode(', ', $arrResult);
		}
		return '';
	}
	
	public function getCategoriesLink($portfolio){
		$collection = $this->getCategories($portfolio);
		$html = '';
		if(count($collection)>0){
			$i=0;
			foreach($collection as $item){
				$cate = $this->_objectManager->create('MGS\Portfolio\Model\Category')->getCollection()->addFieldToFilter('category_id', ['eq' => $item->getCategoryId()])->getFirstItem();
				$i++;
				if($cate->getIdentifier()!=''){
					$html .= '<a href="'.$this->getUrl('portfolio/'.$cate->getIdentifier()).'">'.$item->getName().'</a>';
				}else{
					$html .= '<a href="'.$this->getUrl('portfolio/category/view', ['id'=>$cate->getId()]).'">'.$item->getName().'</a>';
				}
				
				if($i<count($collection)){
					$html .= ', ';
				}
			}
		}
		return $html;
	}
	
	public function getPortfolios(){
		$portfolios = $this->getModel()
			->getCollection()
			->addFieldToFilter('status', 1);
		
		if($this->hasData('category_ids')){
			$resourceModel = $this->_objectManager->create('MGS\Portfolio\Model\ResourceModel\Portfolio');
			$portfolios = $resourceModel->filterByCategories($portfolios, $this->getData('category_ids'));
		}
		if($this->getLimit() > 0){
			$portfolios->setPageSize($this->getLimit());
		}
		
		foreach ($portfolios as $portfolio) {
			if($portfolio->getIdentifier()!=''){
				$portfolio->setAddress($this->getUrl('portfolio/'.$portfolio->getIdentifier()));
			}else{
				$portfolio->setAddress($this->getUrl('portfolio/index/view', ['id'=>$portfolio->getId()]));
			}
		}

		return $portfolios;
	}
	
	public function truncate($content, $length){
		return $this->filterManager->truncate($content, ['length' => $length, 'etc' => '']);
	}

	/**
	 *
	 * @param $idPortfolio
	 * @return boolean
	 *
	 */
	
	public function isActive($idPortfolio): bool
	{
		$model = $this->_objectManager->create('MGS\Portfolio\Model\PortfolioStore');
		$data = $model->getCollection()->addFieldToFilter("portfolio_id", $idPortfolio)->getData();
		if(count($data) == 1) {
			$stringStoreId = $data[0]['store_id'];
			$arrayStoreId = explode(',', $stringStoreId);
			$storeId = $this->getStoreId();
			if(in_array($storeId, $arrayStoreId) || in_array('0', $arrayStoreId)) {
				return true;
			}else{
				return false;
			}
		}
		return false;
	}


	/**
	 *
	 * @return current store id
	 *
	 */
	
	public function getStoreId(): int 
	{
		return $this->_storeManager->getStore()->getId();
	}
}

