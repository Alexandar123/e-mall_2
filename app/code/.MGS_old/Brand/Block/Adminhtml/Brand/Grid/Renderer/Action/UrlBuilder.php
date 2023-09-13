<?php

namespace MGS\Brand\Block\Adminhtml\Brand\Grid\Renderer\Action;

class UrlBuilder
{
    protected $frontendUrlBuilder;

    public function __construct(\Magento\Framework\UrlInterface $frontendUrlBuilder)
    {
        $this->frontendUrlBuilder = $frontendUrlBuilder;
    }

    public function getUrl($routePath, $scope, $store)
    {
        $this->frontendUrlBuilder->setScope($scope);
        $href = $this->frontendUrlBuilder->getUrl(
            $routePath,
            ['_current' => false, '_query' => '___store=' . $store]
        );
        return $href;
    }
}
