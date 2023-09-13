<?php
namespace MGS\Ajaxlayernavigation\Block\Navigation; 


use MGS\Ajaxlayernavigation\Model\Layer\Filter\Item as FilterItem;

class RenderLayered extends \Magento\Framework\View\Element\Template
{
     
    protected $_template = 'Magento_Swatches::product/layered/renderer.phtml';

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Eav\Model\Entity\Attribute $eavAttributeModel,
        \MGS\Ajaxlayernavigation\Model\ResourceModel\Layer\Filter\AttributeFactory $customAttribute,
        \Magento\Swatches\Helper\Data $swatchesHelper,
        \Magento\Swatches\Helper\Media $mediaswatchHelper,
        array $data = []
    ) {
        $this->eavAttributeModel = $eavAttributeModel;
        $this->customAttribute = $customAttribute;
        $this->swatchesHelper = $swatchesHelper;
        $this->mediaHelper = $mediaswatchHelper;

        parent::__construct($context, $data);
    }

    public function setSwatchFilter($filter)
    {
        $this->filterModel = $filter;
        $this->eavAttributeModel = $filter->getAttributeModel();

        return $this;
    }

    public function getSwatchData()
    {
        
        $isAttributeModel = $this->eavAttributeModel instanceof \Magento\Eav\Model\Entity\Attribute;
        if (false === $isAttributeModel) {
            throw new \RuntimeException('Attribute model has not been set.');
        }

        $attrOptions = [];
        foreach ($this->eavAttributeModel->getOptions() as $option) {
            if ($current = $this->getCurrentOption($this->filterModel->getItems(), $option)) {
                $attrOptions[$option->getValue()] = $current;
            } elseif ($this->isShowEmpty()) {
                $attrOptions[$option->getValue()] = $this->getIsUnused($option);
            }
        }

        $optionIds = array_keys($attrOptions);
        $swatchesData = $this->swatchesHelper->getSwatchesByOptionsId($optionIds);

        $data = [
            'attribute_id' => $this->eavAttributeModel->getId(),
            'attribute_code' => $this->eavAttributeModel->getAttributeCode(),
            'attribute_label' => $this->eavAttributeModel->getStoreLabel(),
            'options' => $attrOptions,
            'swatches' => $swatchesData,
        ];

        return $data;
    }

    public function buildUrl($code, $id)
    {
        return $this->_urlBuilder->getUrl(
            '*/*/*',
            [
                '_current' => true,
                '_use_rewrite' => true,
                '_query' => [$code => $id]
            ]
        );
    }

    protected function getIsUnused(Option $option)
    {
        return [
            'label' => $option->getLabel(),
            'link' => 'javascript:void();',
            'custom_style' => 'disabled'
        ];
    }

    protected function getCurrentOption(array $items, $option)
    {
        $result = false;
        $item = $this->getFilterItemById($items, $option->getValue());
        if ($item && $this->isOptionVisible($item)) {
            $result = $this->getViewData($item, $option);
        }

        return $result;
    }

    protected function getViewData($item,$option)
    {
        $custom = '';
        $code = $this->eavAttributeModel->getAttributeCode();
        $value = $item->getValue();
        $optionLink = $this->buildUrl($code, $value);
        if ($this->isDisabled($item)) {
            $custom = 'disabled';
            $optionLink = 'javascript:void();';
        }

        return [
            'label' => $option->getLabel(),
            'link' => $optionLink,
            'custom_style' => $custom
        ];
    }

    protected function isOptionVisible($filterItem)
    {
        return $this->isDisabled($filterItem) && $this->isShowEmpty() ? false : true;
    }

    protected function isShowEmpty()
    {
        return $this->eavAttributeModel->getIsFilterable() != 1;
    }

    protected function isDisabled($item)
    {
        return !$item->getCount();
    }

    protected function getFilterItemById(array $items, $id)
    {
        foreach ($items as $item) {
            if ($id == $item->getValue()) {
                return $item;
            }
        }
        return false;
    }

    public function getSwatchPath($type, $file)
    {
        return $this->mediaHelper->getSwatchAttributeImage($type, $file);
    }
}
