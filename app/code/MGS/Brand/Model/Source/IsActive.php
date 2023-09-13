<?php

namespace MGS\Brand\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class IsActive implements OptionSourceInterface
{
    protected $brandModel;

    public function __construct(\MGS\Brand\Model\Brand $brandModel)
    {
        $this->brandModel = $brandModel;
    }

    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->brandModel->getAvailableStatuses();

        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }

        return $options;
    }
}