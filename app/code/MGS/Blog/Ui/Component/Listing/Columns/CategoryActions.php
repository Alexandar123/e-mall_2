<?php

namespace MGS\Blog\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use MGS\Blog\Block\Adminhtml\Category\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\UrlInterface;

class CategoryActions extends Column
{

    /**
     * Url path  to edit
     *
     * @var string
     */
    const URL_PATH_EDIT = 'blog/category/edit';

    /**
     * Url path  to delete
     *
     * @var string
     */
    const URL_PATH_DELETE = 'blog/category/delete';

    /**
     * URL builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    )
    {
        $this->_urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }


    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['category_id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->_urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'category_id' => $item['category_id']
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'delete' => [
                            'href' => $this->_urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'category_id' => $item['category_id']
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __(sprintf('Delete <b>%s</b>',$item['title'])),
                                'message' => __(sprintf('Are you sure you wan\'t to delete a <b>%s</b> record?',$item['title']))
                            ]
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}
