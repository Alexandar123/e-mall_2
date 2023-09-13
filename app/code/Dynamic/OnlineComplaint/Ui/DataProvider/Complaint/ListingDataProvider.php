<?php

namespace Dynamic\OnlineComplaint\Ui\DataProvider\Complaint;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Framework\Exception\LocalizedException;

class ListingDataProvider extends DataProvider
{
    /**
     * @var PoolInterface
     */
    private $modifierPool;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Reporting $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param PoolInterface $modifierPool
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Reporting $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        PoolInterface $modifierPool,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->modifierPool = $modifierPool;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $data = parent::getData();

        return $this->prepareItemsData($data);
    }

    /**
     * Prepare items data
     *
     * @param array $data
     * @return array
     * @throws LocalizedException
     */
    private function prepareItemsData($data)
    {
        $itemsData = isset($data['items']) ? $data['items'] : [];
        foreach ($this->modifierPool->getModifiersInstances() as $modifier) {
            $itemsData = $modifier->modifyData($itemsData);
        }
        $data['items'] = $itemsData;
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        return $this->modifyMeta($meta);
    }

    /**
     * Modify meta
     *
     * @param array $meta
     * @return array
     * @throws LocalizedException
     */
    protected function modifyMeta($meta)
    {
        foreach ($this->modifierPool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }
}
