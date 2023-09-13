<?php
namespace MGS\Ajaxlayernavigation\Model\Layer\Filter;

class ItemBuilder
{
    protected $_items = [];

    public function addItemData($label, $value, $count, $active, $plus)
    {
        $this->_items[] = [
            'label'  => $label,
            'value'  => $value,
            'count'  => $count,
            'active' => $active,
            'plus'   => $plus
         ];
    }

    public function build()
    {
        $items = $this->_items;
        $this->_items = [];
        return $items;
    }
}
