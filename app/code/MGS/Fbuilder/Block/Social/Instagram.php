<?php

namespace MGS\Fbuilder\Block\Social;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\HTTP\ClientFactory;

class Instagram extends Template
{

    protected $_generateHelper;
    protected $_storeManager;
    protected $_httpClientFactory;

    public function __construct(
        Context                       $context,
        \MGS\Fbuilder\Helper\Generate $_generateHelper,
        ClientFactory $httpClientFactory
    )
    {
        $this->_generateHelper = $_generateHelper;
        $this->_storeManager = $context->getStoreManager();
        $this->_httpClientFactory = $httpClientFactory;

        parent::__construct($context);
    }

    public function getStoreConfig($node, $storeId = null)
    {
        return $this->_generateHelper->getStoreConfig($node);
    }

    public function getWidgetInstagramData()
    {
        $result = [];
        $aData = [];
        $instagramToken = $this->getStoreConfig('fbuilder/social/instagram_access_token');

        if (! empty($instagramToken)) {
            $host = "https://graph.instagram.com/me/media?fields=id,media_type,media_url,thumbnail_url,timestamp,permalink,caption,like&access_token=" . $instagramToken;

            $client = $this->_httpClientFactory->create();
            $client->get($host);
            $client->setTimeout(300);
            $content = $client->getBody();

            $content = json_decode($content, true);

            $i = 0;
            $limit = $this->getLimit();

            if (isset($content['data']) && count($content['data']) > 0) {
                foreach ($content['data'] as $data) {
                    $i++;
                    $aData[] = [
                        'src'     => $data['media_url'],
                        'link'    => $data['permalink'],
                        'like'    => $data['likes']['count'] ?? null,
                        'comment' => $data['comments']['count'] ?? null,
                    ];
                    $result = [
                        'data'    => $aData,
                        'message' => 'get data success',
                        'status'  => 'success'
                    ];
                    if (($limit != '') && ($limit != 0) && ($i == $limit)) {
                        break;
                    }
                }
            } else {
                $result = [
                    'data'    => [],
                    'message' => $content['error']['message'] ?? 'get data error',
                    'status'  => 'error'
                ];
            }
        }

        return $result;
    }
}
