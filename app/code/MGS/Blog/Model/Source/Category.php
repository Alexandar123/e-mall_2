<?php

namespace MGS\Blog\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Category implements OptionSourceInterface
{
    protected $categoryModel;

    public function __construct(\MGS\Blog\Model\Category $categoryModel)
    {
        $this->categoryModel = $categoryModel;
    }

    public function toOptionArray($withEmpty = false)
    {
        $collection = $this->categoryModel->getCollection()->setOrder('title', 'ASC');
        $options = [];
        foreach ($collection as $category) {
            $options[] = ['label' => $category->getTitle(), 'value' => $category->getId()];
        }

        if ($withEmpty) {
            array_unshift($options, [
                'value' => '',
                'label' => (string)new \Magento\Framework\Phrase('-- Please Select --')
            ]);
        }
        return $options;
    }
}
