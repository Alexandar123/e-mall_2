<?php

nameSpace MGS\ThemeSettings\Block\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;

class Landing  extends \Magento\Framework\View\Element\Template {

    protected $_storeManager;

    protected $categoryRepository;

    public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
		 array $data = []
    ) {
		parent::__construct($context, $data);
        $this->_storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
    }

    public function getCategory($categoryId) {
		$category = $this->categoryRepository->get($categoryId, $this->_storeManager->getStore()->getId());
        return $category;
    }

	/**
     * @param string $subCatid
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getImageUrl($image)
    {
        $url = false;
        if ($image) {
            if (is_string($image)) {
                $path = explode("/",$image);
                $url = $this->_storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . 'catalog/category/' . end($path);
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }
        } else {
            $url = $this->_storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . 'catalog/category/placeholder.jpg';
        }
        return $url;
    }

}
