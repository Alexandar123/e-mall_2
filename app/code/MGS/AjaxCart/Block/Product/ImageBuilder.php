<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
declare(strict_types=1);

namespace MGS\AjaxCart\Block\Product;

use Magento\Catalog\Block\Product\Image as ImageBlock;
use Magento\Catalog\Model\View\Asset\ImageFactory as AssetImageFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\ConfigInterface;
use Magento\Catalog\Helper\Image as ImageHelper;

class ImageBuilder
{
	
    /**
     * @var ConfigInterface
     */
    private $presentationConfig;

    /**
     * @var AssetImageFactory
     */
    private $viewAssetImageFactory;
	
	/**
     * @var Product
     */
    protected $product;
	
    /**
     * @var string
     */
    protected $imageId;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var ParamsBuilder
     */
    private $imageParamsBuilder;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var PlaceholderFactory
     */
    private $viewAssetPlaceholderFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ConfigInterface $presentationConfig
     * @param AssetImageFactory $viewAssetImageFactory
     * @param PlaceholderFactory $viewAssetPlaceholderFactory
     * @param ParamsBuilder $imageParamsBuilder
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ConfigInterface $presentationConfig,
        AssetImageFactory $viewAssetImageFactory,
        PlaceholderFactory $viewAssetPlaceholderFactory,
        ParamsBuilder $imageParamsBuilder
    ) {
        $this->objectManager = $objectManager;
        $this->presentationConfig = $presentationConfig;
        $this->viewAssetPlaceholderFactory = $viewAssetPlaceholderFactory;
        $this->viewAssetImageFactory = $viewAssetImageFactory;
        $this->imageParamsBuilder = $imageParamsBuilder;
    }

    /**
     * Retrieve image custom attributes for HTML element
     *
     * @param array $attributes
     * @return string
     */
    private function getStringCustomAttributes(array $attributes): string
    {
        $result = [];
        foreach ($attributes as $name => $value) {
            $result[] = $name . '="' . $value . '"';
        }
        return !empty($result) ? implode(' ', $result) : '';
    }

    /**
     * Calculate image ratio
     *
     * @param $width
     * @param $height
     * @return float
     */
    private function getRatio(int $width, int $height): float
    {
        if ($width && $height) {
            return $height / $width;
        }
        return 1.0;
    }
	
	/**
     * Set product
     *
     * @param Product $product
     * @return $this
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;
        return $this;
    }
	
	/**
     * Set image ID
     *
     * @param string $imageId
     * @return $this
     */
    public function setImageId($imageId)
    {
        $this->imageId = $imageId;
        return $this;
    }

    /**
     * Set custom attributes
     *
     * @param array $attributes
     * @return $this
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @param Product $product
     *
     * @param string $imageType
     * @return string
     */
    private function getLabel(Product $product, string $imageType): string
    {
        $label = $product->getData($imageType . '_' . 'label');
        if (empty($label)) {
            $label = $product->getName();
        }
        return (string) $label;
    }

    /**
     * Create image block from product
     * @param Product $product
     * @param string $imageId
     * @param array|null $attributes
     * @return ImageBlock
     */
    public function create(Product $product = null, string $imageId = null, array $attributes = null)
    {
		$product = $this->product;
        $imageId = $this->imageId;
        $attributes = $this->attributes;
		
        $viewImageConfig = $this->presentationConfig->getViewConfig()->getMediaAttributes(
            'Magento_Catalog',
            ImageHelper::MEDIA_TYPE_CONFIG_NODE,
            $imageId
        );

        $imageMiscParams = $this->imageParamsBuilder->build($viewImageConfig);
        $originalFilePath = $product->getData($imageMiscParams['image_type']);

        if ($originalFilePath === null || $originalFilePath === 'no_selection') {
            $imageAsset = $this->viewAssetPlaceholderFactory->create(
                [
                    'type' => $imageMiscParams['image_type']
                ]
            );
        } else {
            $imageAsset = $this->viewAssetImageFactory->create(
                [
                    'miscParams' => $imageMiscParams,
                    'filePath' => $originalFilePath,
                ]
            );
        }

        $data = [
            'data' => [
                'template' => 'MGS_AjaxCart::product/image_with_borders.phtml',
                'image_url' => $imageAsset->getUrl(),
                'width' => $imageMiscParams['image_width'],
                'height' => $imageMiscParams['image_height'],
                'label' => $this->getLabel($product, $imageMiscParams['image_type']),
                'ratio' => $this->getRatio($imageMiscParams['image_width'], $imageMiscParams['image_height']),
                'custom_attributes' => $this->getStringCustomAttributes($attributes),
                'product_id' => $product->getId()
            ],
        ];

        return $this->objectManager->create(ImageBlock::class, $data);
    }
}
