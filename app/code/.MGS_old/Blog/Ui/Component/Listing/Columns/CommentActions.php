<?php

namespace MGS\Blog\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use MGS\Blog\Block\Adminhtml\Comment\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\UrlInterface;

class CommentActions extends Column
{
    const COMMENT_URL_PATH_APPROVE = 'blog/comment/approve';
    const COMMENT_URL_PATH_UNAPPROVE = 'blog/comment/unapprove';
    const COMMENT_URL_PATH_DELETE = 'blog/comment/delete';
    protected $actionUrlBuilder;
    protected $urlBuilder;
    private $approveUrl;
    private $unapproveUrl;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlBuilder $actionUrlBuilder,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $approveUrl = self::COMMENT_URL_PATH_APPROVE,
        $unapproveUrl = self::COMMENT_URL_PATH_UNAPPROVE
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->actionUrlBuilder = $actionUrlBuilder;
        $this->approveUrl = $approveUrl;
        $this->unapproveUrl = $unapproveUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['comment_id'])) {
                    $item[$name]['approve'] = [
                        'href' => $this->urlBuilder->getUrl($this->approveUrl, ['comment_id' => $item['comment_id']]),
                        'label' => __('Approve')
                    ];
                    $item[$name]['unapprove'] = [
                        'href' => $this->urlBuilder->getUrl($this->unapproveUrl, ['comment_id' => $item['comment_id']]),
                        'label' => __('Unapprove')
                    ];
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(self::COMMENT_URL_PATH_DELETE, ['comment_id' => $item['comment_id']]),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete ${ $.$data.title }'),
                            'message' => __('Are you sure you wan\'t to delete a ${ $.$data.title } record?')
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}
