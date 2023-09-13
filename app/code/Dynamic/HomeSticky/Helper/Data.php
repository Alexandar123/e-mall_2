<?php

namespace Dynamic\HomeSticky\Helper;

/**
 * helper class.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	
	/**
	 * @param Session $customerSession
	 * @param \Magento\Framework\App\Helper\Context $context
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 */
	public function __construct(
		
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Sales\Model\Order $order,
		\Magento\Sales\Model\OrderRepository $orderRepository,
		\Magento\Catalog\Helper\ImageFactory $imageFactory,
		\Magento\Framework\View\Asset\Repository $assetRepos,
		\Magento\Catalog\Model\ProductFactory $productFactory
	) {
		$this->_storeManager = $storeManager;
		$this->_order = $order;
		$this->_orderRepository = $orderRepository;
		$this->_productFactory = $productFactory;
		$this->assetRepos = $assetRepos;
        $this->imageFactory = $imageFactory;
		parent::__construct($context);
	}
	
	public function getOrderCollection()
	{
		return $this->_order->getCollection();
	}

	public function getLoadId($id)
	{
		return $this->_order->load($id);
	}

	public function getOrderDetails($id)
    {
        $order = $this->_orderRepository->get($id);
        return $order;
    }

	public function getProduct($id)
    {
        $product = $this->_productFactory->create()->load($id);
        return $product;
    }

	public function getImageUrl()
    {
        $store = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $store;
    }

	public function getPlaceHolderImage(): string
    {
        $imagePlaceholder = $this->imageFactory->create();
        $smallImagePlaceHolder = $imagePlaceholder->getPlaceholder('small_image');
        return $this->assetRepos->getUrl($smallImagePlaceHolder);
    }

}
