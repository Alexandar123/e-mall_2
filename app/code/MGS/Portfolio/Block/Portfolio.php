<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Portfolio\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\ResourceConnection;
use MGS\Portfolio\Model\ResourceModel\Portfolio\CollectionFactory;

/**
 * Main contact form block
 */
class Portfolio extends Template
{
	/**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected $resourceConnection;

    protected $_portfoliocollectionFactory;

    protected $_storeManager;

    /**
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
		Template\Context $context,
        ResourceConnection $resourceConnection,
        CollectionFactory $portfoliocollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		array $data = []
	)
    {
        parent::__construct($context, $data);
        $this->resourceConnection = $resourceConnection;
        $this->_portfoliocollectionFactory = $portfoliocollectionFactory;
        $this->_storeManager = $storeManager;
		$this->_objectManager = $objectManager;
    }

	/**
     * Prepare global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
		$id = $this->getRequest()->getParam('id');
        $store = $this->getStore();
		$portfolio = $this->getModel()->load($id);

        $data = $portfolio->getData();
        $temp = $portfolio->getPortfolioId();
        $update = $this->getPortfolioByStore($store->getId(), $temp);
        foreach ($update as $key =>$value) {
            $data[$key] = $value['value'];
        }

        $portfolio->setData($data);

		$title = $portfolio->getName();

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
		return $this->_objectManager->create('MGS\Portfolio\Model\Portfolio');
	}

	public function getPortfolio(){
        $store = $this->getStore();
        $portfolio = $this->getModel()->load($this->getRequest()->getParam('id'));

        $data = $portfolio->getData();
            $temp = $portfolio->getPortfolioId();
            $update = $this->getPortfolioByStore($store->getId(), $temp);
            foreach ($update as $key =>$value) {
                $data[$key] = $value['value'];
        }

            $portfolio->setData($data);
        return $portfolio;
	}

	public function getBaseImage($portfolio){
		if($portfolio->getBaseImage()){
			$result = [];
			$baseImage = $portfolio->getBaseImage();
            $galleryArray = explode(" ", $baseImage);
			if(count($galleryArray)>0){
				foreach($galleryArray as $img){
					$filePath = 'mgs_portfolio/'.$img;
					if($filePath!=''){
						$imageUrl = $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $filePath;
						$result[] = $imageUrl;
					}
				}
			}
			return $result;
		} else {
            return 0;
        }

	}

	public function getCategories($portfolio){
		$collection = $this->_objectManager->create('MGS\Portfolio\Model\Stores')
			->getCollection()
			->addFieldToFilter('portfolio_id', $portfolio->getId());

		$resourceModel = $this->_objectManager->create('MGS\Portfolio\Model\ResourceModel\Stores');
		$collection = $resourceModel->joinFilter($collection);
		return $collection;
	}

	public function getRelatedPortfolio($portfolio){
		$collection = $this->getCategories($portfolio);
		if(count($collection)>0){
			$arrResult = array();
			foreach($collection as $item){
				$arrResult[] = $item->getCategoryId();
			}
			$catString = implode(', ', $arrResult);

			$portfolios = $this->getModel()
				->getCollection()
				->addFieldToFilter('status', 1);

			if($catString != ''){
				$resourceModel = $this->_objectManager->create('MGS\Portfolio\Model\ResourceModel\Portfolio');
				$portfolios = $resourceModel->getRelatedPortfolio($portfolios, $portfolio->getId(), $catString);
			}
			return $portfolios;
		}
		return false;
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
		} else {
		    return 0;
        }
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

		if($this->getPortfolioCount()>0){
			$portfolios->setPageSize($this->getPortfolioCount());
		}

		if($this->hasData('category_ids')){
			$resourceModel = $this->_objectManager->create('MGS\Portfolio\Model\ResourceModel\Portfolio');
			$portfolios = $resourceModel->filterByCategories($portfolios, $this->getData('category_ids'));
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

