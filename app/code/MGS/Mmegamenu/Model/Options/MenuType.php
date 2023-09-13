<?php 

namespace MGS\Mmegamenu\Model\Options;

use Magento\Framework\Option\ArrayInterface;

class MenuType implements ArrayInterface {

    protected $data;

    protected $request;

    function __construct(
        \MGS\Mmegamenu\Model\Mmegamenu $megamenu,
        \Magento\Framework\App\Request\Http $request
    )
    {
        $this->data = $megamenu;
        $this->request = $request;
    }
    function toOptionArray() {
        $megamenu_id = $this->request->getParam('id');
        $megamenu = $this->data->load($megamenu_id);
        $menu_type = $megamenu->getMenuType();
        
        if($menu_type == 2) {
            $options = [
                'static' => [
                    'label' => 'Static Content',
                    'value' => '2'
                ],
                'category' => [
                    'label' => 'Catalog Category',
                    'value' => '1'
                ],
            ];
        }
        else {
            $options = [
                'category' => [
                    'label' => 'Catalog Category',
                    'value' => '1'
                ],
                'static' => [
                    'label' => 'Static Content',
                    'value' => '2'
                ],
            ];
        }
        return $options;
    }
}