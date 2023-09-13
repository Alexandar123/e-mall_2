<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Portfolio\Block;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\View\Element\Template;

/**
 * Main contact form block
 */
class Category extends Template
{
	/**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected $resourceConnection;

    /**
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
		Template\Context $context,
        ResourceConnection $resourceConnection,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		 array $data = []
	)
    {
        parent::__construct($context, $data);
		$this->_objectManager = $objectManager;
        $this->resourceConnection = $resourceConnection;
    }

	/**
     * Prepare global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
		$title = __('Portfolio List');

		if($id = $this->getRequest()->getParam('id')){
			$category = $this->getModel()->load($id);
			$title = $category->getCategoryName();
		}

		$breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
		$breadcrumbsBlock->addCrumb(
			'home',
			[
				'label' => __('Home'),
				'title' => __('Go to Home Page'),
				'link' => $this->_storeManager->getStore()->getBaseUrl()
			]
		);

		$breadcrumbsBlock->addCrumb('portfolio_category', ['label' => $title, 'title' => $title]);

        $this->pageConfig->getTitle()->set($title);
        return parent::_prepareLayout();
    }

	public function getModel(){
		return $this->_objectManager->create('MGS\Portfolio\Model\Category');
	}

	public function getPortfolios($cateid = NULL){
        $store = $this->getStore();
		$portfolios = $this->_objectManager->create('MGS\Portfolio\Model\Portfolio')
			->getCollection()
			->addFieldToFilter('status', 1);

		foreach ($portfolios as $portfolio) {
		    $data = $portfolio->getData();
            $temp = $portfolio->getPortfolioId();
            $update = $this->getPortfolioByStore($store->getId(), $temp);
            foreach ($update as $key =>$value) {
                $data[$key] = $value['value'];
            }

            $portfolio->setData($data);
        }

		if($cateid != null){
			$resourceModel = $this->_objectManager->create('MGS\Portfolio\Model\ResourceModel\Portfolio');
			$portfolios = $resourceModel->joinFilter($portfolios, $cateid);
		}
		return $portfolios;
	}


	public function getCurrentPortfolios(){
		$portfolios = $this->getPortfolios();
		$id = $this->getRequest()->getParam('id');
		if($id != ""){
			$resourceModel = $this->_objectManager->create('MGS\Portfolio\Model\ResourceModel\Portfolio');
			$portfolios = $resourceModel->joinFilter($portfolios, $id);
		}

		return $portfolios;
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

	public function getCategories($portfolio){
		$collection = $this->_objectManager->create('MGS\Portfolio\Model\Stores')
			->getCollection()
			->addFieldToFilter('portfolio_id', $portfolio->getId());

		$resourceModel = $this->_objectManager->create('MGS\Portfolio\Model\ResourceModel\Stores');
		$collection = $resourceModel->joinFilter($collection);
		return $collection;
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

	public function getMenu(){
		$menu = $this->getModel()->getCollection();

		foreach ($menu as $cate) {
			if($cate->getIdentifier()!=''){
				$cate->setLinkCate($this->getUrl('portfolio/'.$cate->getIdentifier()));
			}else{
				$cate->setLinkCate($this->getUrl('portfolio/category/view', ['id'=>$cate->getId()]));
			}

        }
		return $menu;
	}

	public function truncate($content, $length){
		return $this->filterManager->truncate($content, ['length' => $length, 'etc' => '']);
	}

	public function getFilterClass($portfolio){
		$i = 0;
		$html = "";
		$collection = $this->getCategories($portfolio);
		foreach($collection as $item){
			$i++;
			if($i > 1){
				$html .= ' ';
			}
			$html .= 'item_'.$item->getCategoryId();
		}
		return $html;
	}

    public function getPortfolioByStore($store, $portfolio_id) {
        $connection = $this->resourceConnection->getConnection();
        $table = $connection->getTableName('mgs_portfolio_items_update');

        $sql = "SELECT `field`, `value`
                 FROM `$table`
                 WHERE `scope_id`= $store
                 AND `portfolio_id`= $portfolio_id ";
        $portfolio = $connection->fetchAssoc($sql);
        return $portfolio;
    }

    public function getStore(){
        return $this->_storeManager->getStore();
    }
}

