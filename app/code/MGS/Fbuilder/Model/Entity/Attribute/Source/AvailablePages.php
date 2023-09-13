<?php

namespace MGS\Fbuilder\Model\Entity\Attribute\Source;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;

class AvailablePages extends AbstractSource
{
    /**
     * @var CollectionFactory
     */
    protected $pageCollectionFactory;

    /**
     * AvailablePages constructor.
     *
     * @param CollectionFactory $pageCollectionFactory
     */
    public function __construct(
        CollectionFactory $pageCollectionFactory
    ) {
        $this->pageCollectionFactory = $pageCollectionFactory;
    }

    public function getAllOptions()
    {
        $cmsOptions['0'] = [
            'label' => 'All CMS Pages',
            'value' => '0'
        ];
        $pagesCollection = $this->pageCollectionFactory->create();
        $pagesCollection->addFieldToFilter('is_active', ['eq'=>1]);
        if ($pagesCollection->getSize()) {
            foreach ($pagesCollection as $page) {

                $value = $page->getPageId();
                $title = 'ID: '. $value . ' - ' . $page->getTitle();
                $cmsPages = [
                        'label' => $title,
                        'value' => $value,
                ];
                $cmsOptions[$value] = $cmsPages;
            }
        }
        return $cmsOptions;
    }
}
