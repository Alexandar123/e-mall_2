<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Fbuilder\Controller\Element;

use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\App\Cache\Manager as CacheManager;

class Save extends \Magento\Framework\App\Action\Action
{
    protected $_storeManager;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var Request
     */
    protected $request;

    protected $_filesystem;

    protected $_file;

    protected $builderHelper;

    /**
     * File Uploader factory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\View\Element\Context $urlContext,
        CacheManager $cacheManager,
        Request $request,
        \MGS\Fbuilder\Helper\Generate $builderHelper
    ) {
        $this->_storeManager = $storeManager;
        $this->request = $request;
        $this->customerSession = $customerSession;
        $this->_urlBuilder = $urlContext->getUrlBuilder();
        $this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_file = $file;
        $this->builderHelper = $builderHelper;
        $this->cacheManager = $cacheManager;
        parent::__construct($context);
    }

    public function getModel($model)
    {
        return $this->_objectManager->create($model);
    }

    public function execute()
    {
        $files = $this->request->getFiles()->toArray();
        if ($this->customerSession->getUseFrontendBuilder() == 1) {
            $data = $this->getRequest()->getPostValue();
            switch ($data['type']) {
                /* Static content Block */
            case "static":
                $this->removePanelImages('panel', $data);
                $content = $data['content'];

                $data['block_content'] = $content;
                $result['message'] = 'success';
                $sessionMessage = __('You saved the Text content block. Please wait for page reload.');
                break;

            case "owl_banner":
                $this->removePanelImages('slider', $data);

                if (isset($data['setting']['html_slider']) && $data['setting']['html_slider'] != "") {
                    $dataInit = ['autoplay', 'stop_auto', 'navigation', 'pagination', 'loop', 'fullheight', 'rtl', 'hide_nav'];

                    $data = $this->reInitData($data, $dataInit);

                    $speed = '';
                    if ($data['setting']['speed']) {
                        $speed = $data['setting']['speed'];
                    }

                    $sliderHtml = htmlentities($data['setting']['html_slider']);

                    $dot = $data['setting']['pagination'];

                    $content = '{{block class="MGS\Fbuilder\Block\Widget\OwlCarousel" autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" hide_nav="'.$data['setting']['hide_nav'].'" nav_top="'.$data['setting']['nav_top'].'" navigation_position="'.$data['setting']['navigation_position'].'" pagination_position="'.$data['setting']['pagination_position'].'" fullheight="'.$data['setting']['fullheight'].'" pagination="'.$dot.'" loop="'.$data['setting']['loop'].'" rtl="'.$data['setting']['rtl'].'" speed="'.$speed.'" items="'.$data['setting']['items'].'" items_tablet="'.$data['setting']['items_tablet'].'" items_mobile="'.$data['setting']['items_mobile'].'" slide_margin="'.$data['setting']['slide_margin'].'" html_slider="'.$sliderHtml.'" template="widget/owl_slider.phtml"}}';

                    $data['block_content'] = $content;
                    $result['message'] = 'success';
                    $sessionMessage = __('You saved the "OWL Carousel Slider" block. Please wait for page reload.');
                } else {
                    $result['message'] = __('You have not add any images to slider.');
                }
                break;

                /* New Products Block */
            case "new_products":
                $dataInit = ['use_slider', 'autoplay', 'stop_auto', 'rtl', 'navigation', 'loop', 'pagination', 'use_tabs', 'hide_name', 'hide_review', 'hide_price', 'hide_addcart', 'hide_addwishlist', 'hide_addcompare', 'tab_font_bold', 'tab_italic', 'tab_uppercase', 'hide_nav'];
                $data = $this->reInitData($data, $dataInit);
                $categories = '';
                if (isset($data['setting']['category_id'])) {
                    $categories = implode(',', $data['setting']['category_id']);
                }
                if ($data['setting']['template']=='list.phtml') {
                    $data['setting']['use_tabs'] = 0;
                }
                if ($data['setting']['use_tabs']) {
                    $template = 'products/category-tabs.phtml';
                } else {
                    $template = 'products/'.$data['setting']['template'];
                }

                $content = '{{block class="MGS\Fbuilder\Block\Products\NewProducts" block_type="new" limit="'.$data['setting']['limit'].'" category_ids="'.$categories.'" use_slider="'.$data['setting']['use_slider'].'" hide_name="'.$data['setting']['hide_name'].'" hide_review="'.$data['setting']['hide_review'].'" hide_price="'.$data['setting']['hide_price'].'" hide_addcart="'.$data['setting']['hide_addcart'].'" hide_addwishlist="'.$data['setting']['hide_addwishlist'].'" hide_addcompare="'.$data['setting']['hide_addcompare'].'"';

                if ($data['setting']['template']=='list.phtml') {
                    $content .= ' numbercol="'.$data['setting']['numbercol'].'" percol="'.$data['setting']['percol'].'"';
                }

                if ($data['setting']['template']=='grid.phtml') {
                    $content .= ' perrow="'.$data['setting']['perrow'].'" perrow_tablet="'.$data['setting']['perrow_tablet'].'" perrow_mobile="'.$data['setting']['perrow_mobile'].'"';
                }

                if ($data['setting']['use_slider']) {
                    $content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" hide_nav="'.$data['setting']['hide_nav'].'" nav_top="'.$data['setting']['nav_top'].'" navigation_position="'.$data['setting']['navigation_position'].'" pagination_position="'.$data['setting']['pagination_position'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'" slide_by="'.$data['setting']['slide_by'].'" loop="'.$data['setting']['loop'].'" rtl="'.$data['setting']['rtl'].'" slide_margin="'.$data['setting']['slide_margin'].'"';
                }

                if ($data['setting']['use_tabs']) {
                    $content .= ' tab_style="'.$data['setting']['tab_style'].'"';
                    if ($data['setting']['tab_font_bold']) {
                        $content .= ' tab_font_bold="1"';
                    }
                    if ($data['setting']['tab_italic']) {
                        $content .= ' tab_italic="1"';
                    }
                    if ($data['setting']['tab_uppercase']) {
                        $content .= ' tab_uppercase="1"';
                    }
                    $content .= ' tab_align="'.$data['setting']['tab_align'].'"';

                    $data['custom_style_temp']['tab-style'] = ['tab-'.$data['setting']['tab_style']=>[
                            'font-size' => $data['setting']['font_size'],
                            'primary-color' => $data['setting']['tab_primary_color'],
                            'secondary-color' => $data['setting']['tab_secondary_color'],
                            'third-color' => $data['setting']['tab_third_color']
                        ]
                    ];
                }

                $content .= ' template="'.$template.'"}}';

                $data['block_content'] = $content;

                $result['message'] = 'success';
                $sessionMessage = __('You saved the "New Products" block. Please wait for page reload.');
                break;

                /* Attribute Products Block */
            case "attribute_products":
                $dataInit = ['use_slider', 'autoplay', 'stop_auto', 'rtl', 'navigation', 'loop', 'pagination', 'use_tabs', 'hide_name', 'hide_review', 'hide_price', 'hide_addcart', 'hide_addwishlist', 'hide_addcompare', 'tab_font_bold', 'tab_italic', 'tab_uppercase', 'hide_nav'];
                $data = $this->reInitData($data, $dataInit);
                $categories = '';
                if (isset($data['setting']['category_id'])) {
                    $categories = implode(',', $data['setting']['category_id']);
                }
                if ($data['setting']['template']=='list.phtml') {
                    $data['setting']['use_tabs'] = 0;
                }
                if ($data['setting']['use_tabs']) {
                    $template = 'products/category-tabs.phtml';
                } else {
                    $template = 'products/'.$data['setting']['template'];
                }

                $content = '{{block class="MGS\Fbuilder\Block\Products\Attributes" block_type="attribute" attribute="'.$data['setting']['attribute'].'" limit="'.$data['setting']['limit'].'" category_ids="'.$categories.'" use_slider="'.$data['setting']['use_slider'].'" hide_name="'.$data['setting']['hide_name'].'" hide_review="'.$data['setting']['hide_review'].'" hide_price="'.$data['setting']['hide_price'].'" hide_addcart="'.$data['setting']['hide_addcart'].'" hide_addwishlist="'.$data['setting']['hide_addwishlist'].'" hide_addcompare="'.$data['setting']['hide_addcompare'].'"';

                if ($data['setting']['template']=='list.phtml') {
                    $content .= ' numbercol="'.$data['setting']['numbercol'].'" percol="'.$data['setting']['percol'].'"';
                }

                if ($data['setting']['template']=='grid.phtml') {
                    $content .= ' perrow="'.$data['setting']['perrow'].'" perrow_tablet="'.$data['setting']['perrow_tablet'].'" perrow_mobile="'.$data['setting']['perrow_mobile'].'"';
                }

                if ($data['setting']['use_slider']) {
                    $content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" hide_nav="'.$data['setting']['hide_nav'].'" nav_top="'.$data['setting']['nav_top'].'" navigation_position="'.$data['setting']['navigation_position'].'" pagination_position="'.$data['setting']['pagination_position'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'" slide_by="'.$data['setting']['slide_by'].'" loop="'.$data['setting']['loop'].'" rtl="'.$data['setting']['rtl'].'" slide_margin="'.$data['setting']['slide_margin'].'"';
                }

                if ($data['setting']['use_tabs']) {
                    $content .= ' tab_style="'.$data['setting']['tab_style'].'"';
                    if ($data['setting']['tab_font_bold']) {
                        $content .= ' tab_font_bold="1"';
                    }
                    if ($data['setting']['tab_italic']) {
                        $content .= ' tab_italic="1"';
                    }
                    if ($data['setting']['tab_uppercase']) {
                        $content .= ' tab_uppercase="1"';
                    }
                    $content .= ' tab_align="'.$data['setting']['tab_align'].'"';

                    $data['custom_style_temp']['tab-style'] = ['tab-'.$data['setting']['tab_style']=>[
                            'font-size' => $data['setting']['font_size'],
                            'primary-color' => $data['setting']['tab_primary_color'],
                            'secondary-color' => $data['setting']['tab_secondary_color'],
                            'third-color' => $data['setting']['tab_third_color']
                        ]
                    ];
                }

                $content .= ' template="'.$template.'"}}';

                $data['block_content'] = $content;

                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Products by Attribute" block. Please wait for page reload.');
                break;

                /* Sale Products Block */
            case "sale":
                $dataInit = ['use_slider', 'autoplay', 'stop_auto', 'rtl', 'navigation', 'loop', 'pagination', 'use_tabs', 'hide_name', 'hide_review', 'hide_price', 'hide_addcart', 'hide_addwishlist', 'hide_addcompare', 'tab_font_bold', 'tab_italic', 'tab_uppercase', 'hide_nav'];
                $data = $this->reInitData($data, $dataInit);
                $categories = '';
                if (isset($data['setting']['category_id'])) {
                    $categories = implode(',', $data['setting']['category_id']);
                }
                if ($data['setting']['template']=='list.phtml') {
                    $data['setting']['use_tabs'] = 0;
                }
                if ($data['setting']['use_tabs']) {
                    $template = 'products/category-tabs.phtml';
                } else {
                    $template = 'products/'.$data['setting']['template'];
                }

                $content = '{{block class="MGS\Fbuilder\Block\Products\Sale" block_type="sale" limit="'.$data['setting']['limit'].'" category_ids="'.$categories.'" use_slider="'.$data['setting']['use_slider'].'" hide_name="'.$data['setting']['hide_name'].'" hide_review="'.$data['setting']['hide_review'].'" hide_price="'.$data['setting']['hide_price'].'" hide_addcart="'.$data['setting']['hide_addcart'].'" hide_addwishlist="'.$data['setting']['hide_addwishlist'].'" hide_addcompare="'.$data['setting']['hide_addcompare'].'"';

                if ($data['setting']['template']=='list.phtml') {
                    $content .= ' numbercol="'.$data['setting']['numbercol'].'" percol="'.$data['setting']['percol'].'"';
                }

                if ($data['setting']['template']=='grid.phtml') {
                    $content .= ' perrow="'.$data['setting']['perrow'].'" perrow_tablet="'.$data['setting']['perrow_tablet'].'" perrow_mobile="'.$data['setting']['perrow_mobile'].'"';
                }

                if ($data['setting']['use_slider']) {
                    $content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" hide_nav="'.$data['setting']['hide_nav'].'" nav_top="'.$data['setting']['nav_top'].'" navigation_position="'.$data['setting']['navigation_position'].'" pagination_position="'.$data['setting']['pagination_position'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'" slide_by="'.$data['setting']['slide_by'].'" loop="'.$data['setting']['loop'].'" rtl="'.$data['setting']['rtl'].'" slide_margin="'.$data['setting']['slide_margin'].'"';
                }

                if ($data['setting']['use_tabs']) {
                    $content .= ' tab_style="'.$data['setting']['tab_style'].'"';
                    if ($data['setting']['tab_font_bold']) {
                        $content .= ' tab_font_bold="1"';
                    }
                    if ($data['setting']['tab_italic']) {
                        $content .= ' tab_italic="1"';
                    }
                    if ($data['setting']['tab_uppercase']) {
                        $content .= ' tab_uppercase="1"';
                    }
                    $content .= ' tab_align="'.$data['setting']['tab_align'].'"';

                    $data['custom_style_temp']['tab-style'] = ['tab-'.$data['setting']['tab_style']=>[
                            'font-size' => $data['setting']['font_size'],
                            'primary-color' => $data['setting']['tab_primary_color'],
                            'secondary-color' => $data['setting']['tab_secondary_color'],
                            'third-color' => $data['setting']['tab_third_color']
                        ]
                    ];
                }

                $content .= ' template="'.$template.'"}}';

                $data['block_content'] = $content;

                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Sale Products" block. Please wait for page reload.');
                break;

                /* Top Rate Products Block */
            case "rate":
                $dataInit = ['use_slider', 'autoplay', 'stop_auto', 'rtl', 'navigation', 'loop', 'pagination', 'use_tabs', 'hide_name', 'hide_review', 'hide_price', 'hide_addcart', 'hide_addwishlist', 'hide_addcompare', 'tab_font_bold', 'tab_italic', 'tab_uppercase', 'hide_nav'];
                $data = $this->reInitData($data, $dataInit);
                $categories = '';
                if (isset($data['setting']['category_id'])) {
                    $categories = implode(',', $data['setting']['category_id']);
                }
                if ($data['setting']['template']=='list.phtml') {
                    $data['setting']['use_tabs'] = 0;
                }
                if ($data['setting']['use_tabs']) {
                    $template = 'products/category-tabs.phtml';
                } else {
                    $template = 'products/'.$data['setting']['template'];
                }

                $content = '{{block class="MGS\Fbuilder\Block\Products\Rate" block_type="rate" limit="'.$data['setting']['limit'].'" category_ids="'.$categories.'" use_slider="'.$data['setting']['use_slider'].'" hide_name="'.$data['setting']['hide_name'].'" hide_review="'.$data['setting']['hide_review'].'" hide_price="'.$data['setting']['hide_price'].'" hide_addcart="'.$data['setting']['hide_addcart'].'" hide_addwishlist="'.$data['setting']['hide_addwishlist'].'" hide_addcompare="'.$data['setting']['hide_addcompare'].'"';

                if ($data['setting']['template']=='list.phtml') {
                    $content .= ' numbercol="'.$data['setting']['numbercol'].'" percol="'.$data['setting']['percol'].'"';
                }

                if ($data['setting']['template']=='grid.phtml') {
                    $content .= ' perrow="'.$data['setting']['perrow'].'" perrow_tablet="'.$data['setting']['perrow_tablet'].'" perrow_mobile="'.$data['setting']['perrow_mobile'].'"';
                }

                if ($data['setting']['use_slider']) {
                    $content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" hide_nav="'.$data['setting']['hide_nav'].'" nav_top="'.$data['setting']['nav_top'].'" navigation_position="'.$data['setting']['navigation_position'].'" pagination_position="'.$data['setting']['pagination_position'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'" slide_by="'.$data['setting']['slide_by'].'" loop="'.$data['setting']['loop'].'" rtl="'.$data['setting']['rtl'].'" slide_margin="'.$data['setting']['slide_margin'].'"';
                }

                if ($data['setting']['use_tabs']) {
                    $content .= ' tab_style="'.$data['setting']['tab_style'].'"';
                    if ($data['setting']['tab_font_bold']) {
                        $content .= ' tab_font_bold="1"';
                    }
                    if ($data['setting']['tab_italic']) {
                        $content .= ' tab_italic="1"';
                    }
                    if ($data['setting']['tab_uppercase']) {
                        $content .= ' tab_uppercase="1"';
                    }
                    $content .= ' tab_align="'.$data['setting']['tab_align'].'"';

                    $data['custom_style_temp']['tab-style'] = ['tab-'.$data['setting']['tab_style']=>[
                            'font-size' => $data['setting']['font_size'],
                            'primary-color' => $data['setting']['tab_primary_color'],
                            'secondary-color' => $data['setting']['tab_secondary_color'],
                            'third-color' => $data['setting']['tab_third_color']
                        ]
                    ];
                }

                $content .= ' template="'.$template.'"}}';

                $data['block_content'] = $content;

                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Top Rate Products" block. Please wait for page reload.');
                break;

                /* Category Products Block */
            case "category_products":
                $dataInit = ['use_slider', 'autoplay', 'stop_auto', 'rtl', 'navigation', 'loop', 'pagination', 'use_tabs', 'hide_name', 'hide_review', 'hide_price', 'hide_addcart', 'hide_addwishlist', 'hide_addcompare', 'tab_font_bold', 'tab_italic', 'tab_uppercase', 'hide_nav'];
                $data = $this->reInitData($data, $dataInit);
                $categories = '';
                if (isset($data['setting']['category_id'])) {
                    $categories = implode(',', $data['setting']['category_id']);
                }
                if ($data['setting']['template']=='list.phtml') {
                    $data['setting']['use_tabs'] = 0;
                }
                if ($data['setting']['use_tabs']) {
                    $template = 'products/category-tabs.phtml';
                } else {
                    $template = 'products/'.$data['setting']['template'];
                }

                $content = '{{block class="MGS\Fbuilder\Block\Products\Category" block_type="catproduct" limit="'.$data['setting']['limit'].'" category_ids="'.$categories.'" use_slider="'.$data['setting']['use_slider'].'" hide_name="'.$data['setting']['hide_name'].'" hide_review="'.$data['setting']['hide_review'].'" hide_price="'.$data['setting']['hide_price'].'" hide_addcart="'.$data['setting']['hide_addcart'].'" hide_addwishlist="'.$data['setting']['hide_addwishlist'].'" hide_addcompare="'.$data['setting']['hide_addcompare'].'"';

                if ($data['setting']['template']=='list.phtml') {
                    $content .= ' numbercol="'.$data['setting']['numbercol'].'" percol="'.$data['setting']['percol'].'"';
                }

                if ($data['setting']['template']=='grid.phtml') {
                    $content .= ' perrow="'.$data['setting']['perrow'].'" perrow_tablet="'.$data['setting']['perrow_tablet'].'" perrow_mobile="'.$data['setting']['perrow_mobile'].'"';
                }

                if ($data['setting']['use_slider']) {
                    $content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" hide_nav="'.$data['setting']['hide_nav'].'" nav_top="'.$data['setting']['nav_top'].'" navigation_position="'.$data['setting']['navigation_position'].'" pagination_position="'.$data['setting']['pagination_position'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'" slide_by="'.$data['setting']['slide_by'].'" loop="'.$data['setting']['loop'].'" rtl="'.$data['setting']['rtl'].'" slide_margin="'.$data['setting']['slide_margin'].'"';
                }

                if ($data['setting']['use_tabs']) {
                    $content .= ' tab_style="'.$data['setting']['tab_style'].'"';
                    if ($data['setting']['tab_font_bold']) {
                        $content .= ' tab_font_bold="1"';
                    }
                    if ($data['setting']['tab_italic']) {
                        $content .= ' tab_italic="1"';
                    }
                    if ($data['setting']['tab_uppercase']) {
                        $content .= ' tab_uppercase="1"';
                    }
                    $content .= ' tab_align="'.$data['setting']['tab_align'].'"';

                    $data['custom_style_temp']['tab-style'] = ['tab-'.$data['setting']['tab_style']=>[
                            'font-size' => $data['setting']['font_size'],
                            'primary-color' => $data['setting']['tab_primary_color'],
                            'secondary-color' => $data['setting']['tab_secondary_color'],
                            'third-color' => $data['setting']['tab_third_color']
                        ]
                    ];
                }

                $content .= ' template="'.$template.'"}}';

                $data['block_content'] = $content;

                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Category Products" block. Please wait for page reload.');
                break;

                /* Deals Block */
            case "deals":
                $dataInit = ['use_slider', 'autoplay', 'stop_auto', 'rtl', 'navigation', 'loop', 'pagination', 'use_tabs', 'hide_name', 'hide_review', 'hide_addcart', 'hide_addwishlist', 'hide_addcompare', 'tab_font_bold', 'tab_italic', 'tab_uppercase', 'hide_time', 'hide_saved_price', 'hide_discount', 'hide_nav'];
                $data = $this->reInitData($data, $dataInit);
                $categories = '';
                if (isset($data['setting']['category_id'])) {
                    $categories = implode(',', $data['setting']['category_id']);
                }
                if ($data['setting']['template']=='list.phtml') {
                    $data['setting']['use_tabs'] = 0;
                }
                if ($data['setting']['use_tabs']) {
                    $template = 'products/deals/category-tabs.phtml';
                } else {
                    $template = 'products/deals/'.$data['setting']['template'];
                }

                $content = '{{block class="MGS\Fbuilder\Block\Products\Deals" block_type="deals" limit="'.$data['setting']['limit'].'" category_ids="'.$categories.'" use_slider="'.$data['setting']['use_slider'].'" hide_name="'.$data['setting']['hide_name'].'" hide_review="'.$data['setting']['hide_review'].'" hide_addcart="'.$data['setting']['hide_addcart'].'" hide_addwishlist="'.$data['setting']['hide_addwishlist'].'" hide_addcompare="'.$data['setting']['hide_addcompare'].'" hide_time="'.$data['setting']['hide_time'].'" hide_discount="'.$data['setting']['hide_discount'].'" hide_saved_price="'.$data['setting']['hide_saved_price'].'"';

                if ($data['setting']['template']=='list.phtml') {
                    $content .= ' numbercol="'.$data['setting']['numbercol'].'" percol="'.$data['setting']['percol'].'"';
                }

                if ($data['setting']['template']=='grid.phtml') {
                    $content .= ' perrow="'.$data['setting']['perrow'].'" perrow_tablet="'.$data['setting']['perrow_tablet'].'" perrow_mobile="'.$data['setting']['perrow_mobile'].'"';
                }

                if ($data['setting']['use_slider']) {
                    $content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" hide_nav="'.$data['setting']['hide_nav'].'" nav_top="'.$data['setting']['nav_top'].'" navigation_position="'.$data['setting']['navigation_position'].'" pagination_position="'.$data['setting']['pagination_position'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'" slide_by="'.$data['setting']['slide_by'].'" loop="'.$data['setting']['loop'].'" rtl="'.$data['setting']['rtl'].'" slide_margin="'.$data['setting']['slide_margin'].'"';
                }

                if ($data['setting']['hide_discount'] != 1) {
                    $data['custom_style_temp']['discount-style'] = [
                        'discount-color' => $data['setting']['discount_color'],
                        'discount-background' => $data['setting']['discount_background'],
                        'discount-width' => $data['setting']['discount_width'],
                        'discount-font-size' => $data['setting']['discount_font_size']
                    ];
                }

                if ($data['setting']['hide_time'] != 1) {
                    $content .= ' fbuilder_days="'.$this->encodeHtml($data['setting']['days']).'" fbuilder_hours="'.$this->encodeHtml($data['setting']['hours']).'" fbuilder_minutes="'.$this->encodeHtml($data['setting']['minutes']).'" fbuilder_seconds="'.$this->encodeHtml($data['setting']['seconds']).'"';

                    $data['custom_style_temp']['deal-style'] = [
                        'width' => $data['setting']['time_width'],
                        'background-color' => $data['setting']['time_background'],
                        'number-font-size' => $data['setting']['number_font_size'],
                        'text-font-size' => $data['setting']['text_font_size'],
                        'number-color' => $data['setting']['number_color'],
                        'text-color' => $data['setting']['text_color']

                    ];
                }

                if ($data['setting']['hide_saved_price'] != 1) {
                    $content .= ' fbuilder_saved_text="'.$this->encodeHtml($data['setting']['saved_text']).'"';

                    $data['custom_style_temp']['saved-style'] = [
                        'save-font-size' => $data['setting']['saved_font_size'],
                        'saved-price-font-size' => $data['setting']['saved_price_font_size'],
                        'saved-color' => $data['setting']['saved_color'],
                        'saved-price-color' => $data['setting']['saved_price_color']

                    ];
                }


                if ($data['setting']['use_tabs']) {
                    $content .= ' tab_style="'.$data['setting']['tab_style'].'"';
                    if ($data['setting']['tab_font_bold']) {
                        $content .= ' tab_font_bold="1"';
                    }
                    if ($data['setting']['tab_italic']) {
                        $content .= ' tab_italic="1"';
                    }
                    if ($data['setting']['tab_uppercase']) {
                        $content .= ' tab_uppercase="1"';
                    }
                    $content .= ' tab_align="'.$data['setting']['tab_align'].'"';

                    $data['custom_style_temp']['tab-style'] = ['tab-'.$data['setting']['tab_style']=>[
                            'font-size' => $data['setting']['font_size'],
                            'primary-color' => $data['setting']['tab_primary_color'],
                            'secondary-color' => $data['setting']['tab_secondary_color'],
                            'third-color' => $data['setting']['tab_third_color']
                        ]
                    ];
                }

                $content .= ' template="'.$template.'"}}';

                $data['block_content'] = $content;

                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Deals" block. Please wait for page reload.');
                break;

                /* Product Tabs Block */
            case "tabs":
                if (isset($data['setting']['tabs']) && count($data['setting']['tabs'])>0) {
                    $tabs = $data['setting']['tabs'];

                    $dataInit = ['use_slider', 'autoplay', 'stop_auto', 'rtl', 'navigation', 'loop', 'pagination', 'hide_name', 'hide_review', 'hide_price', 'hide_addcart', 'hide_addwishlist', 'hide_addcompare', 'tab_font_bold', 'tab_italic', 'tab_uppercase', 'hide_nav'];
                    $data = $this->reInitData($data, $dataInit);
                    $categories = '';
                    if (isset($data['setting']['category_id'])) {
                        $categories = implode(',', $data['setting']['category_id']);
                    }

                    usort(
                        $tabs, function ($item1, $item2) {
                            if ($item1['position'] == $item2['position']) {
                                return 0;
                            }
                                return $item1['position'] < $item2['position'] ? -1 : 1;
                        }
                    );

                    $tabType = $tabLabel = [];
                    foreach ($tabs as $tab) {
                        $tabType[] = $tab['value'];
                        $tabLabel[] = $tab['label'];
                    }
                    $tabs = implode(',', $tabType);
                    $labels = implode(',', $tabLabel);

                    $content = '{{block class="MGS\Fbuilder\Block\Products\Tabs" limit="'.$data['setting']['limit'].'" category_ids="'.$categories.'" use_slider="'.$data['setting']['use_slider'].'" hide_name="'.$data['setting']['hide_name'].'" hide_review="'.$data['setting']['hide_review'].'" hide_price="'.$data['setting']['hide_price'].'" hide_addcart="'.$data['setting']['hide_addcart'].'" hide_addwishlist="'.$data['setting']['hide_addwishlist'].'" hide_addcompare="'.$data['setting']['hide_addcompare'].'" tabs="'.$tabs.'" perrow="'.$data['setting']['perrow'].'" perrow_tablet="'.$data['setting']['perrow_tablet'].'" perrow_mobile="'.$data['setting']['perrow_mobile'].'"';

                    if ($data['setting']['use_slider']) {
                        $content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" hide_nav="'.$data['setting']['hide_nav'].'" nav_top="'.$data['setting']['nav_top'].'" navigation_position="'.$data['setting']['navigation_position'].'" pagination_position="'.$data['setting']['pagination_position'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'" slide_by="'.$data['setting']['slide_by'].'" loop="'.$data['setting']['loop'].'" rtl="'.$data['setting']['rtl'].'" slide_margin="'.$data['setting']['slide_margin'].'"';
                    }

                    $content .=' labels="'.$labels.'"';

                    $content .= ' tab_style="'.$data['setting']['tab_style'].'"';
                    if ($data['setting']['tab_font_bold']) {
                        $content .= ' tab_font_bold="1"';
                    }
                    if ($data['setting']['tab_italic']) {
                        $content .= ' tab_italic="1"';
                    }
                    if ($data['setting']['tab_uppercase']) {
                        $content .= ' tab_uppercase="1"';
                    }
                    $content .= ' tab_align="'.$data['setting']['tab_align'].'"';

                    $data['custom_style_temp']['tab-style'] = ['tab-'.$data['setting']['tab_style']=>[
                            'font-size' => $data['setting']['font_size'],
                            'primary-color' => $data['setting']['tab_primary_color'],
                            'secondary-color' => $data['setting']['tab_secondary_color'],
                            'third-color' => $data['setting']['tab_third_color']
                        ]
                    ];

                    $content .= ' template="products/tabs/view.phtml"}}';

                    $data['block_content'] = $content;
                    $result['message'] = 'success';
                    $sessionMessage = __('You saved the "Product Tabs" block. Please wait for page reload.');
                } else {
                    $result['message'] = __('You have not add any tabs.');
                }
                break;

                /* Single Product Block */
            case "special_product":
                $dataInit = ['hide_name', 'hide_review', 'hide_price', 'hide_addcart', 'hide_addwishlist', 'hide_addcompare', 'hide_description'];
                $data = $this->reInitData($data, $dataInit);

                $content = '{{block class="MGS\Fbuilder\Block\Products\SpecialProduct" product_id="'.$data['setting']['product_id'].'" hide_name="'.$data['setting']['hide_name'].'" hide_review="'.$data['setting']['hide_review'].'" hide_price="'.$data['setting']['hide_price'].'" hide_addcart="'.$data['setting']['hide_addcart'].'" hide_addwishlist="'.$data['setting']['hide_addwishlist'].'" hide_addcompare="'.$data['setting']['hide_addcompare'].'" hide_description="'.$data['setting']['hide_description'].'" truncate="'.$data['setting']['truncate'].'"';

                $content .= ' template="products/single/default.phtml"}}';

                $data['block_content'] = $content;
                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Single Product" block. Please wait for page reload.');
                break;
                /* Facebook Fan Box Block */
            case "facebook":
                $dataInit = ['hide_cover', 'show_facepile', 'hide_call_to', 'small_header', 'fit_inside'];
                $data = $this->reInitData($data, $dataInit);
                $tabs = implode(',', $data['setting']['facebook_tabs']);
                $content = '{{block class="MGS\Fbuilder\Block\Social\Facebook" page_url="'.$data['setting']['page_url'].'" width="'.$data['setting']['width'].'" height="'.$data['setting']['height'].'" facebook_tabs="'.$tabs.'" hide_cover="'.$data['setting']['hide_cover'].'" show_facepile="'.$data['setting']['show_facepile'].'" small_header="'.$data['setting']['small_header'].'" fit_inside="'.$data['setting']['fit_inside'].'" hide_call_to="'.$data['setting']['hide_call_to'].'" template="widget/socials/facebook_fanbox.phtml"}}';

                $data['block_content'] = $content;
                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Facebook Fanbox" block. Please wait to reload page.');
                break;

                /* Twitter Fan Box Block */
            case "twitter":
                $content = '{{block class="MGS\Fbuilder\Block\Social\Twitter" page_url="'.$data['setting']['page_url'].'" width="'.$data['setting']['width'].'" height="'.$data['setting']['height'].'" theme="'.$data['setting']['theme'].'" default_link_color="'.$data['setting']['default_link_color'].'" language="'.$data['setting']['language'].'" template="widget/socials/twitter_timeline.phtml"}}';

                $data['block_content'] = $content;
                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Twitter Timeline" block. Please wait to reload page.');
                break;

                /* Instagram Block */
            case "instagram":
                $dataInit = ['link', 'use_slider', 'autoplay', 'stop_auto', 'rtl', 'loop', 'navigation', 'pagination', 'hide_nav'];
                $data = $this->reInitData($data, $dataInit);
                $content = '{{block class="MGS\Fbuilder\Block\Social\Instagram" link="'.$data['setting']['link'].'" perrow="'.$data['setting']['perrow'].'" perrow_tablet="'.$data['setting']['perrow_tablet'].'" perrow_mobile="'.$data['setting']['perrow_mobile'].'" use_slider="'.$data['setting']['use_slider'].'"';

                if ($data['setting']['use_slider']) {
                    $content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" hide_nav="'.$data['setting']['hide_nav'].'" nav_top="'.$data['setting']['nav_top'].'" navigation_position="'.$data['setting']['navigation_position'].'" pagination_position="'.$data['setting']['pagination_position'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'" slide_by="'.$data['setting']['slide_by'].'" rtl="'.$data['setting']['rtl'].'" slide_margin="'.$data['setting']['slide_margin'].'" loop="'.$data['setting']['loop'].'"';
                }

                $content .=  ' template="widget/socials/instagram.phtml"}}';

                $data['block_content'] = $content;
                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Instagram" block. Please wait to reload page.');
                break;

                /* Snapppt Block*/
            case "instagram_shop":
                $content = '{{block class="MGS\Fbuilder\Block\Social\Snapppt" template="widget/socials/snapppt.phtml"}}';

                $data['block_content'] = $content;
                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Instagram Shop" block. Please wait to reload page.');
                break;

                /* Category List */
            case "category_list":
                $dataInit = ['use_slider', 'autoplay', 'stop_auto', 'rtl', 'navigation', 'loop', 'pagination', 'show_category_name', 'show_product', 'show_icon', 'font_bold', 'font_italic', 'uppercase', 'other_font_bold', 'other_font_italic', 'other_uppercase', 'hide_nav'];
                $data = $this->reInitData($data, $dataInit);

                $categories = '';
                if (isset($data['setting']['category_id'])) {
                    $categories = implode(',', $data['setting']['category_id']);
                }

                $content = '{{block class="MGS\Fbuilder\Block\Widget\CategoryList" fbuilder_title="'.$this->encodeHtml($data['setting']['title']).'" category_ids="'.$categories.'"';

                if ($data['setting']['template']=='grid.phtml') {
                    $content .= ' use_slider="'.$data['setting']['use_slider'].'" perrow="'.$data['setting']['perrow'].'" perrow_tablet="'.$data['setting']['perrow_tablet'].'" perrow_mobile="'.$data['setting']['perrow_mobile'].'" show_category_name="'.$data['setting']['show_category_name'].'" show_product="'.$data['setting']['show_product'].'"';

                    if ($data['setting']['use_slider']) {
                        $content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" hide_nav="'.$data['setting']['hide_nav'].'" nav_top="'.$data['setting']['nav_top'].'" navigation_position="'.$data['setting']['navigation_position'].'" pagination_position="'.$data['setting']['pagination_position'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'" slide_by="'.$data['setting']['slide_by'].'" rtl="'.$data['setting']['rtl'].'" slide_margin="'.$data['setting']['slide_margin'].'" loop="'.$data['setting']['loop'].'"';
                    }

                    $data['custom_style_temp']['category-style'] = ['grid'=>[
                        'font-size' => $data['setting']['font_size'],
                        'other-font-size' => $data['setting']['other_font_size'],
                        'primary-color' => $data['setting']['primary_color'],
                        'secondary-color' => $data['setting']['secondary_color'],
                        'third-color' => $data['setting']['third_color']
                    ]];

                } else {
                    $content .= ' show_icon="'.$data['setting']['show_icon'].'"';

                    $data['custom_style_temp']['category-style'] = ['list'=>[
                        'font-size' => $data['setting']['font_size'],
                        'other-font-size' => $data['setting']['other_font_size'],
                        'primary-color' => $data['setting']['primary_color'],
                        'secondary-color' => $data['setting']['secondary_color'],
                        'third-color' => $data['setting']['third_color'],
                        'fourth-color' => $data['setting']['fourth_color'],
                        'fifth_color' => $data['setting']['fifth_color'],
                    ]];
                }

                if ($data['setting']['font_bold']) {
                    $content .= ' font_bold="1"';
                }
                if ($data['setting']['font_italic']) {
                    $content .= ' font_italic="1"';
                }
                if ($data['setting']['uppercase']) {
                    $content .= ' uppercase="1"';
                }

                if ($data['setting']['other_font_bold']) {
                    $content .= ' other_font_bold="1"';
                }
                if ($data['setting']['other_font_italic']) {
                    $content .= ' other_font_italic="1"';
                }
                if ($data['setting']['other_uppercase']) {
                    $content .= ' other_uppercase="1"';
                }

                $content .=  ' template="widget/category/'.$data['setting']['template'].'"}}';

                $data['block_content'] = $content;
                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Category List" block. Please wait to reload page.');
                break;

                /* Accordion Block*/
            case "accordion":
                if (isset($data['setting']['accordion']) && count($data['setting']['accordion'])>0) {
                    $accordions = $data['setting']['accordion'];

                    $dataInit = ['collapse_all', 'title_font_bold', 'title_font_italic', 'title_uppercase', 'active_font_bold', 'active_font_italic', 'active_uppercase'];
                    $data = $this->reInitData($data, $dataInit);

                    usort(
                        $accordions, function ($item1, $item2) {
                            if ($item1['position'] == $item2['position']) {
                                return 0;
                            }
                                return $item1['position'] < $item2['position'] ? -1 : 1;
                        }
                    );

                    $accordionContent = $accordionLabel = [];
                    foreach ($accordions as $accordion) {
                        $accordionContent[] = $this->encodeHtml($accordion['content']);
                        $accordionLabel[] = $this->encodeHtml($accordion['label']);
                    }

                    $accordionData = implode(',', $accordionContent);
                    $labels = implode(',', $accordionLabel);

                    $content = '{{block class="Magento\Framework\View\Element\Template" accordion_content="'.$accordionData.'" accordion_label="'.$labels.'" collapse_all="'.$data['setting']['collapse_all'].'" accordion_icon="'.$data['setting']['accordion_icon'].'" icon_position="'.$data['setting']['icon_position'].'"';

                    $content .= ' template="MGS_Fbuilder::widget/accordion.phtml"}}';

                    $data['custom_style_temp']['accordion-style'] = [
                        'margin' => $data['setting']['accordion_margin'],
                        'padding' => $data['setting']['accordion_padding'],
                        'font-size' => $data['setting']['title_font_size'],
                        'height' => $data['setting']['title_height'],
                        'title-color' => $data['setting']['title_font_color'],
                        'title-background' => $data['setting']['title_background'],
                        'title-bold' => $data['setting']['title_font_bold'],
                        'title-italic' => $data['setting']['title_font_italic'],
                        'title-uppercase' => $data['setting']['title_uppercase'],
                        'active-color' => $data['setting']['active_font_color'],
                        'active-background' => $data['setting']['active_background'],
                        'active-bold' => $data['setting']['active_font_bold'],
                        'active-italic' => $data['setting']['active_font_italic'],
                        'active-uppercase' => $data['setting']['active_uppercase'],
                        'icon-color' => $data['setting']['icon_font_color'],
                        'icon-background' => $data['setting']['icon_background'],
                        'icon-size' => $data['setting']['icon_font_size'],
                        'icon-position' => $data['setting']['icon_position'],
                        'icon-type' => $data['setting']['accordion_icon'],
                    ];

                    $data['block_content'] = $content;
                    $result['message'] = 'success';
                    $sessionMessage = __('You saved the "Accordion" block. Please wait for page reload.');
                } else {
                    $result['message'] = __('You have not add any content for Accordion.');
                }
                break;

                /* Video Block*/
            case "video":
                $dataInit = ['full_width','autoplay','hide_info','hide_control','loop','mute'];
                $data = $this->reInitData($data, $dataInit);

                $content = '{{block class="MGS\Fbuilder\Block\Widget\Video" video_url="'.$data['setting']['video_url'].'" full_width="'.$data['setting']['full_width'].'" video_width="'.$data['setting']['video_width'].'" video_height="'.$data['setting']['video_height'].'" autoplay="'.$data['setting']['autoplay'].'" hide_info="'.$data['setting']['hide_info'].'" hide_control="'.$data['setting']['hide_control'].'" loop="'.$data['setting']['loop'].'" mute="'.$data['setting']['mute'].'"';

                $content .= ' template="widget/video.phtml"}}';


                $data['block_content'] = $content;
                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Video" block. Please wait for page reload.');

                break;

                /* Google Map Block*/
            case "map":
                $dataInit = ['location_address','location','address_box','wheel','navigation','type_control','scale','draggable','grayscale'];
                $data = $this->reInitData($data, $dataInit);

                if (isset($data['remove_pin_image']) && ($data['remove_pin_image']==1)) {
                    $filePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/fbuilder/map/') . $data['setting']['map_icon'];
                    if ($this->_file->isExists($filePath)) {
                        $this->_file->deleteFile($filePath);
                    }

                    $data['setting']['map_icon'] = '';
                }

                if (isset($files['pin_icon']) && $files['pin_icon']['name'] != '') {
                    try {

                        if (isset($data['setting']['map_icon']) && ($data['setting']['map_icon']!='')) {
                            $filePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/fbuilder/map') . $data['setting']['map_icon'];
                            if ($this->_file->isExists($filePath)) {
                                $this->_file->deleteFile($filePath);
                            }
                        }

                        $uploader = $this->uploadFile('pin_icon', 'map');
                        $data['setting']['map_icon'] = $uploader->getUploadedFileName();
                    } catch (\Exception $e) {
                        $result['message'] = $e->getMessage();
                    }
                }

                $content = '{{block class="MGS\Fbuilder\Block\Widget\Map" location_address="'.$data['setting']['location_address'].'" location="'.$data['setting']['location'].'" address_box="'.$data['setting']['address_box'].'" map_height="'.$data['setting']['map_height'].'" map_zoom="'.$data['setting']['map_zoom'].'" wheel="'.$data['setting']['wheel'].'" navigation="'.$data['setting']['navigation'].'" type_control="'.$data['setting']['type_control'].'" scale="'.$data['setting']['scale'].'" draggable="'.$data['setting']['draggable'].'" grayscale="'.$data['setting']['grayscale'].'" lat="'.$data['setting']['lat'].'" long="'.$data['setting']['long'].'" fbuilder_address="'.$this->encodeHtml($data['setting']['address']).'" map_icon="'.$data['setting']['map_icon'].'"';

                if ($data['setting']['address_box']) {
                    $content .= ' fbuilder_address_title="'.$this->encodeHtml($data['setting']['address_title']).'" fbuilder_line_one="'.$this->encodeHtml($data['setting']['line_one']).'" fbuilder_line_two="'.$this->encodeHtml($data['setting']['line_two']).'" fbuilder_line_three="'.$this->encodeHtml($data['setting']['line_three']).'" fbuilder_line_four="'.$this->encodeHtml($data['setting']['line_four']).'" fbuilder_line_five="'.$this->encodeHtml($data['setting']['line_five']).'"';

                    $data['custom_style_temp']['map-style'] = [
                        'background' => $data['setting']['box_background'],
                        'color' => $data['setting']['box_color'],
                        'width' => $data['setting']['box_width'],
                        'size' => $data['setting']['font_size'],
                        'title-size' => $data['setting']['title_font_size']
                    ];
                }

                $content .= ' template="widget/map.phtml"}}';


                $data['block_content'] = $content;
                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Google Map" block. Please wait for page reload.');

                break;

                /* Promo Banner Block*/
            case "promo_banner":
                $dataInit = [];
                $data = $this->reInitData($data, $dataInit);

                if (isset($files['image']) && $files['image']['name'] != '') {
                    try {
                        /* if(isset($data['setting']['banner_image']) && ($data['setting']['banner_image']!='')){
                            $filePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/fbuilder/promobanners') . $data['setting']['banner_image'];
                            if ($this->_file->isExists($filePath))  {
                                $this->_file->deleteFile($filePath);
                            }
                        } */

                        $uploader = $this->uploadFile('image', 'promobanners');
                        $data['setting']['banner_image'] = $uploader->getUploadedFileName();



                    } catch (\Exception $e) {
                        $result['message'] = $e->getMessage();
                    }
                }

                $content = '{{block class="MGS\Fbuilder\Block\Widget\PromoBanner" banner_image="'.$data['setting']['banner_image'].'" url="'.$data['setting']['url'].'" fbuilder_text_content="'.$this->encodeHtml($data['setting']['text_content']).'" fbuilder_button_text="'.$this->encodeHtml($data['setting']['button_text']).'" text_align="'.$data['setting']['text_align'].'" effect="'.$data['setting']['effect'].'"';

                $content .= ' template="widget/promobanner.phtml"}}';

                $data['custom_style_temp']['banner-style'] = [
                    'text-color' => $data['setting']['text_color'],
                    'button-background' => $data['setting']['button_background'],
                    'button-color' => $data['setting']['button_color'],
                    'button-border' => $data['setting']['button_border'],
                    'button-hover-background' => $data['setting']['button_hover_background'],
                    'button-hover-color' => $data['setting']['button_hover_color'],
                    'button-hover-border' => $data['setting']['button_hover_border']
                ];


                $data['block_content'] = $content;
                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Promo Banner" block. Please wait for page reload.');

                break;

                /* Profile Block*/
            case "profile":
                $dataInit = ['social_box_shadow'];
                $data = $this->reInitData($data, $dataInit);

                if (isset($files['photo']) && $files['photo']['name'] != '') {
                    try {

                        /* if(isset($data['setting']['profile_photo']) && ($data['setting']['profile_photo']!='')){
                            $filePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/fbuilder/profiles') . $data['setting']['profile_photo'];
                            if ($this->_file->isExists($filePath))  {
                                $this->_file->deleteFile($filePath);
                            }
                        } */

                        $uploader = $this->uploadFile('photo', 'profiles');
                        $data['setting']['profile_photo'] = $uploader->getUploadedFileName();
                    } catch (\Exception $e) {
                        $result['message'] = $e->getMessage();
                    }
                }

                $content = '{{block class="MGS\Fbuilder\Block\Widget\Profile" profile_photo="'.$data['setting']['profile_photo'].'" fbuilder_profile_name="'.$this->encodeHtml($data['setting']['profile_name']).'" fbuilder_subtitle="'.$this->encodeHtml($data['setting']['subtitle']).'" fbuilder_text_content="'.$this->encodeHtml($data['setting']['text_content']).'" email="'.$data['setting']['email'].'" facebook="'.$data['setting']['facebook'].'" twitter="'.$data['setting']['twitter'].'" linkedin="'.$data['setting']['linkedin'].'" social_box_shadow="'.$data['setting']['social_box_shadow'].'"';

                $content .= ' template="widget/profile/'.$data['setting']['style'].'.phtml"}}';

                $data['custom_style_temp']['profile-style'] = [
                    'name-font-size' => $data['setting']['name_font_size'],
                    'name-font-color' => $data['setting']['name_font_color'],
                    'subtitle-font-size' => $data['setting']['subtitle_font_size'],
                    'subtitle-font-color' => $data['setting']['subtitle_font_color'],
                    'subtitle-border-color' => $data['setting']['subtitle_border_color'],
                    'desc-font-size' => $data['setting']['desc_font_size'],
                    'desc-font-color' => $data['setting']['desc_font_color'],
                    'social-font-size' => $data['setting']['social_font_size'],
                    'social-box-width' => $data['setting']['social_box_width'],
                    'social-font-color' => $data['setting']['social_font_color'],
                    'social-background' => $data['setting']['social_background'],
                    'social-border' => $data['setting']['social_border'],
                    'social-hover-color' => $data['setting']['social_hover_color'],
                    'social-hover-background' => $data['setting']['social_hover_background'],
                    'social-hover-border' => $data['setting']['social_hover_border'],
                    'social-box-shadow' => $data['setting']['social_box_shadow']
                ];


                $data['block_content'] = $content;
                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Profile" block. Please wait for page reload.');

                break;

                /* Content Block*/
            case "content_box":
                $content = '{{block class="Magento\Framework\View\Element\Template" fbuilder_icon="'.$this->encodeHtml($data['setting']['icon']).'" fbuilder_subtitle="'.$this->encodeHtml($data['setting']['subtitle']).'" fbuilder_text_content="'.$this->encodeHtml($data['setting']['text_content']).'" style="'.$data['setting']['style'].'" link="'.$this->encodeHtml($data['setting']['link']).'"';

                $content .= ' template="MGS_Fbuilder::widget/content_box.phtml"}}';

                $data['custom_style_temp']['box-style'] = [
                    'icon_font_size' => $data['setting']['icon_font_size'],
                    'border' => $data['setting']['border'],
                    'border_hover' => $data['setting']['border_hover'],
                    'border_width' => $data['setting']['border_width'],
                    'width' => $data['setting']['width'],
                    'icon_color' => $data['setting']['icon_color'],
                    'icon_color_hover' => $data['setting']['icon_color_hover'],
                    'icon_background' => $data['setting']['icon_background'],
                    'icon_background_hover' => $data['setting']['icon_background_hover'],
                    'subtitle_font_size' => $data['setting']['subtitle_font_size'],
                    'subtitle_font_color' => $data['setting']['subtitle_font_color'],
                    'subtitle_color_hover' => $data['setting']['subtitle_color_hover'],
                    'desc_font_size' => $data['setting']['desc_font_size'],
                    'style' => $data['setting']['style']
                ];


                $data['block_content'] = $content;
                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Content Box" block. Please wait for page reload.');

                break;

                /* Counter Block*/
            case "counter_box":
                $content = '{{block class="Magento\Framework\View\Element\Template" fbuilder_icon="'.$this->encodeHtml($data['setting']['icon']).'" fbuilder_subtitle="'.$this->encodeHtml($data['setting']['subtitle']).'" fbuilder_text_content="'.$this->encodeHtml($data['setting']['text_content']).'" style="'.$data['setting']['style'].'" icon_font_size="'.$data['setting']['icon_font_size'].'" border="'.$data['setting']['border'].'" border_width="'.$data['setting']['border_width'].'" width="'.$data['setting']['width'].'" icon_color="'.$data['setting']['icon_color'].'" icon_color="'.$data['setting']['icon_color'].'" icon_background="'.$data['setting']['icon_background'].'" subtitle_font_size="'.$data['setting']['subtitle_font_size'].'" subtitle_font_color="'.$data['setting']['subtitle_font_color'].'" desc_font_size="'.$data['setting']['desc_font_size'].'" box_border="'.$data['setting']['box_border'].'" number_color="'.$data['setting']['number_color'].'" number_font_size="'.$data['setting']['number_font_size'].'" number_from="'.$data['setting']['number_from'].'" number_to="'.$data['setting']['number_to'].'" duration="'.$data['setting']['duration'].'" separators="'.$data['setting']['separators'].'" template="MGS_Fbuilder::widget/counter_box.phtml"}}';

                $data['block_content'] = $content;
                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Counter Box" block. Please wait for page reload.');

                break;

                /* Countdown Box*/
            case "countdown_box":
                $dataInit = ['date_fontweight','text_fontweight'];
                $data = $this->reInitData($data, $dataInit);
                $content = '{{block class="Magento\Framework\View\Element\Template" fbuilder_days="'.$this->encodeHtml($data['setting']['days']).'" fbuilder_coundown_date="'.$this->encodeHtml($data['setting']['coundown_date']).'" fbuilder_hours="'.$this->encodeHtml($data['setting']['hours']).'" fbuilder_minutes="'.$this->encodeHtml($data['setting']['minutes']).'" fbuilder_seconds="'.$this->encodeHtml($data['setting']['seconds']).'" date_fontweight="'.$data['setting']['date_fontweight'].'" text_fontweight="'.$data['setting']['text_fontweight'].'" position="'.$data['setting']['position'].'" template="MGS_Fbuilder::widget/countdown_box.phtml"}}';

                $data['custom_style_temp']['countdown-style'] = [
                    'date_font_size' => $data['setting']['date_font_size'],
                    'date_fontweight' => $data['setting']['date_fontweight'],
                    'date_color' => $data['setting']['date_color'],
                    'date_background' => $data['setting']['date_background'],
                    'date_border' => $data['setting']['date_border'],
                    'date_border_size' => $data['setting']['date_border_size'],
                    'date_border_radius' => $data['setting']['date_border_radius'],
                    'text_font_size' => $data['setting']['text_font_size'],
                    'text_color' => $data['setting']['text_color'],
                    'position' => $data['setting']['position']
                ];

                $data['block_content'] = $content;
                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Countdown Box" block. Please wait for page reload.');

                break;

                /* Progress bar Block*/
            case "progress_bar":
                $dataInit = ['box_animation','percent_fontweight','title_fontweight'];
                $data = $this->reInitData($data, $dataInit);
                $content = '{{block class="Magento\Framework\View\Element\Template" fbuilder_subtitle="'.$this->encodeHtml($data['setting']['subtitle']).'" percent="'.$data['setting']['percent'].'" percent_font_size="'.$data['setting']['percent_font_size'].'" percent_color="'.$data['setting']['percent_color'].'" percent_background="'.$data['setting']['percent_background'].'" percent_fontweight="'.$data['setting']['percent_fontweight'].'" title_font_size="'.$data['setting']['title_font_size'].'" title_color="'.$data['setting']['title_color'].'" title_fontweight="'.$data['setting']['title_fontweight'].'" bar_background="'.$data['setting']['bar_background'].'" progress_background="'.$data['setting']['progress_background'].'" bar_height="'.$data['setting']['bar_height'].'" border_radius="'.$data['setting']['border_radius'].'" box_shadow="'.$data['setting']['box_shadow'].'" box_animation="'.$data['setting']['box_animation'].'" position="'.$data['setting']['position'].'" template="MGS_Fbuilder::widget/progress_bar.phtml"}}';

                $data['block_content'] = $content;
                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Progress Bar" block. Please wait for page reload.');

                break;

                /* Progress circle Block*/
            case "progress_circle":
                $dataInit = ['show_icon','percent_fontweight','title_fontweight'];
                $data = $this->reInitData($data, $dataInit);
                $content = '{{block class="Magento\Framework\View\Element\Template" fbuilder_subtitle="'.$this->encodeHtml($data['setting']['subtitle']).'" percent="'.$data['setting']['percent'].'" percent_font_size="'.$data['setting']['percent_font_size'].'" percent_color="'.$data['setting']['percent_color'].'" percent_fontweight="'.$data['setting']['percent_fontweight'].'" title_font_size="'.$data['setting']['title_font_size'].'" title_color="'.$data['setting']['title_color'].'" title_fontweight="'.$data['setting']['title_fontweight'].'" circle_type="'.$data['setting']['circle_type'].'" circle_width="'.$data['setting']['circle_width'].'" show_icon="'.$data['setting']['show_icon'].'" fbuilder_icon="'.$this->encodeHtml($data['setting']['icon']).'" icon_color="'.$data['setting']['icon_color'].'" icon_font_size="'.$data['setting']['icon_font_size'].'" progress_width="'.$data['setting']['progress_width'].'" middle_background="'.$data['setting']['middle_background'].'" bar_background="'.$data['setting']['bar_background'].'" progress_background="'.$data['setting']['progress_background'].'" template="MGS_Fbuilder::widget/progress_circle.phtml"}}';

                $data['block_content'] = $content;
                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Progress Circle" block. Please wait for page reload.');

                break;

                /* Divider Block*/
            case "divider":
                $dataInit = ['show_text','show_icon','text_fontweight'];
                $data = $this->reInitData($data, $dataInit);
                $content = '{{block class="Magento\Framework\View\Element\Template" fbuilder_subtitle="'.$this->encodeHtml($data['setting']['subtitle']).'" width="'.$data['setting']['width'].'" style="'.$data['setting']['style'].'" border_align="'.$data['setting']['border_align'].'" show_text="'.$data['setting']['show_text'].'" text_fontweight="'.$data['setting']['text_fontweight'].'" show_icon="'.$data['setting']['show_icon'].'" fbuilder_icon="'.$this->encodeHtml($data['setting']['icon']).'" template="MGS_Fbuilder::widget/divider.phtml"}}';

                $data['custom_style_temp']['divider-style'] = [
                    'border_width' => $data['setting']['border_width'],
                    'border_color' => $data['setting']['border_color'],
                    'show_text' => $data['setting']['show_text'],
                    'text_font_size' => $data['setting']['text_font_size'],
                    'text_color' => $data['setting']['text_color'],
                    'text_background' => $data['setting']['text_background'],
                    'text_padding' => $data['setting']['text_padding'],
                    'style' => $data['setting']['style'],
                    'show_icon' => $data['setting']['show_icon'],
                    'icon_font_size' => $data['setting']['icon_font_size'],
                    'icon_color' => $data['setting']['icon_color'],
                    'icon_background' => $data['setting']['icon_background'],
                    'icon_border' => $data['setting']['icon_border'],
                    'icon_padding' => $data['setting']['icon_padding']
                ];

                $data['block_content'] = $content;
                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Divider" block. Please wait for page reload.');

                break;

                /* Divider Block*/
            case "chart":
                $content = '';
                if ($data['setting']['chart_type']=='line' || $data['setting']['chart_type']=='bar' || $data['setting']['chart_type']=='radar') {
                    if (isset($data['setting']['timeline']) && count($data['setting']['timeline'])>0) {
                        $timeline = $data['setting']['timeline'];
                        $timelineArr = [];
                        foreach ($timeline as $_timeline) {
                            $timelineArr[] = $this->encodeHtml($_timeline['label']);
                        }

                        $timelineLabels = implode(',', $timelineArr);
                        $items = json_encode($data['setting']['item']);

                        $content = '{{block class="MGS\Fbuilder\Block\Widget\Chart" chart_type="'.$data['setting']['chart_type'].'" chart_width="'.$data['setting']['chart_width'].'" fbuilder_timeline_label="'.$this->noSpace($timelineLabels).'" fbuilder_chart_item="'.$this->noSpace($this->encodeHtml($items)).'" template="MGS_Fbuilder::widget/chart.phtml"}}';

                        $result['message'] = 'success';
                    } else {
                        $result['message'] = __('No timeline to create chart.');
                    }
                } else {
                    if (isset($data['setting']['segment']) && count($data['setting']['segment'])>0) {
                        $segments = json_encode($data['setting']['segment']);

                        $content = '{{block class="MGS\Fbuilder\Block\Widget\Chart" chart_type="'.$data['setting']['chart_type'].'" chart_width="'.$data['setting']['chart_width'].'" fbuilder_segment="'.$this->noSpace($this->encodeHtml($segments)).'" template="MGS_Fbuilder::widget/chart.phtml"}}';

                        $result['message'] = 'success';
                    } else {
                        $result['message'] = __('No segment to create chart.');
                    }
                }

                $data['block_content'] = $content;

                $sessionMessage = __('You saved the "Chart" block. Please wait for page reload.');

                break;

                /* Heading Block*/
            case "heading":
                $dataInit = ['heading_fontweight', 'heading_italic', 'heading_uppercase', 'show_border'];
                $data = $this->reInitData($data, $dataInit);
                $content = '{{block class="Magento\Framework\View\Element\Template" heading="'.$data['setting']['heading'].'" fbuilder_subtitle="'.$this->encodeHtml($data['setting']['subtitle']).'" heading_align="'.$data['setting']['heading_align'].'" heading_fontweight="'.$data['setting']['heading_fontweight'].'" heading_italic="'.$data['setting']['heading_italic'].'" heading_uppercase="'.$data['setting']['heading_uppercase'].'" show_border="'.$data['setting']['show_border'].'" border_style="'.$data['setting']['border_style'].'" border_position="'.$data['setting']['border_position'].'" template="MGS_Fbuilder::widget/heading.phtml"}}';

                $data['custom_style_temp']['heading-style'] = [
                    'heading_font_size' => $data['setting']['heading_font_size'],
                    'heading_color' => $data['setting']['heading_color'],
                    'heading_background' => $data['setting']['heading_background'],
                    'show_border' => $data['setting']['show_border'],
                    'border_position' => $data['setting']['border_position'],
                    'border_color' => $data['setting']['border_color'],
                    'border_container_width' => $data['setting']['border_container_width'],
                    'border_width' => $data['setting']['border_width'],
                    'border_margin' => $data['setting']['border_margin']
                ];

                $data['block_content'] = $content;
                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Heading" block. Please wait for page reload.');
                break;

                /* List Block*/
            case "list":
                if (isset($data['setting']['accordion']) && count($data['setting']['accordion'])>0) {
                    $accordions = $data['setting']['accordion'];

                    $dataInit = ['fontweight', 'fontitalic'];
                    $data = $this->reInitData($data, $dataInit);

                    $accordionContent = $accordionLabel = [];
                    foreach ($accordions as $accordion) {
                        $accordionContent[] = $this->encodeHtml($accordion['content']);
                    }

                    $accordionData = implode(',', $accordionContent);

                    $content = '{{block class="Magento\Framework\View\Element\Template" accordion_content="'.$accordionData.'" list_type="'.$data['setting']['list_type'].'" fontweight="'.$data['setting']['fontweight'].'" fontitalic="'.$data['setting']['fontitalic'].'" list_style_type="'.$data['setting']['list_style_type'].'" fbuilder_icon="'.$this->encodeHtml($data['setting']['icon']).'" list_style="'.$data['setting']['list_style'].'"';

                    $content .= ' template="MGS_Fbuilder::widget/list.phtml"}}';

                    $data['custom_style_temp']['list-style'] = [
                        'font_size' => $data['setting']['font_size'],
                        'color' => $data['setting']['color'],
                        'margin_bottom' => $data['setting']['margin_bottom'],
                        'list_style_type' => $data['setting']['list_style_type'],
                        'icon_color' => $data['setting']['icon_color'],
                        'icon_font_size' => $data['setting']['icon_font_size'],
                        'icon_margin' => $data['setting']['icon_margin']

                    ];

                    $data['block_content'] = $content;
                    $result['message'] = 'success';
                    $sessionMessage = __('You saved the "List" block. Please wait for page reload.');
                } else {
                    $result['message'] = __('Have no item to save.');
                }
                break;

                /* Lookbook Block*/
            case "lookbook":
                $content = '{{widget type="MGS\Lookbook\Block\Widget\Lookbook" lookbook_id="'.$data['setting']['lookbook_id'].'" template="MGS_Lookbook::widget/lookbook.phtml"}}';

                $data['block_content'] = $content;
                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Lookbook" block. Please wait for page reload.');
                break;

                /* Lookbook Block*/
            case "lookbook_slider":
                $content = '{{widget type="MGS\Lookbook\Block\Widget\Slider" slider_id="'.$data['setting']['slide_id'].'" template="MGS_Lookbook::widget/slider.phtml"}}';

                $data['block_content'] = $content;
                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Lookbook Slider" block. Please wait for page reload.');
                break;

                /* Latest Post Block*/
            case "latest_post":
                $dataInit = ['use_slider', 'autoplay', 'stop_auto', 'rtl', 'navigation', 'loop', 'pagination', 'hide_thumbnail', 'hide_description', 'hide_create', 'hide_nav'];
                $data = $this->reInitData($data, $dataInit);
                $categories = '';

                if (isset($data['setting']['post_category'])) {
                    $categories = implode(',', $data['setting']['post_category']);
                } else {
                    $data['setting']['post_category'] = [];
                }

                $content = '{{block class="MGS\Fbuilder\Block\Widget\LatestPost" limit="'.$data['setting']['limit'].'" post_category="'.$categories.'" use_slider="'.$data['setting']['use_slider'].'" hide_thumbnail="'.$data['setting']['hide_thumbnail'].'" hide_description="'.$data['setting']['hide_description'].'" character_count="'.$data['setting']['character_count'].'" hide_create="'.$data['setting']['hide_create'].'"';

                if ($data['setting']['template']=='list.phtml') {
                    $content .= ' numbercol="'.$data['setting']['numbercol'].'" percol="'.$data['setting']['percol'].'"';
                }

                if ($data['setting']['template']=='grid.phtml') {
                    $content .= ' perrow="'.$data['setting']['perrow'].'" perrow_tablet="'.$data['setting']['perrow_tablet'].'" perrow_mobile="'.$data['setting']['perrow_mobile'].'"';
                }

                if ($data['setting']['use_slider']) {
                    $content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" hide_nav="'.$data['setting']['hide_nav'].'" nav_top="'.$data['setting']['nav_top'].'" navigation_position="'.$data['setting']['navigation_position'].'" pagination_position="'.$data['setting']['pagination_position'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'" slide_by="'.$data['setting']['slide_by'].'" loop="'.$data['setting']['loop'].'" rtl="'.$data['setting']['rtl'].'" slide_margin="'.$data['setting']['slide_margin'].'"';
                }

                $content .= ' template="MGS_Fbuilder::widget/blog/'.$data['setting']['template'].'"}}';

                $data['block_content'] = $content;

                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Latest Post" block. Please wait for page reload.');
                break;

                /* Portfolio Block*/
            case "portfolio":
                $dataInit = ['use_slider', 'autoplay', 'stop_auto', 'rtl', 'navigation', 'loop', 'pagination', 'hide_categories', 'hide_nav'];
                $data = $this->reInitData($data, $dataInit);
                $categories = '';
                if (isset($data['setting']['category_ids'])) {
                    $categories = implode(',', $data['setting']['category_ids']);
                } else {
                    $data['setting']['category_ids'] = [];
                }

                $content = '{{block class="MGS\Portfolio\Block\Widget" limit="'.$data['setting']['limit'].'" category_ids="'.$categories.'" use_slider="'.$data['setting']['use_slider'].'" hide_categories="'.$data['setting']['hide_categories'].'" perrow="'.$data['setting']['perrow'].'" perrow_tablet="'.$data['setting']['perrow_tablet'].'" perrow_mobile="'.$data['setting']['perrow_mobile'].'"';

                if ($data['setting']['use_slider']) {
                    $content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" hide_nav="'.$data['setting']['hide_nav'].'" nav_top="'.$data['setting']['nav_top'].'" navigation_position="'.$data['setting']['navigation_position'].'" pagination_position="'.$data['setting']['pagination_position'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'" slide_by="'.$data['setting']['slide_by'].'" loop="'.$data['setting']['loop'].'" rtl="'.$data['setting']['rtl'].'" slide_margin="'.$data['setting']['slide_margin'].'"';
                }

                $content .= ' template="MGS_Fbuilder::widget/portfolio.phtml"}}';

                $data['block_content'] = $content;

                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Portfolio" block. Please wait for page reload.');
                break;

                /* Testimonial Block*/
            case "testimonial":
                $dataInit = ['use_slider', 'autoplay', 'stop_auto', 'rtl', 'navigation', 'loop', 'pagination', 'hide_photo', 'hide_name', 'hide_info', 'content_italic', 'hide_nav'];
                $data = $this->reInitData($data, $dataInit);


                $content = '{{block class="MGS\Testimonial\Block\Testimonial" testimonials_count="'.$data['setting']['limit'].'" hide_photo="'.$data['setting']['hide_photo'].'" template_mode="'.$data['setting']['template'].'" hide_name="'.$data['setting']['hide_name'].'" hide_info="'.$data['setting']['hide_info'].'" content_italic="'.$data['setting']['content_italic'].'" use_slider="'.$data['setting']['use_slider'].'" perrow="'.$data['setting']['perrow'].'" perrow_tablet="'.$data['setting']['perrow_tablet'].'" perrow_mobile="'.$data['setting']['perrow_mobile'].'"';

                if ($data['setting']['use_slider']) {
                    $content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" hide_nav="'.$data['setting']['hide_nav'].'" nav_top="'.$data['setting']['nav_top'].'" navigation_position="'.$data['setting']['navigation_position'].'" pagination_position="'.$data['setting']['pagination_position'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'" slide_by="'.$data['setting']['slide_by'].'" loop="'.$data['setting']['loop'].'" rtl="'.$data['setting']['rtl'].'" slide_margin="'.$data['setting']['slide_margin'].'"';
                }

                $content .= ' template="MGS_Fbuilder::widget/tetimonials.phtml"}}';

                $data['custom_style_temp']['testimonial'] = [
                    'name_font_size' => $data['setting']['name_font_size'],
                    'name_color' => $data['setting']['name_color'],
                    'info_font_size' => $data['setting']['info_font_size'],
                    'info_color' => $data['setting']['info_color'],
                    'content_font_size' => $data['setting']['content_font_size'],
                    'content_color' => $data['setting']['content_color']
                ];

                $data['block_content'] = $content;

                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Testimonial" block. Please wait for page reload.');
                break;

                /* Image Block*/
            case "image":
                $dataInit = ['lightbox', 'box_shadown'];
                $data = $this->reInitData($data, $dataInit);

                if (isset($files['image']) && $files['image']['name'] != '') {
                    try {
                        $uploader = $this->uploadFile('image', 'images');
                        $data['setting']['image'] = $uploader->getUploadedFileName();
                    } catch (\Exception $e) {
                        $result['message'] = $e->getMessage();
                    }
                }
                if ($data['setting']['image_block_type']=='multiple') {
                    if (isset($files['image_after']) && $files['image_after']['name'] != '') {
                        try {
                            $uploader = $this->uploadFile('image_after', 'images');
                            $data['setting']['after_image'] = $uploader->getUploadedFileName();
                        } catch (\Exception $e) {
                            $result['message'] = $e->getMessage();
                        }
                    }
                }

                $content = '{{block class="Magento\Framework\View\Element\Template" type="'.$data['setting']['image_block_type'].'" image="'.$data['setting']['image'].'" box_shadown="'.$data['setting']['box_shadown'].'" url="'.$data['setting']['url'].'" alt="'.$data['setting']['alt'].'" alt_after="'.$data['setting']['alt_after'].'"';

                if ($data['setting']['image_block_type']=='multiple') {
                    $content .= ' after_image="'.$data['setting']['after_image'].'" multiple_effect="'.$data['setting']['multiple_effect'].'" slide_type="'.$data['setting']['slide_type'].'"';
                } else {
                    $content .= ' effect="'.$data['setting']['effect'].'" lightbox="'.$data['setting']['lightbox'].'" lightbox_group="'.$data['setting']['lightbox_group'].'"';


                }

                $data['custom_style_temp']['image'] = [
                    'border_width' => $data['setting']['border_width'],
                    'border_color' => $data['setting']['border_color'],
                    'border_radius' => $data['setting']['border_radius']
                ];

                $content .= ' template="MGS_Fbuilder::widget/image/'.$data['setting']['image_block_type'].'.phtml"}}';




                $data['block_content'] = $content;
                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Image" block. Please wait for page reload.');

                break;

                /* Button Block*/
            case "button":
                if (trim($data['setting']['subtitle'])=='') {
                    $result['message'] = __('Have no button to save.');
                } else {
                    $dataInit = ['use_border', 'border_top','border_right','border_bottom','border_left','use_icon','use_divider','box_shadow','full_width', 'button_bg_gradient'];
                    $data = $this->reInitData($data, $dataInit);

                    $content = '{{block class="Magento\Framework\View\Element\Template" fbuilder_subtitle="'.$this->encodeHtml($data['setting']['subtitle']).'" use_icon="'.$data['setting']['use_icon'].'" fbuilder_icon="'.$this->encodeHtml($data['setting']['icon']).'" icon_align="'.$data['setting']['icon_align'].'" use_divider="'.$data['setting']['use_divider'].'" box_shadow="'.$data['setting']['box_shadow'].'" button_align="'.$data['setting']['button_align'].'" full_width="'.$data['setting']['full_width'].'" button_link="'.$data['setting']['button_link'].'"';

                    $data['custom_style_temp']['button'] = [
                        'text_font_size' => $data['setting']['text_font_size'],
                        'text_color' => $data['setting']['text_color'],
                        'text_hover_color' => $data['setting']['text_hover_color'],
                        'button_bg_gradient' => $data['setting']['button_bg_gradient'],
                        'button_bg_color' => $data['setting']['button_bg_color'],
                        'button_bg_hover_color' => $data['setting']['button_bg_hover_color'],
                        'button_bg_from' => $data['setting']['button_bg_from'],
                        'button_bg_to' => $data['setting']['button_bg_to'],
                        'button_bg_orientation' => $data['setting']['button_bg_orientation'],
                        'button_bg_hover_from' => $data['setting']['button_bg_hover_from'],
                        'button_bg_hover_to' => $data['setting']['button_bg_hover_to'],
                        'button_bg_hover_orientation' => $data['setting']['button_bg_hover_orientation'],
                        'use_border' => $data['setting']['use_border'],
                        'border_color' => $data['setting']['border_color'],
                        'border_width' => $data['setting']['border_width'],
                        'border_hover_color' => $data['setting']['border_hover_color'],
                        'border_top' => $data['setting']['border_top'],
                        'border_right' => $data['setting']['border_right'],
                        'border_bottom' => $data['setting']['border_bottom'],
                        'border_left' => $data['setting']['border_left'],
                        'use_icon' => $data['setting']['use_icon'],
                        'icon' => $data['setting']['icon'],
                        'icon_font_size' => $data['setting']['icon_font_size'],
                        'icon_color' => $data['setting']['icon_color'],
                        'icon_hover_color' => $data['setting']['icon_hover_color'],
                        'icon_align' => $data['setting']['icon_align'],
                        'use_divider' => $data['setting']['use_divider'],
                        'divider_width' => $data['setting']['divider_width'],
                        'divider_color' => $data['setting']['divider_color'],
                        'divider_hover_color' => $data['setting']['divider_hover_color'],
                        'border_radius' => $data['setting']['border_radius'],
                        'full_width' => $data['setting']['full_width'],
                        'button_width' => $data['setting']['button_width'],
                        'button_height' => $data['setting']['button_height']
                    ];

                    $content .= ' template="MGS_Fbuilder::widget/button.phtml"}}';


                    $data['block_content'] = $content;
                    $result['message'] = 'success';
                    $sessionMessage = __('You saved the "Button" block. Please wait for page reload.');
                }

                break;

                /* Table Block*/
            case "table":
                if (trim($data['text_content'])=='') {
                    $result['message'] = __('Have no content to save.');
                } else {
                    $dataInit = ['fullwidth', 'border_vertical','border_horizontal','other_border_vertical','other_border_horizontal','heading_font_bold'];
                    $data = $this->reInitData($data, $dataInit);

                    $content = $data['text_content'];
                    $content = str_replace('<table class="mgs-table-block" ', '<table ', $content);
                    $content = str_replace('<table ', '<table class="mgs-table-block" ', $content);

                    $data['custom_style_temp']['table'] = [
                        'text_align' => $data['setting']['text_align'],
                        'border_color' => $data['setting']['border_color'],
                        'border_width' => $data['setting']['border_width'],
                        'text_color' => $data['setting']['text_color'],
                        'font_size' => $data['setting']['font_size'],
                        'row_height' => $data['setting']['row_height'],
                        'fullwidth' => $data['setting']['fullwidth'],
                        'heading_background' => $data['setting']['heading_background'],
                        'heading_text_color' => $data['setting']['heading_text_color'],
                        'heading_font_size' => $data['setting']['heading_font_size'],
                        'heading_font_bold' => $data['setting']['heading_font_bold'],
                        'heading_row_height' => $data['setting']['heading_row_height'],
                        'heading_border_color' => $data['setting']['heading_border_color'],
                        'heading_border_width' => $data['setting']['heading_border_width'],
                        'border_vertical' => $data['setting']['border_vertical'],
                        'border_horizontal' => $data['setting']['border_horizontal'],
                        'other_border_color' => $data['setting']['other_border_color'],
                        'other_border_width' => $data['setting']['other_border_width'],
                        'other_border_vertical' => $data['setting']['other_border_vertical'],
                        'other_border_horizontal' => $data['setting']['other_border_horizontal'],
                        'even_background' => $data['setting']['even_background'],
                        'odd_background' => $data['setting']['odd_background'],
                    ];


                    $data['block_content'] = $content;
                    $result['message'] = 'success';
                    $sessionMessage = __('You saved the "Table" block. Please wait for page reload.');
                }
                break;

                /* Masonry Block*/
            case "masonry":
                $dataInit = ['lightbox','box_shadow'];
                $data = $this->reInitData($data, $dataInit);

                if (isset($data['setting']['removeimg']) && count($data['setting']['removeimg'])>0) {
                    foreach ($data['setting']['removeimg'] as $imageName) {
                        $filePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('wysiwyg/masonry/') . $imageName;
                        if ($this->_file->isExists($filePath)) {
                            $this->_file->deleteFile($filePath);
                        }

                        if (isset($data['setting']['addimg']) && count($data['setting']['addimg'])>0) {
                            $key = array_search($imageName, array_combine(range(1, count($settings['addimg'])), array_values($settings['addimg'])));
                            if ($key) {
                                unset($data['setting']['addimg'][$key]);
                            }
                        }

                        if (isset($data['setting']['url']) && count($data['setting']['url'])>0) {
                            $keyUrl = array_search($imageName, array_combine(range(1, count($settings['url'])), array_values($settings['url'])));
                            if ($keyUrl) {
                                unset($data['setting']['url'][$key]);
                            }
                        }
                    }
                }

                if (isset($data['setting']['addimg']) && count($data['setting']['addimg'])>0) {

                    $images = implode(',', $data['setting']['addimg']);
                    $urls = implode(',', $data['setting']['url']);

                    $content = '{{block class="Magento\Framework\View\Element\Template" images="'.$images.'" links="'.$urls.'" column="'.$data['setting']['column'].'" item_margin="'.$data['setting']['item_margin'].'" lightbox="'.$data['setting']['lightbox'].'" box_shadow="'.$data['setting']['box_shadow'].'" effect="'.$data['setting']['effect'].'"  template="MGS_Fbuilder::widget/masonry.phtml"}}';

                    $data['custom_style_temp']['masonry'] = [
                        'border_color' => $data['setting']['border_color'],
                        'border_width' => $data['setting']['border_width'],
                        'border_radius' => $data['setting']['border_radius']
                    ];

                    $data['block_content'] = $content;
                    $result['message'] = 'success';
                    $sessionMessage = __('You saved the "Masonry" block. Please wait for page reload.');
                } else {
                    $result['message'] = __('Have no image to add to gallery.');
                }
                break;

                /* Tabs Block*/
            case "static_tabs":
                if (isset($data['setting']['accordion']) && count($data['setting']['accordion'])>0) {
                    $accordions = $data['setting']['accordion'];

                    $dataInit = ['tab_font_bold', 'tab_italic', 'tab_uppercase'];
                    $data = $this->reInitData($data, $dataInit);

                    usort(
                        $accordions, function ($item1, $item2) {
                            if ($item1['position'] == $item2['position']) {
                                return 0;
                            }
                                return $item1['position'] < $item2['position'] ? -1 : 1;
                        }
                    );

                    $accordionContent = $accordionLabel = [];
                    foreach ($accordions as $accordion) {
                        $accordionContent[] = $this->encodeHtml($accordion['content']);
                        $accordionLabel[] = $this->encodeHtml($accordion['label']);
                    }

                    $accordionData = implode(',', $accordionContent);
                    $labels = implode(',', $accordionLabel);

                    $content = '{{block class="Magento\Framework\View\Element\Template" accordion_content="'.$accordionData.'" accordion_label="'.$labels.'" tab_style="'.$data['setting']['tab_style'].'"';

                    if ($data['setting']['tab_font_bold']) {
                        $content .= ' tab_font_bold="1"';
                    }
                    if ($data['setting']['tab_italic']) {
                        $content .= ' tab_italic="1"';
                    }
                    if ($data['setting']['tab_uppercase']) {
                        $content .= ' tab_uppercase="1"';
                    }
                    $content .= ' tab_align="'.$data['setting']['tab_align'].'"';

                    $data['custom_style_temp']['tab-style'] = ['tab-'.$data['setting']['tab_style']=>[
                            'font-size' => $data['setting']['font_size'],
                            'primary-color' => $data['setting']['tab_primary_color'],
                            'secondary-color' => $data['setting']['tab_secondary_color'],
                            'third-color' => $data['setting']['tab_third_color']
                        ]
                    ];

                    $content .= ' template="MGS_Fbuilder::widget/static_tabs.phtml"}}';

                    $data['block_content'] = $content;
                    $result['message'] = 'success';
                    $sessionMessage = __('You saved the "Tabs" block. Please wait for page reload.');
                } else {
                    $result['message'] = __('Have no tabs to save.');
                }
                break;

                    /* Modal Popup Block*/
            case "modal_popup":
                if (trim($data['setting']['text_content'])=='') {
                    $result['message'] = __('Have no content for popup.');
                } elseif (trim($data['setting']['subtitle'])=='') {
                    $result['message'] = __('Have no button to save.');
                } else {
                    $dataInit = ['use_border', 'border_top','border_right','border_bottom','border_left','use_icon','use_divider','box_shadow','full_width', 'button_bg_gradient'];
                    $data = $this->reInitData($data, $dataInit);

                    $content = '{{block class="Magento\Framework\View\Element\Template" fbuilder_text_content="'.$this->encodeHtml($data['setting']['text_content']).'" fbuilder_subtitle="'.$this->encodeHtml($data['setting']['subtitle']).'" use_icon="'.$data['setting']['use_icon'].'" fbuilder_icon="'.$this->encodeHtml($data['setting']['icon']).'" icon_align="'.$data['setting']['icon_align'].'" use_divider="'.$data['setting']['use_divider'].'" box_shadow="'.$data['setting']['box_shadow'].'" button_align="'.$data['setting']['button_align'].'" full_width="'.$data['setting']['full_width'].'" generate_block_id="'.$data['setting']['generate_block_id'].'" fbuilder_button_text="'.$this->encodeHtml($data['setting']['button_text']).'"';

                    $data['custom_style_temp']['button'] = [
                        'text_font_size' => $data['setting']['text_font_size'],
                        'text_color' => $data['setting']['text_color'],
                        'text_hover_color' => $data['setting']['text_hover_color'],
                        'button_bg_gradient' => $data['setting']['button_bg_gradient'],
                        'button_bg_color' => $data['setting']['button_bg_color'],
                        'button_bg_hover_color' => $data['setting']['button_bg_hover_color'],
                        'button_bg_from' => $data['setting']['button_bg_from'],
                        'button_bg_to' => $data['setting']['button_bg_to'],
                        'button_bg_orientation' => $data['setting']['button_bg_orientation'],
                        'button_bg_hover_from' => $data['setting']['button_bg_hover_from'],
                        'button_bg_hover_to' => $data['setting']['button_bg_hover_to'],
                        'button_bg_hover_orientation' => $data['setting']['button_bg_hover_orientation'],
                        'use_border' => $data['setting']['use_border'],
                        'border_color' => $data['setting']['border_color'],
                        'border_width' => $data['setting']['border_width'],
                        'border_hover_color' => $data['setting']['border_hover_color'],
                        'border_top' => $data['setting']['border_top'],
                        'border_right' => $data['setting']['border_right'],
                        'border_bottom' => $data['setting']['border_bottom'],
                        'border_left' => $data['setting']['border_left'],
                        'use_icon' => $data['setting']['use_icon'],
                        'icon' => $data['setting']['icon'],
                        'icon_font_size' => $data['setting']['icon_font_size'],
                        'icon_color' => $data['setting']['icon_color'],
                        'icon_hover_color' => $data['setting']['icon_hover_color'],
                        'icon_align' => $data['setting']['icon_align'],
                        'use_divider' => $data['setting']['use_divider'],
                        'divider_width' => $data['setting']['divider_width'],
                        'divider_color' => $data['setting']['divider_color'],
                        'divider_hover_color' => $data['setting']['divider_hover_color'],
                        'border_radius' => $data['setting']['border_radius'],
                        'full_width' => $data['setting']['full_width'],
                        'button_width' => $data['setting']['button_width'],
                        'button_height' => $data['setting']['button_height']
                    ];

                    $data['custom_style_temp']['popup'] = [
                        'generate_block_id' => $data['setting']['generate_block_id'],
                        'popup_width' => $data['setting']['popup_width'],
                        'popup_background' => $data['setting']['popup_background'],
                        'popup_font_size' => $data['setting']['popup_font_size'],
                        'popup_color' => $data['setting']['popup_color'],
                        'popup_border_radius' => $data['setting']['popup_border_radius'],
                        'title_font_size' => $data['setting']['title_font_size'],
                        'title_color' => $data['setting']['title_color'],
                        'title_boder_color' => $data['setting']['title_boder_color'],
                        'title_border_size' => $data['setting']['title_border_size']
                    ];

                    $content .= ' template="MGS_Fbuilder::widget/modal_popup.phtml"}}';


                    $data['block_content'] = $content;
                    $result['message'] = 'success';
                    $sessionMessage = __('You saved the "Modal Popup" block. Please wait for page reload.');
                }

                break;

                /* Form Block*/
            case "form":
                if (isset($data['setting']['form']) && count($data['setting']['form'])>0) {

                    $dataInit = ['use_border', 'border_top','border_right','border_bottom','border_left','use_icon','use_divider','box_shadow','full_width', 'button_bg_gradient','use_mgs_captcha'];
                    $data = $this->reInitData($data, $dataInit);

                    $fields = $data['setting']['form'];

                    usort(
                        $fields, function ($item1, $item2) {
                            if ($item1['position'] == $item2['position']) {
                                return 0;
                            }
                                return $item1['position'] < $item2['position'] ? -1 : 1;
                        }
                    );

                    $content = '{{block class="MGS\Fbuilder\Block\Widget\Form" fields="'.$this->encodeHtml(json_encode($fields)).'" fbuilder_subtitle="'.$this->encodeHtml($data['setting']['subtitle']).'" use_icon="'.$data['setting']['use_icon'].'" fbuilder_icon="'.$this->encodeHtml($data['setting']['icon']).'" icon_align="'.$data['setting']['icon_align'].'" use_divider="'.$data['setting']['use_divider'].'" box_shadow="'.$data['setting']['box_shadow'].'" button_align="'.$data['setting']['button_align'].'" full_width="'.$data['setting']['full_width'].'" use_mgs_captcha="'.$data['setting']['use_mgs_captcha'].'" mgs_receive_email="'.$data['setting']['mgs_receive_email'].'" mgs_email_subject="'.$data['setting']['mgs_email_subject'].'" mgs_email_template_top="'.$this->encodeHtml($data['setting']['mgs_email_template_top']).'" mgs_email_template_bottom="'.$this->encodeHtml($data['setting']['mgs_email_template_bottom']).'" mgs_success_message="'.$this->encodeHtml($data['setting']['mgs_success_message']).'"';

                    $content .= ' template="MGS_Fbuilder::widget/form.phtml"}}';

                    $data['custom_style_temp']['button'] = [
                        'text_font_size' => $data['setting']['text_font_size'],
                        'text_color' => $data['setting']['text_color'],
                        'text_hover_color' => $data['setting']['text_hover_color'],
                        'button_bg_gradient' => $data['setting']['button_bg_gradient'],
                        'button_bg_color' => $data['setting']['button_bg_color'],
                        'button_bg_hover_color' => $data['setting']['button_bg_hover_color'],
                        'button_bg_from' => $data['setting']['button_bg_from'],
                        'button_bg_to' => $data['setting']['button_bg_to'],
                        'button_bg_orientation' => $data['setting']['button_bg_orientation'],
                        'button_bg_hover_from' => $data['setting']['button_bg_hover_from'],
                        'button_bg_hover_to' => $data['setting']['button_bg_hover_to'],
                        'button_bg_hover_orientation' => $data['setting']['button_bg_hover_orientation'],
                        'use_border' => $data['setting']['use_border'],
                        'border_color' => $data['setting']['border_color'],
                        'border_width' => $data['setting']['border_width'],
                        'border_hover_color' => $data['setting']['border_hover_color'],
                        'border_top' => $data['setting']['border_top'],
                        'border_right' => $data['setting']['border_right'],
                        'border_bottom' => $data['setting']['border_bottom'],
                        'border_left' => $data['setting']['border_left'],
                        'use_icon' => $data['setting']['use_icon'],
                        'icon' => $data['setting']['icon'],
                        'icon_font_size' => $data['setting']['icon_font_size'],
                        'icon_color' => $data['setting']['icon_color'],
                        'icon_hover_color' => $data['setting']['icon_hover_color'],
                        'icon_align' => $data['setting']['icon_align'],
                        'use_divider' => $data['setting']['use_divider'],
                        'divider_width' => $data['setting']['divider_width'],
                        'divider_color' => $data['setting']['divider_color'],
                        'divider_hover_color' => $data['setting']['divider_hover_color'],
                        'border_radius' => $data['setting']['border_radius'],
                        'full_width' => $data['setting']['full_width'],
                        'button_width' => $data['setting']['button_width'],
                        'button_height' => $data['setting']['button_height']
                    ];

                    $data['block_content'] = $content;
                    $result['message'] = 'success';
                    $sessionMessage = __('You saved the "Form" block. Please wait for page reload.');
                } else {
                    $result['message'] = __('Have no tabs to save.');
                }
                break;

                /* Lottie File */
            case "lottiefile":
                $dataInit = ['controls', 'autoplay', 'hover_play', 'loop'];
                $data = $this->reInitData($data, $dataInit);

                if (isset($files['json']) && $files['json']['name'] != '') {
                    try {
                        $uploader = $this->uploadFile('json', 'lottiefile');
                        $data['setting']['json_file'] = $uploader->getUploadedFileName();
                    } catch (\Exception $e) {
                        $result['message'] = $e->getMessage();
                    }
                }

                $content = '{{block class="Magento\Framework\View\Element\Template" json="'.$data['setting']['json_file'].'" width="'.$data['setting']['lottie_width'].'" height="'.$data['setting']['lottie_height'].'" mode="'.$data['setting']['play_mode'].'" direction="'.$data['setting']['direction'].'" background="'.$data['setting']['lottie_background_color'].'" speed="'.$data['setting']['lottie_animation_speed'].'" controls="'.$data['setting']['controls'].'" autoplay="'.$data['setting']['autoplay'].'" hover="'.$data['setting']['hover_play'].'" loop="'.$data['setting']['loop'].'" template="MGS_Fbuilder::widget/lottiefile.phtml"}}';

                $data['block_content'] = $content;
                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Lottie Animation" block. Please wait for page reload.');

                break;

                /* Mageplaza Banner Slider */
            case "mageplaza_slider":
                $dataInit = ['autoplay', 'stop_auto', 'navigation', 'pagination', 'loop', 'fullheight', 'rtl', 'hide_nav'];

                $data = $this->reInitData($data, $dataInit);
                $content = '{{block class="Mageplaza\BannerSlider\Block\Widget" slider_id="'.$data['setting']['slider_id'].'" autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" hide_nav="'.$data['setting']['hide_nav'].'" nav_top="'.$data['setting']['nav_top'].'" navigation_position="'.$data['setting']['navigation_position'].'" pagination_position="'.$data['setting']['pagination_position'].'" fullheight="'.$data['setting']['fullheight'].'" pagination="'.$data['setting']['pagination'].'" loop="'.$data['setting']['loop'].'" rtl="'.$data['setting']['rtl'].'" speed="'.$data['setting']['speed'].'" items="'.$data['setting']['items'].'" items_tablet="'.$data['setting']['items_tablet'].'" items_mobile="'.$data['setting']['items_mobile'].'" slide_margin="'.$data['setting']['slide_margin'].'" template="MGS_Fbuilder::widget/mageplaza/bannerslider.phtml"}}';

                $data['block_content'] = $content;
                $result['message'] = 'success';
                $sessionMessage = __('You saved the "Banner Slider" block. Please wait for page reload.');
                break;

                /* Mageplaza Blog Post */
            case "mageplaza_blog":
                $dataInit = ['use_slider', 'autoplay', 'stop_auto', 'rtl', 'navigation', 'loop', 'pagination', 'hide_nav'];
                $data = $this->reInitData($data, $dataInit);


                $content = '{{widget type="Mageplaza\Blog\Block\Widget\Posts" post_count="'.$data['setting']['limit'].'" show_type="'.$data['setting']['show_type'].'" use_slider="'.$data['setting']['use_slider'].'" perrow="'.$data['setting']['perrow'].'" perrow_tablet="'.$data['setting']['perrow_tablet'].'" perrow_mobile="'.$data['setting']['perrow_mobile'].'"';

                if ($data['setting']['show_type']=='category') {
                    $content .= ' category_id="'.$data['setting']['category_id'].'"';
                }

                if ($data['setting']['use_slider']) {
                    $content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" hide_nav="'.$data['setting']['hide_nav'].'" nav_top="'.$data['setting']['nav_top'].'" navigation_position="'.$data['setting']['navigation_position'].'" pagination_position="'.$data['setting']['pagination_position'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'" slide_by="'.$data['setting']['slide_by'].'" loop="'.$data['setting']['loop'].'" rtl="'.$data['setting']['rtl'].'" slide_margin="'.$data['setting']['slide_margin'].'"';
                }

                $content .= ' template="MGS_Fbuilder::widget/mageplaza/blog_posts.phtml"}}';

                $data['block_content'] = $content;

                $result['message'] = 'success';
                $sessionMessage = __('You saved "Blog posts" block. Please wait for page reload.');
                break;

                /* Mageplaza Countdown Timer */
            case "mageplaza_countdown":
                $content = '{{block class="Mageplaza\CountdownTimer\Block\Widget" rule_id ="'.$data['setting']['rule_id'].'"}}';

                $data['block_content'] = $content;

                $result['message'] = 'success';
                $sessionMessage = __('You saved Countdown Timer block. Please wait for page reload.');
                break;

                /* Mageplaza Daily Deal */
            case "mageplaza_deal":
                $dataInit = ['use_slider', 'autoplay', 'stop_auto', 'rtl', 'navigation', 'loop', 'pagination', 'hide_nav'];
                $data = $this->reInitData($data, $dataInit);


                $content = '{{block class="Mageplaza\DailyDeal\Block\Widget" limit="'.$data['setting']['limit'].'" type="'.$data['setting']['type'].'" perrow="'.$data['setting']['perrow'].'" use_slider="'.$data['setting']['use_slider'].'" perrow_tablet="'.$data['setting']['perrow_tablet'].'" perrow_mobile="'.$data['setting']['perrow_mobile'].'"';

                if ($data['setting']['use_slider']) {
                    $content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" hide_nav="'.$data['setting']['hide_nav'].'" nav_top="'.$data['setting']['nav_top'].'" navigation_position="'.$data['setting']['navigation_position'].'" pagination_position="'.$data['setting']['pagination_position'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'" slide_by="'.$data['setting']['slide_by'].'" loop="'.$data['setting']['loop'].'" rtl="'.$data['setting']['rtl'].'" slide_margin="'.$data['setting']['slide_margin'].'"';
                }

                $content .= ' template="MGS_Fbuilder::widget/mageplaza/deal.phtml"}}';

                $data['block_content'] = $content;

                $result['message'] = 'success';
                $sessionMessage = __('You saved "Countdown Timer" block. Please wait for page reload.');
                break;

                /* Mageplaza Shop by Brand */
            case "mageplaza_brand":
                $dataInit = ['use_slider', 'autoplay', 'stop_auto', 'rtl', 'navigation', 'loop', 'pagination', 'hide_nav'];
                $data = $this->reInitData($data, $dataInit);

                $type = $data['setting']['show_type'];

                $content = '{{block class="MGS\Fbuilder\Block\Mageplaza\Shopbybrand\\'.$type.'" perrow="'.$data['setting']['perrow'].'" use_slider="'.$data['setting']['use_slider'].'" perrow_tablet="'.$data['setting']['perrow_tablet'].'" perrow_mobile="'.$data['setting']['perrow_mobile'].'"';

                if ($type=='OptionId') {
                    if (isset($data['setting']['option_id']) && count($data['setting']['option_id'])>0) {
                        $options = implode(',', $data['setting']['option_id']);
                        $content .= ' option_id="'.$options.'"';
                    } else {
                        $result['message'] = __('Have no brand to display.');
                    }
                } elseif ($type=='CategoryId') {
                    if (isset($data['setting']['category_id']) && count($data['setting']['category_id'])>0) {
                        $categories = implode(',', $data['setting']['category_id']);
                        $content .= ' category_id="'.$categories.'"';
                    } else {
                        $result['message'] = __('Have no brand to display.');
                    }
                }

                if ($data['setting']['use_slider']) {
                    $content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" hide_nav="'.$data['setting']['hide_nav'].'" nav_top="'.$data['setting']['nav_top'].'" navigation_position="'.$data['setting']['navigation_position'].'" pagination_position="'.$data['setting']['pagination_position'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'" slide_by="'.$data['setting']['slide_by'].'" loop="'.$data['setting']['loop'].'" rtl="'.$data['setting']['rtl'].'" slide_margin="'.$data['setting']['slide_margin'].'"';
                }

                $content .= ' template="MGS_Fbuilder::widget/mageplaza/brand.phtml"}}';

                $data['block_content'] = $content;

                $result['message'] = 'success';
                $sessionMessage = __('You saved "Brand" block. Please wait for page reload.');
                break;

                /* Mageplaza Product Slider */
            case "mageplaza_productslider":
                $dataInit = ['use_slider', 'autoplay', 'stop_auto', 'rtl', 'navigation', 'loop', 'pagination', 'hide_nav'];
                $data = $this->reInitData($data, $dataInit);

                $type = $data['setting']['product_type'];

                $content = '{{block class="MGS\Fbuilder\Block\Mageplaza\Productslider" product_type="'.$type.'" products_count="'.$data['setting']['products_count'].'" perrow="'.$data['setting']['perrow'].'" use_slider="'.$data['setting']['use_slider'].'" perrow_tablet="'.$data['setting']['perrow_tablet'].'" perrow_mobile="'.$data['setting']['perrow_mobile'].'"';

                if ($data['setting']['use_slider']) {
                    $content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" hide_nav="'.$data['setting']['hide_nav'].'" nav_top="'.$data['setting']['nav_top'].'" navigation_position="'.$data['setting']['navigation_position'].'" pagination_position="'.$data['setting']['pagination_position'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'" slide_by="'.$data['setting']['slide_by'].'" loop="'.$data['setting']['loop'].'" rtl="'.$data['setting']['rtl'].'" slide_margin="'.$data['setting']['slide_margin'].'"';
                }

                $content .= ' template="MGS_Fbuilder::widget/mageplaza/product_slider.phtml"}}';

                $data['block_content'] = $content;

                $result['message'] = 'success';
                $sessionMessage = __('You saved "Product Slider" block. Please wait for page reload.');
                break;
            }
            if ($result['message']=='success') {
                $this->saveBlockData($data, $sessionMessage);
                $this->cacheManager->clean(['full_page']);
            } else {
                return $this->getMessageHtml('danger', $result['message'], false);
            }
        } else {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
    }

    public function uploadFile($field, $folder)
    {
        $uploader = $this->_fileUploaderFactory->create(['fileId' => $field]);
        if ($field=='json') {
            $uploader->setAllowedExtensions(['json']);
        } else {
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
        }

        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(true);

        $path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/fbuilder/'.$folder.'/');
        $uploader->save($path);
        return $uploader;
    }

    /* Save data to childs table */
    public function saveBlockData($data, $sessionMessage)
    {
        $model = $this->getModel('MGS\Fbuilder\Model\Child');
        $data['setting'] = json_encode($data['setting']);



        if (isset($data['remove_background']) && ($data['remove_background']==1) && isset($data['old_background'])) {
            $filePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/fbuilder/backgrounds') . $data['old_background'];
            if ($this->_file->isExists($filePath)) {
                $this->_file->deleteFile($filePath);
            }

            $data['background_image'] = '';
        }

        /* Update Image */
        if (isset($files['background_image']['name']) && $files['background_image']['name'] != '') {
            $uploader = $this->_fileUploaderFactory->create(['fileId' => 'background_image']);
            $file = $uploader->validateFile();

            if (($file['name']!='') && ($file['size'] >0)) {
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);

                $path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/fbuilder/backgrounds');
                $uploader->save($path);
                $data['background_image'] = $uploader->getUploadedFileName();
            }
        }

        if (!isset($data['child_id'])) {
            $storeId = $this->_storeManager->getStore()->getId();
            $data['store_id'] = $storeId;
            $data['position'] = $this->getNewPositionOfChild($data['store_id'], $data['block_name']);
        }

        if (isset($data['product_id'])) {
            if ($data['overriden']!=0) {
                $data['store_id'] = $this->_storeManager->getStore()->getId();
            } else {
                $data['store_id'] = 0;
            }
        }

        if (!isset($data['background_repeat'])) {
            $data['background_repeat'] = 0;
        }
        if (!isset($data['background_gradient'])) {
            $data['background_gradient'] = 0;
        }
        if (!isset($data['background_cover'])) {
            $data['background_cover'] = 0;
        }

        if (!isset($data['hide_desktop'])) {
            $data['hide_desktop'] = 0;
        }
        if (!isset($data['hide_tablet'])) {
            $data['hide_tablet'] = 0;
        }
        if (!isset($data['hide_mobile'])) {
            $data['hide_mobile'] = 0;
        }

        $model->setData($data);
        if (isset($data['child_id'])) {
            $id = $data['child_id'];
            unset($data['child_id']);
            $model->setId($id);
        }
        try {
            // save the data
            $model->save();

            $customStyle = '';
            if (isset($data['custom_style_temp']['tab-style'])) {
                //print_r($data['custom_style_temp']['tab-style']); die();
                foreach ($data['custom_style_temp']['tab-style'] as $tabStyle => $styleInfo) {
                    if (($styleInfo['font-size']!='') && ($styleInfo['font-size']>0)) {
                        $customStyle .= '.block'.$model->getId().' .mgs-tab.data.items > .item.title > .switch{font-size:'.$styleInfo['font-size'].'px;}';

                        if ($tabStyle=='tab-style1') {
                            $height = $styleInfo['font-size'] + 4;
                            $customStyle .= '.block'.$model->getId().' .mgs-tab.data.items > .item.title > .switch{height:'.$height.'px !important; line-height:'.$height.'px !important}';
                            $customStyle .= '.block'.$model->getId().' .mgs-product-tab .mgs-tab.data.items .item.title .switch::before{height: '.$height.'px; top:1px}';
                        }

                        if ($tabStyle=='tab-style2' || $tabStyle=='tab-style3') {
                            $borderRadius = $styleInfo['font-size'] + 10;
                            $customStyle .= '.block'.$model->getId().' .mgs-product-tab .'.$tabStyle.'.data.items .item.title .switch{border-radius:' . $borderRadius .'px;}';
                        }
                    }

                    if (($tabStyle=='tab-style1') || ($tabStyle=='tab-style2') || ($tabStyle=='tab-style4') || ($tabStyle=='tab-style5') || ($tabStyle=='tab-style7')) {
                        if ($styleInfo['third-color']!='') {
                            $customStyle .= '.block'.$model->getId().' .mgs-product-tab .'.$tabStyle.'.data.items .item.title .switch:before{background: '.$styleInfo['third-color'].';}';

                            $customStyle .= '@media (max-width:767px) {.mgs-product-tab .mgs-tab.data.items > .item.title > .switch{border:1px solid '.$styleInfo['third-color'].'}}';

                            if ($tabStyle=='tab-style2') {
                                $customStyle .= '.block'.$model->getId().' .mgs-product-tab .'.$tabStyle.'.data.items .item.title.active .switch{border-color: '.$styleInfo['third-color'].';}';
                            }

                            if ($tabStyle=='tab-style4') {
                                $customStyle .= '.block'.$model->getId().' .mgs-product-tab .'.$tabStyle.'.data.items .item.title.active .switch::after{background-color: '.$styleInfo['third-color'].';}';
                            }

                            if ($tabStyle=='tab-style5') {
                                $customStyle .= '.block'.$model->getId().' .mgs-product-tab .tab-style5.data.items > .item.content{border-color: '.$styleInfo['third-color'].';}';
                            }
                        }

                        if ($styleInfo['secondary-color']!='') {
                            $customStyle .= '.block'.$model->getId().' .mgs-product-tab .'.$tabStyle.'.data.items .item.title .switch{color: '.$styleInfo['secondary-color'].' !important;}';
                        }

                        if ($styleInfo['primary-color']!='') {
                            $customStyle .= '.block'.$model->getId().' .mgs-product-tab .'.$tabStyle.'.data.items .item.title.active .switch, .block'.$model->getId().' .mgs-product-tab .'.$tabStyle.'.data.items .item.title .switch:hover{color: '.$styleInfo['primary-color'].' !important}';

                            if ($tabStyle=='tab-style5') {
                                $customStyle .= '.block'.$model->getId().' .mgs-product-tab .'.$tabStyle.'.data.items .item.title.active .switch:after{background-color: '.$styleInfo['primary-color'].';}';
                            }
                        }


                    }

                    if ($tabStyle=='tab-style3') {
                        if ($styleInfo['third-color']!='') {
                            $customStyle .= '.block'.$model->getId().' .mgs-product-tab .'.$tabStyle.'.data.items .item.title .switch{border-color: '.$styleInfo['third-color'].'}';
                        }

                        if ($styleInfo['secondary-color']!='') {
                            $customStyle .= '.block'.$model->getId().' .mgs-product-tab .'.$tabStyle.'.data.items .item.title .switch{color: '.$styleInfo['secondary-color'].'}';
                        }

                        if ($styleInfo['primary-color']!='') {
                            $customStyle .= '.block'.$model->getId().' .mgs-product-tab .'.$tabStyle.'.data.items .item.title.active .switch,.block'.$model->getId().' .mgs-product-tab .'.$tabStyle.'.data.items .item.title .switch:hover{background-color: '.$styleInfo['primary-color'].' !important; border-color:'.$styleInfo['primary-color'].' !important}';
                        }
                    }

                    if ($tabStyle=='tab-style6') {
                        if ($styleInfo['third-color']!='') {
                            $customStyle .= '.block'.$model->getId().' .mgs-product-tab .'.$tabStyle.'.data.items .item.title .switch{border-color: '.$styleInfo['third-color'].'}';
                        }

                        if ($styleInfo['secondary-color']!='') {
                            $customStyle .= '.block'.$model->getId().' .mgs-product-tab .'.$tabStyle.'.data.items .item.title .switch{background-color: '.$styleInfo['secondary-color'].'}';
                        }

                        if ($styleInfo['primary-color']!='') {
                            $customStyle .= '.block'.$model->getId().' .mgs-product-tab .'.$tabStyle.'.data.items .item.title.active .switch,.block'.$model->getId().' .mgs-product-tab .'.$tabStyle.'.data.items .item.title .switch:hover{background-color: '.$styleInfo['primary-color'].' !important;}';
                        }
                    }
                }
            }

            if (isset($data['custom_style_temp']['deal-style'])) {
                $dealStyleInfo = $data['custom_style_temp']['deal-style'];

                if ($dealStyleInfo['width']!='') {
                    $customStyle .= '.block'.$model->getId().' .deal-timer .countdown,.block'.$model->getId().' .deal-timer .time-note{width:'.$dealStyleInfo['width'].'px}';
                }

                if ($dealStyleInfo['background-color']!='') {
                    $customStyle .= '.block'.$model->getId().' .deal-timer .countdown > span > b {background:'.$dealStyleInfo['background-color'].'; padding:10px 0; margin-bottom:3px}';
                }

                if ($dealStyleInfo['number-font-size']!='') {
                    $customStyle .= '.block'.$model->getId().' .deal-timer .countdown > span > b{font-size:'.$dealStyleInfo['number-font-size'].'px}';
                }

                if ($dealStyleInfo['number-color']!='') {
                    $customStyle .= '.block'.$model->getId().' .deal-timer .countdown > span > b{color:'.$dealStyleInfo['number-color'].'}';
                }

                if ($dealStyleInfo['text-font-size']!='') {
                    $customStyle .= '.block'.$model->getId().' .deal-timer .time-note span{font-size:'.$dealStyleInfo['text-font-size'].'px}';
                }

                if ($dealStyleInfo['text-color']!='') {
                    $customStyle .= '.block'.$model->getId().' .deal-timer .time-note span{color:'.$dealStyleInfo['text-color'].'}';
                }
            }

            if (isset($data['custom_style_temp']['discount-style'])) {
                $discountStyleInfo = $data['custom_style_temp']['discount-style'];

                if ($discountStyleInfo['discount-width']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-products .sale-ribbon{width:'.$discountStyleInfo['discount-width'].'px; height:'.$discountStyleInfo['discount-width'].'px; line-height:'.$discountStyleInfo['discount-width'].'px}';
                }

                if ($discountStyleInfo['discount-font-size']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-products .sale-ribbon span{font-size:'.$discountStyleInfo['discount-font-size'].'px}';
                }

                if ($discountStyleInfo['discount-color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-products .sale-ribbon span{color:'.$discountStyleInfo['discount-color'].'}';
                }

                if ($discountStyleInfo['discount-background']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-products .sale-ribbon{background:'.$discountStyleInfo['discount-background'].'}';
                }
            }

            if (isset($data['custom_style_temp']['saved-style'])) {
                $savedPriceStyleInfo = $data['custom_style_temp']['saved-style'];

                if ($savedPriceStyleInfo['save-font-size']!='') {
                    $customStyle .= '.block'.$model->getId().' .saved-price{font-size:'.$savedPriceStyleInfo['save-font-size'].'px}';
                }

                if ($savedPriceStyleInfo['saved-price-font-size']!='') {
                    $customStyle .= '.block'.$model->getId().' .saved-price .price{font-size:'.$savedPriceStyleInfo['saved-price-font-size'].'px !important}';
                }

                if ($savedPriceStyleInfo['saved-color']!='') {
                    $customStyle .= '.block'.$model->getId().' .saved-price{color:'.$savedPriceStyleInfo['saved-color'].'}';
                }
                if ($savedPriceStyleInfo['saved-price-color']!='') {
                    $customStyle .= '.block'.$model->getId().' .saved-price .price{color:'.$savedPriceStyleInfo['saved-price-color'].' !important}';
                }
            }

            if (isset($data['custom_style_temp']['category-style'])) {
                if (isset($data['custom_style_temp']['category-style']['grid'])) {
                    $savedStyle = $data['custom_style_temp']['category-style']['grid'];

                    if ($savedStyle['other-font-size']!='') {
                        $customStyle .= '.block'.$model->getId().' .category-grid-block .category-item .widget-category-infor .category-product-count{font-size:'.$savedStyle['other-font-size'].'px}';
                    }

                    if ($savedStyle['font-size']!='') {
                        $customStyle .= '.block'.$model->getId().' .category-grid-block .category-item .widget-category-infor .category-name{font-size:'.$savedStyle['font-size'].'px}';
                    }

                    if ($savedStyle['primary-color']!='') {
                        $customStyle .= '.block'.$model->getId().' .category-grid-block .category-item .widget-category-infor .category-name{color:'.$savedStyle['primary-color'].'}';
                    }

                    if (isset($savedStyle['fifth_color']) && $savedStyle['fifth_color']!='') {
                        $customStyle .= '.block'.$model->getId().' .category-grid-block .category-item .widget-category-infor .category-name:hover{color:'.$savedStyle['fifth_color'].'}';
                    }

                    if ($savedStyle['secondary-color']!='') {
                        $customStyle .= '.block'.$model->getId().' .category-grid-block .category-item .widget-category-infor .category-product-count{color:'.$savedStyle['secondary-color'].'}';
                    }

                    if ($savedStyle['third-color']!='') {
                        $customStyle .= '.block'.$model->getId().' .category-grid-block .category-item .widget-category-infor .category-product-count .number{color:'.$savedStyle['third-color'].'}';
                    }
                } else {
                    $savedStyle = $data['custom_style_temp']['category-style']['list'];

                    if ($savedStyle['other-font-size']!='') {
                        $customStyle .= '.block'.$model->getId().' .category-list-block .list-heading h3{font-size:'.$savedStyle['other-font-size'].'px}';
                    }

                    if ($savedStyle['font-size']!='') {
                        $customStyle .= '.block'.$model->getId().' .category-list-block ul li a{font-size:'.$savedStyle['font-size'].'px}';
                    }

                    if (isset($savedStyle['fifth_color']) && $savedStyle['fifth_color']!='') {
                        $customStyle .= '.block'.$model->getId().' .category-list-block ul li a:hover{color:'.$savedStyle['fifth_color'].'}';
                    }

                    if ($savedStyle['secondary-color']!='') {
                        $customStyle .= '.block'.$model->getId().' .category-list-block .list-heading h3{color:'.$savedStyle['secondary-color'].'}';
                    }

                    if ($savedStyle['third-color']!='') {
                        $customStyle .= '.block'.$model->getId().' .category-list-block .list-heading h3{background-color:'.$savedStyle['third-color'].'}';
                    }

                    if ($savedStyle['fourth-color']!='') {
                        $customStyle .= '.block'.$model->getId().' .category-list-block .list-heading h3,.block'.$model->getId().' .category-list-block ul li,.block'.$model->getId().' .category-list-block{border-color:'.$savedStyle['fourth-color'].'}';
                    }
                }
            }

            if (isset($data['custom_style_temp']['accordion-style'])) {
                $accordionStyle = $data['custom_style_temp']['accordion-style'];
                if ($accordionStyle['margin']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-accordion .accordion-title{margin-top:'.$accordionStyle['margin'].'px}';
                    $customStyle .= '.block'.$model->getId().' .mgs-accordion .accordion-title:first-child{margin-top:0}';
                }
                if ($accordionStyle['padding']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-accordion .accordion-content{padding:'.$accordionStyle['padding'].'}';
                }
                if ($accordionStyle['font-size']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-accordion .accordion-title{font-size:'.$accordionStyle['font-size'].'px}';
                }
                if ($accordionStyle['height']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-accordion .accordion-title,.block'.$model->getId().' .mgs-accordion .accordion-title::before{height:'.$accordionStyle['height'].'px; line-height:'.$accordionStyle['height'].'px}';
                    $customStyle .= '.block'.$model->getId().' .mgs-accordion .accordion-title::before{width:'.$accordionStyle['height'].'px}';
                }
                if ($accordionStyle['title-color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-accordion .accordion-title{color:'.$accordionStyle['title-color'].'}';
                }
                if ($accordionStyle['title-background']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-accordion .accordion-title{background-color:'.$accordionStyle['title-background'].'}';
                }
                if ($accordionStyle['title-bold']) {
                    $customStyle .= '.block'.$model->getId().' .mgs-accordion .accordion-title{font-weight:bold}';
                }
                if ($accordionStyle['title-italic']) {
                    $customStyle .= '.block'.$model->getId().' .mgs-accordion .accordion-title{font-style:italic}';
                }
                if ($accordionStyle['title-uppercase']) {
                    $customStyle .= '.block'.$model->getId().' .mgs-accordion .accordion-title{text-transform:uppercase}';
                }
                if ($accordionStyle['active-bold']) {
                    $customStyle .= '.block'.$model->getId().' .mgs-accordion .accordion-title.ui-accordion-header-active{font-weight:bold}';
                }
                if ($accordionStyle['active-italic']) {
                    $customStyle .= '.block'.$model->getId().' .mgs-accordion .accordion-title.ui-accordion-header-active{font-style:italic}';
                }
                if ($accordionStyle['active-uppercase']) {
                    $customStyle .= '.block'.$model->getId().' .mgs-accordion .accordion-title.ui-accordion-header-active{text-transform:uppercase}';
                }
                if ($accordionStyle['active-color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-accordion .accordion-title.ui-accordion-header-active{color:'.$accordionStyle['active-color'].'}';
                }
                if ($accordionStyle['active-background']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-accordion .accordion-title.ui-accordion-header-active{background-color:'.$accordionStyle['active-background'].'}';
                    $customStyle .= '.block'.$model->getId().' .mgs-accordion .ui-accordion-content-active{border-color:'.$accordionStyle['active-background'].'}';
                }
                if ($accordionStyle['icon-color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-accordion .accordion-title::before{color:'.$accordionStyle['icon-color'].'}';
                }

                if ($accordionStyle['icon-background']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-accordion .accordion-title::before{background-color:'.$accordionStyle['icon-background'].'}';
                    if ($accordionStyle['icon-position']=='left') {
                        $padding = 50;
                        if ($accordionStyle['height']!='') {
                            $padding = $accordionStyle['height'] + 10;

                        }
                        $customStyle .= '.block'.$model->getId().' .mgs-accordion .accordion-title{padding-left:'.$padding.'px}';
                    }
                }

                if ($accordionStyle['icon-size']!='') {


                    if ($accordionStyle['icon-type']=='icon2') {
                        $fontSize = $accordionStyle['icon-size'] - 4;
                        $customStyle .= '.block'.$model->getId().' .mgs-accordion .accordion-title::before{font-size:'.$fontSize.'px}';

                        $customStyle .= '.block'.$model->getId().' .mgs-accordion .accordion-title.ui-accordion-header-active::before{font-size:'.$accordionStyle['icon-size'].'px}';
                    } else {
                        $customStyle .= '.block'.$model->getId().' .mgs-accordion .accordion-title::before{font-size:'.$accordionStyle['icon-size'].'px}';
                    }
                }
            }

            if (isset($data['custom_style_temp']['map-style'])) {
                $mapStyle = $data['custom_style_temp']['map-style'];

                if ($mapStyle['background']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-map .map-info{background-color:'.$mapStyle['background'].'}';
                }

                if ($mapStyle['color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-map .map-info h3, .block'.$model->getId().' .mgs-map .map-info .map-detail-info ul li{color:'.$mapStyle['color'].'}';
                }

                if ($mapStyle['width']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-map .map-info{width:'.$mapStyle['width'].'px}';
                }

                if ($mapStyle['size']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-map .map-info .map-detail-info ul li{font-size:'.$mapStyle['size'].'px}';
                }

                if ($mapStyle['title-size']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-map .map-info h3{font-size:'.$mapStyle['title-size'].'px}';
                }
            }

            if (isset($data['custom_style_temp']['banner-style'])) {
                $bannerStyle = $data['custom_style_temp']['banner-style'];

                if ($bannerStyle['text-color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-promobanner .banner-text{color:'.$bannerStyle['text-color'].'}';
                }

                if ($bannerStyle['button-background']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-promobanner .banner-button button.btn-promo-banner{background-color:'.$bannerStyle['button-background'].'}';
                }

                if ($bannerStyle['button-color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-promobanner .banner-button button.btn-promo-banner span{color:'.$bannerStyle['button-color'].'}';
                }

                if ($bannerStyle['button-border']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-promobanner .banner-button button.btn-promo-banner{border-color:'.$bannerStyle['button-border'].'}';
                }

                if ($bannerStyle['button-hover-background']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-promobanner .banner-button button.btn-promo-banner:hover{background-color:'.$bannerStyle['button-hover-background'].'}';
                }

                if ($bannerStyle['button-hover-color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-promobanner .banner-button button.btn-promo-banner:hover span{color:'.$bannerStyle['button-hover-color'].'}';
                }

                if ($bannerStyle['button-hover-border']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-promobanner .banner-button button.btn-promo-banner:hover{border-color:'.$bannerStyle['button-hover-border'].'}';
                }
            }

            if (isset($data['custom_style_temp']['profile-style'])) {
                $profileStyle = $data['custom_style_temp']['profile-style'];

                if ($profileStyle['name-font-size']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-profile h4{font-size:'.$profileStyle['name-font-size'].'px}';
                }

                if ($profileStyle['name-font-color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-profile h4{color:'.$profileStyle['name-font-color'].'}';
                }

                if ($profileStyle['subtitle-font-size']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-profile .subtitle{font-size:'.$profileStyle['subtitle-font-size'].'px}';
                }

                if ($profileStyle['subtitle-font-color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-profile .subtitle{color:'.$profileStyle['subtitle-font-color'].'}';
                }

                if ($profileStyle['subtitle-border-color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-profile .subtitle span::after{background:'.$profileStyle['subtitle-border-color'].'}';
                }

                if ($profileStyle['desc-font-size']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-profile .profile-description{font-size:'.$profileStyle['desc-font-size'].'px}';
                }

                if ($profileStyle['desc-font-color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-profile .profile-description{color:'.$profileStyle['desc-font-color'].'}';
                }

                if ($profileStyle['social-font-size']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-profile .social ul li a{font-size:'.$profileStyle['social-font-size'].'px}';
                }

                if ($profileStyle['social-box-width']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-profile .social ul li{width:'.$profileStyle['social-box-width'].'px; height:'.$profileStyle['social-box-width'].'px; line-height:'.$profileStyle['social-box-width'].'px}';
                }

                if ($profileStyle['social-font-color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-profile .social ul li a{color:'.$profileStyle['social-font-color'].'}';
                }

                if ($profileStyle['social-background']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-profile .social ul li a{background-color:'.$profileStyle['social-background'].'}';
                }

                if ($profileStyle['social-border']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-profile .social ul li a{border-color:'.$profileStyle['social-border'].'}';
                    if ($profileStyle['social-box-shadow']) {
                        $customStyle .= '.block'.$model->getId().' .mgs-profile .social ul li a{-webkit-box-shadow: inset 0 0 5px 0 '.$profileStyle['social-border'].'; box-shadow: inset 0 0 5px 0 '.$profileStyle['social-border'].';}';
                    }
                }

                if ($profileStyle['social-hover-color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-profile .social ul li a:hover{color:'.$profileStyle['social-hover-color'].'}';
                }

                if ($profileStyle['social-hover-background']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-profile .social ul li a:hover{background-color:'.$profileStyle['social-hover-background'].'}';
                }

                if ($profileStyle['social-hover-border']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-profile .social ul li a:hover{border-color:'.$profileStyle['social-hover-border'].'}';
                    if ($profileStyle['social-box-shadow']) {
                        $customStyle .= '.block'.$model->getId().' .mgs-profile .social ul li a:hover{-webkit-box-shadow: inset 0 0 5px 0 '.$profileStyle['social-hover-border'].'; box-shadow: inset 0 0 5px 0 '.$profileStyle['social-hover-border'].';}';
                    }
                }
            }

            if (isset($data['custom_style_temp']['box-style'])) {
                $boxStyle = $data['custom_style_temp']['box-style'];

                $width = 100;
                if ($boxStyle['width']!='') {
                    $width = $boxStyle['width'];
                }

                if ($boxStyle['icon_font_size']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-content-box .icon-wrapper{font-size:'.$boxStyle['icon_font_size'].'px}';
                }

                if ($boxStyle['border']!='' && $boxStyle['border_width']!='') {
                    $lineHeight = $width - $boxStyle['border_width'] - $boxStyle['border_width'];
                    $customStyle .= '.block'.$model->getId().' .mgs-content-box .icon-wrapper{border:'.$boxStyle['border_width'].'px solid '.$boxStyle['border'].'; line-height:'.$lineHeight.'px}';
                }

                if ($boxStyle['border_hover']!='' && $boxStyle['border_width']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-content-box:hover .icon-wrapper{border:'.$boxStyle['border_width'].'px solid '.$boxStyle['border_hover'].'}';
                }

                if ($boxStyle['width']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-content-box .icon-wrapper{width:'.$boxStyle['width'].'px; height:'.$boxStyle['width'].'px;';
                    if ($boxStyle['border']=='' || $boxStyle['border_width']=='') {
                        $customStyle .= 'line-height:'.$boxStyle['width'].'px';
                    }
                    $customStyle .= '}';

                    if ($boxStyle['style']=='horizontal') {
                        $marginLeft = $boxStyle['width'] + 20;
                        $customStyle .= '.block'.$model->getId().' .mgs-content-box.box-horizontal .content-wrapper{margin-left:'.$marginLeft.'px}';
                    }
                }

                if ($boxStyle['icon_color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-content-box .icon-wrapper{color:'.$boxStyle['icon_color'].'}';
                }

                if ($boxStyle['icon_color_hover']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-content-box:hover .icon-wrapper{color:'.$boxStyle['icon_color_hover'].'}';
                }

                if ($boxStyle['icon_background']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-content-box .icon-wrapper{background:'.$boxStyle['icon_background'].'}';
                }

                if ($boxStyle['icon_background_hover']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-content-box:hover .icon-wrapper{background:'.$boxStyle['icon_background_hover'].'}';
                }

                if ($boxStyle['subtitle_font_size']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-content-box .content-wrapper h4{font-size:'.$boxStyle['subtitle_font_size'].'px}';
                }

                if ($boxStyle['subtitle_font_color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-content-box .content-wrapper h4{color:'.$boxStyle['subtitle_font_color'].'}';
                }

                if ($boxStyle['subtitle_color_hover']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-content-box:hover .content-wrapper h4{color:'.$boxStyle['subtitle_color_hover'].'}';
                }

                if ($boxStyle['desc_font_size']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-content-box .content-wrapper .content{font-size:'.$boxStyle['desc_font_size'].'px}';
                }
            }

            if (isset($data['custom_style_temp']['countdown-style'])) {
                $countdownStyle = $data['custom_style_temp']['countdown-style'];
                if ($countdownStyle['date_font_size']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-countdown-block .countdown-timer .countdown span b{font-size:'.$countdownStyle['date_font_size'].'px}';
                }

                if ($countdownStyle['date_fontweight']) {
                    $customStyle .= '.block'.$model->getId().' .mgs-countdown-block .countdown-timer .countdown span b{font-weight:bold}';
                }

                if ($countdownStyle['date_color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-countdown-block .countdown-timer .countdown span b{color:'.$countdownStyle['date_color'].'}';
                }

                if ($countdownStyle['date_background']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-countdown-block .countdown-timer .countdown span b{background:'.$countdownStyle['date_background'].'}';
                }

                if ($countdownStyle['date_border']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-countdown-block .countdown-timer .countdown span b{border:1px solid '.$countdownStyle['date_border'].'}';
                }

                if ($countdownStyle['date_border_size']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-countdown-block .countdown-timer .countdown span b{border-width:'.$countdownStyle['date_border_size'].'px}';
                }

                if ($countdownStyle['date_border_radius']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-countdown-block .countdown-timer .countdown span b{border-radius:'.$countdownStyle['date_border_radius'].'px}';
                }

                if ($countdownStyle['date_background']!='' || $countdownStyle['date_border']!='') {
                    $size = 20;
                    if ($countdownStyle['date_border']!='') {
                        $size = 22;
                        if ($countdownStyle['date_border_size']!='') {
                            $size = 20 + $countdownStyle['date_border_size'];
                        }
                    }
                    $customStyle .= '.block'.$model->getId().' .mgs-countdown-block .countdown-timer .countdown span b{padding:20px;}';
                    $customStyle .= '.block'.$model->getId().' .mgs-countdown-block .countdown{padding:'.$size.'px 0;}';
                    if ($countdownStyle['position']=='top') {
                        $customStyle .= '.block'.$model->getId().' .mgs-countdown-block .countdown-timer .time-text{margin-bottom:10px}';
                    } else {
                        $customStyle .= '.block'.$model->getId().' .mgs-countdown-block .countdown-timer .time-text{margin-top:10px}';
                    }
                }

                if ($countdownStyle['text_font_size']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-countdown-block .countdown-timer .time-text{font-size:'.$countdownStyle['text_font_size'].'px}';
                }

                if ($countdownStyle['text_color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-countdown-block .countdown-timer .time-text{color:'.$countdownStyle['text_color'].'}';
                }
            }

            if (isset($data['custom_style_temp']['divider-style'])) {
                $dividerStyle = $data['custom_style_temp']['divider-style'];
                if ($dividerStyle['border_width']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-divider-block .mgs-divider hr{border-width:'.$dividerStyle['border_width'].'px}';
                }
                if ($dividerStyle['border_color']!='') {
                    if ($dividerStyle['style']=='shadown') {
                        list($r, $g, $b) = sscanf($dividerStyle['border_color'], "#%02x%02x%02x");

                        $customStyle .= '.block'.$model->getId().' .mgs-divider-block .mgs-divider hr::after{background: -webkit-radial-gradient(50% -50% ellipse,rgba('.$r.','.$g.','.$b.',.5) 0,rgba(255,255,255,0) 65%);background: radial-gradient(ellipse at 50% -50%,rgba('.$r.','.$g.','.$b.',.5) 0,rgba(255,255,255,0) 65%);}';
                    } else {
                        $customStyle .= '.block'.$model->getId().' .mgs-divider-block .mgs-divider hr{border-color:'.$dividerStyle['border_color'].'}';
                    }
                }

                if ($dividerStyle['show_text']) {
                    if ($dividerStyle['text_font_size']!='') {
                        $customStyle .= '.block'.$model->getId().' .mgs-divider .divider-text span{font-size:'.$dividerStyle['text_font_size'].'px}';

                        $marginTop = $dividerStyle['text_font_size']/2;
                        $customStyle .= '.block'.$model->getId().' .mgs-divider .divider-text{height:'.$dividerStyle['text_font_size'].'px;line-height:'.$dividerStyle['text_font_size'].'px;margin-top:-'.$marginTop.'px}';
                    }
                    if ($dividerStyle['text_color']!='') {
                        $customStyle .= '.block'.$model->getId().' .mgs-divider .divider-text span{color:'.$dividerStyle['text_color'].'}';
                    }
                    if ($dividerStyle['text_background']!='') {
                        $customStyle .= '.block'.$model->getId().' .mgs-divider .divider-text span{background-color:'.$dividerStyle['text_background'].'}';
                    }
                    if ($dividerStyle['text_padding']!='') {
                        $customStyle .= '.block'.$model->getId().' .mgs-divider .divider-text span{padding:0 '.$dividerStyle['text_padding'].'px}';
                    }
                }

                if ($dividerStyle['show_icon']) {
                    if ($dividerStyle['icon_font_size']!='') {
                        $customStyle .= '.block'.$model->getId().' .mgs-divider .divider-icon span, .block'.$model->getId().' .mgs-divider .text-icon-container span.icon{font-size:'.$dividerStyle['icon_font_size'].'px;}';

                        $marginTop = $dividerStyle['icon_font_size']/2;
                        $customStyle .= '.block'.$model->getId().' .mgs-divider .divider-icon{height:'.$dividerStyle['icon_font_size'].'px;line-height:'.$dividerStyle['icon_font_size'].'px;margin-top:-'.$marginTop.'px; height:'.$dividerStyle['icon_font_size'].'px;}';

                        $customStyle .= '.block'.$model->getId().' .mgs-divider .divider-icon span::before,.block'.$model->getId().' .mgs-divider .text-icon-container span.icon:before{margin-top:-'.$marginTop.'px}';
                    }

                    if ($dividerStyle['icon_color']!='') {
                        $customStyle .= '.block'.$model->getId().' .mgs-divider .divider-icon span, .block'.$model->getId().' .mgs-divider .text-icon-container span.icon{color:'.$dividerStyle['icon_color'].'}';
                    }
                    if ($dividerStyle['icon_background']!='') {
                        $customStyle .= '.block'.$model->getId().' .mgs-divider .divider-icon span, .block'.$model->getId().' .mgs-divider .text-icon-container span.icon{background-color:'.$dividerStyle['icon_background'].'}';
                    }
                    if ($dividerStyle['icon_padding']!='') {
                        $customStyle .= '.block'.$model->getId().' .mgs-divider .divider-icon span, .block'.$model->getId().' .mgs-divider .text-icon-container span.icon{width:'.$dividerStyle['icon_padding'].'px; height:'.$dividerStyle['icon_padding'].'px}';

                        $marginTop = $dividerStyle['icon_padding']/2;
                        $customStyle .= '.block'.$model->getId().' .mgs-divider .divider-icon{height:'.$dividerStyle['icon_padding'].'px; margin-top:-'.$marginTop.'px}';

                    }
                    if ($dividerStyle['icon_border']!='') {
                        $customStyle .= '.block'.$model->getId().' .mgs-divider .divider-icon span, .block'.$model->getId().' .mgs-divider .text-icon-container span.icon{border:1px solid '.$dividerStyle['icon_border'].';}';
                    }
                }

                if ($dividerStyle['show_icon'] && $dividerStyle['show_text']) {
                    if ($dividerStyle['icon_font_size']=='') {
                        $customStyle .= '.block'.$model->getId().' .mgs-divider .text-icon-container span.icon{font-size:15px;}';
                        $customStyle .= '.block'.$model->getId().' .mgs-divider .text-icon-container span.icon::before{margin-top:-7.5px}';
                    }

                    $textHeight = 20;
                    if ($dividerStyle['text_font_size']!='') {
                        $textHeight = $dividerStyle['text_font_size'];
                    }
                    $height = $textHeight;
                    $iconHeight = $dividerStyle['icon_padding'];
                    if ($height<$iconHeight) {
                        $height = $iconHeight;
                    }
                    $marginTop = $height/2;
                    $top = $marginTop/2;
                    $customStyle .= '.block'.$model->getId().' .mgs-divider .divider-text{height:'.$height.'px; line-height:'.$height.'px; margin-top:-'.$marginTop.'px}';

                    if ($dividerStyle['icon_padding']>$dividerStyle['text_font_size']) {
                        $customStyle .= '.block'.$model->getId().' .mgs-divider .divider-text span.text{position:relative; top:-'.$top.'px; background:transparent}';
                    }
                }

            }

            if (isset($data['custom_style_temp']['heading-style'])) {
                $headingStyle = $data['custom_style_temp']['heading-style'];
                if ($headingStyle['heading_font_size']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-heading .heading{font-size:'.$headingStyle['heading_font_size'].'px}';
                }
                if ($headingStyle['heading_color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-heading .heading{color:'.$headingStyle['heading_color'].'}';
                }

                if ($headingStyle['show_border']) {
                    if (($headingStyle['border_position']=='middle') && ($headingStyle['heading_background']!='')) {
                        $customStyle .= '.block'.$model->getId().' .mgs-heading.has-border.heading-middle .heading span{background:'.$headingStyle['heading_background'].'}';
                    }
                    if ($headingStyle['border_color']!='') {
                        $customStyle .= '.block'.$model->getId().' .mgs-heading.has-border .heading::after{border-color:'.$headingStyle['border_color'].'}';
                    }
                    if ($headingStyle['border_width']!='') {
                        $customStyle .= '.block'.$model->getId().' .mgs-heading.has-border .heading::after{border-width:'.$headingStyle['border_width'].'px}';

                        if ($headingStyle['border_position']=='middle') {
                            $customStyle .= '.block'.$model->getId().' .mgs-heading.has-border .heading::after{margin-top:-'. $headingStyle['border_width']/2 .'px}';
                        } else {
                            if ($headingStyle['border_margin']!='') {
                                $customStyle .= '.block'.$model->getId().' .mgs-heading.has-border .heading::after{bottom:-'. $headingStyle['border_margin'] .'px}';
                            }
                        }
                        if ($headingStyle['border_container_width']!='') {
                            $marginLeft = round($headingStyle['border_container_width']/2);
                            $customStyle .= '.block'.$model->getId().' .mgs-heading.has-border .heading::after{width:'. $headingStyle['border_container_width'] .'px; left:50%; margin-left:-'.$marginLeft.'px}';
                        }
                    }
                }
            }

            if (isset($data['custom_style_temp']['list-style'])) {
                $listStyle = $data['custom_style_temp']['list-style'];
                if ($listStyle['font_size']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-list-block li{font-size:'.$listStyle['font_size'].'px}';
                }
                if ($listStyle['color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-list-block li{color:'.$listStyle['color'].'}';
                }
                if ($listStyle['margin_bottom']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-list-block li{margin-bottom:'.$listStyle['margin_bottom'].'px}';
                }
                if ($listStyle['list_style_type']=='default') {
                    if ($listStyle['icon_color']!='') {
                        $customStyle .= '.block'.$model->getId().' .mgs-list-block li{color:'.$listStyle['icon_color'].'}';

                        if ($listStyle['color']!='') {
                            $customStyle .= '.block'.$model->getId().' .mgs-list-block li .text{color:'.$listStyle['color'].'}';
                        } else {
                            $customStyle .= '.block'.$model->getId().' .mgs-list-block li .text{color:#575757}';
                        }
                    }
                } else {
                    if ($listStyle['icon_color']!='') {
                        $customStyle .= '.block'.$model->getId().' .mgs-list-block li .list-icon{color:'.$listStyle['icon_color'].'}';
                    }

                    if ($listStyle['icon_font_size']!='') {
                        $customStyle .= '.block'.$model->getId().' .mgs-list-block li .list-icon{font-size:'.$listStyle['icon_font_size'].'px}';
                    }

                    if ($listStyle['icon_margin']!='') {
                        $customStyle .= '.block'.$model->getId().' .mgs-list-block li .list-icon{margin-right:'.$listStyle['icon_margin'].'px}';
                    }
                }
            }

            if (isset($data['custom_style_temp']['testimonial'])) {
                $testimonialStyle = $data['custom_style_temp']['testimonial'];
                if ($testimonialStyle['name_font_size']!='') {
                    $customStyle .= '.block'.$model->getId().' .testimonial-content .content .name{font-size:'.$testimonialStyle['name_font_size'].'px}';
                }
                if ($testimonialStyle['name_color']!='') {
                    $customStyle .= '.block'.$model->getId().' .testimonial-content .content .name{color:'.$testimonialStyle['name_color'].'}';
                }
                if ($testimonialStyle['info_font_size']!='') {
                    $customStyle .= '.block'.$model->getId().' .testimonial-content .content .infomation{font-size:'.$testimonialStyle['info_font_size'].'px}';
                }
                if ($testimonialStyle['info_color']!='') {
                    $customStyle .= '.block'.$model->getId().' .testimonial-content .content .infomation{color:'.$testimonialStyle['info_color'].'}';
                }
                if ($testimonialStyle['content_font_size']!='') {
                    $customStyle .= '.block'.$model->getId().' .testimonial-content .content blockquote{font-size:'.$testimonialStyle['content_font_size'].'px}';
                }
                if ($testimonialStyle['content_color']!='') {
                    $customStyle .= '.block'.$model->getId().' .testimonial-content .content blockquote{color:'.$testimonialStyle['content_color'].'}';
                }
            }

            if (isset($data['custom_style_temp']['image'])) {
                $imageStyle = $data['custom_style_temp']['image'];
                if ($imageStyle['border_width']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-image-block .image-content span.span-container{border:'.$imageStyle['border_width'].'px solid #f8f8f8}';
                    if ($imageStyle['border_color']!='') {
                        $customStyle .= '.block'.$model->getId().' .mgs-image-block .image-content span.span-container{border-color:'.$imageStyle['border_color'].'; background:'.$imageStyle['border_color'].'}';
                    }
                }

                if ($imageStyle['border_radius']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-image-block .image-content span.span-container, .block'.$model->getId().' .mgs-image-block .image-content span.span-container img{border-radius:'.$imageStyle['border_radius'].'px}';
                }
            }

            if (isset($data['custom_style_temp']['button'])) {
                $buttonStyle = $data['custom_style_temp']['button'];
                if ($buttonStyle['text_font_size']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-button-block button{font-size:'.$buttonStyle['text_font_size'].'px}';
                }
                if ($buttonStyle['text_color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-button-block button{color:'.$buttonStyle['text_color'].'}';
                }
                if ($buttonStyle['text_hover_color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-button-block button:hover{color:'.$buttonStyle['text_hover_color'].'}';
                }
                if ($buttonStyle['button_bg_gradient']) {
                    $customStyle .= '.block'.$model->getId().' .mgs-button-block button{'.$this->getGradientBackground($buttonStyle['button_bg_orientation'], $buttonStyle['button_bg_from'], $buttonStyle['button_bg_to']).'}';

                    if ($buttonStyle['button_bg_hover_from']!='' || $buttonStyle['button_bg_hover_to']!='') {
                        $customStyle .= '.block'.$model->getId().' .mgs-button-block button:hover{'.$this->getGradientBackground($buttonStyle['button_bg_hover_orientation'], $buttonStyle['button_bg_hover_from'], $buttonStyle['button_bg_hover_to']).'}';
                    }
                } else {
                    if ($buttonStyle['button_bg_color']!='') {
                        $customStyle .= '.block'.$model->getId().' .mgs-button-block button{background:'.$buttonStyle['button_bg_color'].'}';
                    }
                    if ($buttonStyle['button_bg_hover_color']!='') {
                        $customStyle .= '.block'.$model->getId().' .mgs-button-block button:hover{background:'.$buttonStyle['button_bg_hover_color'].'}';
                    }
                }


                if ($buttonStyle['use_border'] && $buttonStyle['border_color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-button-block button{border:1px solid '.$buttonStyle['border_color'].'}';
                    if ($buttonStyle['border_width']!='' && $buttonStyle['border_width']>1) {
                        $customStyle .= '.block'.$model->getId().' .mgs-button-block button{border-width:'.$buttonStyle['border_width'].'px}';
                    }

                    if ($buttonStyle['border_top']==0) {
                        $customStyle .= '.block'.$model->getId().' .mgs-button-block button{border-top:0}';
                    }
                    if ($buttonStyle['border_right']==0) {
                        $customStyle .= '.block'.$model->getId().' .mgs-button-block button{border-right:0}';
                    }
                    if ($buttonStyle['border_bottom']==0) {
                        $customStyle .= '.block'.$model->getId().' .mgs-button-block button{border-bottom:0}';
                    }
                    if ($buttonStyle['border_left']==0) {
                        $customStyle .= '.block'.$model->getId().' .mgs-button-block button{border-left:0}';
                    }
                }

                if ($buttonStyle['use_border'] && $buttonStyle['border_hover_color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-button-block button:hover{border-color:'.$buttonStyle['border_hover_color'].'}';
                }

                if ($buttonStyle['border_radius']!='' && $buttonStyle['border_radius']>0) {
                    $customStyle .= '.block'.$model->getId().' .mgs-button-block button{border-radius:'.$buttonStyle['border_radius'].'px}';
                }

                if ($buttonStyle['full_width']==0 && ($buttonStyle['button_width']!='' && $buttonStyle['button_width']>0)) {
                    $customStyle .= '.block'.$model->getId().' .mgs-button-block button{width:'.$buttonStyle['button_width'].'px; text-align:center; padding-left:0; padding-right:0}';

                    $customStyle .= '.block'.$model->getId().' .mgs-button-block button span.has-divider{margin:0}';
                }

                if ($buttonStyle['use_divider'] && ($buttonStyle['button_width']=='' || $buttonStyle['button_width']==0)) {
                    /* if($buttonStyle['icon_align']=='left' && $buttonStyle['use_icon'] && $buttonStyle['icon']!=''){
                        $customStyle .= '.block'.$model->getId().' .mgs-button-block button{padding-left:0}';
                    }elseif($buttonStyle['icon_align']=='right' && $buttonStyle['use_icon'] && $buttonStyle['icon']!=''){
                        $customStyle .= '.block'.$model->getId().' .mgs-button-block button{padding-right:0}';
                    } */
                }

                if ($buttonStyle['button_height']!='' && $buttonStyle['button_height']>0) {
                    $height = $buttonStyle['button_height'];
                    $customStyle .= '.block'.$model->getId().' .mgs-button-block button{height:'.$height.'px; line-height:'.$height.'px;}';
                    $customStyle .= '.block'.$model->getId().' .mgs-button-block button span.has-divider{width:'.$height.'px; padding:0}';
                } else {
                    $height = 35;
                }

                if ($buttonStyle['use_border'] && $buttonStyle['border_color']!='') {
                    $borderHeight = 1;
                    if ($buttonStyle['border_width']!='' && $buttonStyle['border_width']>1) {
                        $borderHeight = $buttonStyle['border_width'];
                    }

                    if ($buttonStyle['border_top']) {
                        $height -= $borderHeight;
                    }
                    if ($buttonStyle['border_bottom']) {
                        $height -= $borderHeight;
                    }

                }



                $customStyle .= '.block'.$model->getId().' .mgs-button-block button{line-height:'.$height.'px;}';
                $customStyle .= '.block'.$model->getId().' .mgs-button-block button span{height:'.$height.'px; line-height:'.$height.'px;}';


                if ($buttonStyle['use_icon'] && $buttonStyle['icon']!='') {
                    if ($buttonStyle['icon_font_size']!='') {
                        $customStyle .= '.block'.$model->getId().' .mgs-button-block button i{font-size:'.$buttonStyle['icon_font_size'].'px}';
                    }
                    if ($buttonStyle['icon_color']!='') {
                        $customStyle .= '.block'.$model->getId().' .mgs-button-block button i{color:'.$buttonStyle['icon_color'].'}';
                    }
                    if ($buttonStyle['icon_hover_color']!='') {
                        $customStyle .= '.block'.$model->getId().' .mgs-button-block button:hover i{color:'.$buttonStyle['icon_hover_color'].'}';
                    }
                    if ($buttonStyle['use_divider']) {
                        if ($buttonStyle['divider_width']!='' && $buttonStyle['divider_width']>1) {
                            $customStyle .= '.block'.$model->getId().' .mgs-button-block button .has-divider{border-width:'.$buttonStyle['divider_width'].'px}';
                        }
                        if ($buttonStyle['divider_color']!='') {
                            $customStyle .= '.block'.$model->getId().' .mgs-button-block button .has-divider{border-color:'.$buttonStyle['divider_color'].'}';
                        }
                        if ($buttonStyle['divider_hover_color']!='') {
                            $customStyle .= '.block'.$model->getId().' .mgs-button-block button:hover .has-divider{border-color:'.$buttonStyle['divider_hover_color'].'}';
                        }
                    }
                }
            }

            if (isset($data['custom_style_temp']['table'])) {
                $tableStyle = $data['custom_style_temp']['table'];
                if ($tableStyle['text_align']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-table-block td{text-align:'.$tableStyle['text_align'].'}';
                }
                if ($tableStyle['border_color']!='' && $tableStyle['border_width'] > 0) {
                    $customStyle .= '.block'.$model->getId().' .mgs-table-block{border:'.$tableStyle['border_width'].'px solid '.$tableStyle['border_color'].'}';
                }
                if ($tableStyle['text_color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-table-block td{color:'.$tableStyle['text_color'].'}';
                }

                if ($tableStyle['font_size']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-table-block td{font-size:'.$tableStyle['font_size'].'px}';
                }

                if ($tableStyle['row_height']!='' && $tableStyle['row_height']>0) {
                    $customStyle .= '.block'.$model->getId().' .mgs-table-block td{height:'.$tableStyle['row_height'].'px; line-height:'.$tableStyle['row_height'].'px; padding-top:0; padding-bottom:0}';
                }

                if ($tableStyle['fullwidth']) {
                    $customStyle .= '.block'.$model->getId().' .mgs-table-block{width:100% !important}';
                }

                if ($tableStyle['heading_row_height']!='' && $tableStyle['heading_row_height']>0) {
                    $customStyle .= '.block'.$model->getId().' .mgs-table-block tr:first-child td{height:'.$tableStyle['heading_row_height'].'px; line-height:'.$tableStyle['heading_row_height'].'px; padding-top:0; padding-bottom:0}';
                }

                if ($tableStyle['heading_background']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-table-block tr:first-child td{background:'.$tableStyle['heading_background'].' !important}';
                }

                if ($tableStyle['heading_text_color']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-table-block tr:first-child td{color:'.$tableStyle['heading_text_color'].'}';
                }

                if ($tableStyle['heading_font_size']!='' && $tableStyle['heading_font_size']>0) {
                    $customStyle .= '.block'.$model->getId().' .mgs-table-block tr:first-child td{font-size:'.$tableStyle['heading_font_size'].'px}';
                }

                if ($tableStyle['heading_font_bold']) {
                    $customStyle .= '.block'.$model->getId().' .mgs-table-block tr:first-child td{font-weight:bold}';
                }



                if ($tableStyle['other_border_color']!='' && $tableStyle['other_border_width']!='' && $tableStyle['other_border_width']>0) {
                    $customStyle .= '.block'.$model->getId().' .mgs-table-block td{border:'.$tableStyle['other_border_width'].'px solid '.$tableStyle['other_border_color'].'}';
                }

                if (!$tableStyle['other_border_vertical']) {
                    $customStyle .= '.block'.$model->getId().' .mgs-table-block td{border-left:0; border-right:0}';
                }
                if (!$tableStyle['other_border_horizontal']) {
                    $customStyle .= '.block'.$model->getId().' .mgs-table-block td{border-top:0; border-bottom:0}';
                }


                if ($tableStyle['heading_border_color']!='' && $tableStyle['heading_border_width']!='' && $tableStyle['heading_border_width']>0) {
                    $customStyle .= '.block'.$model->getId().' .mgs-table-block tr:first-child td{border:'.$tableStyle['heading_border_width'].'px solid '.$tableStyle['heading_border_color'].'}';
                }

                if (!$tableStyle['border_vertical']) {
                    $customStyle .= '.block'.$model->getId().' .mgs-table-block tr:first-child td{border-left:0; border-right:0}';
                }
                if (!$tableStyle['border_horizontal']) {
                    $customStyle .= '.block'.$model->getId().' .mgs-table-block tr:first-child td{border-top:0; border-bottom:0}';
                }

                if ($tableStyle['even_background']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-table-block tr:nth-child(even) td{background:'.$tableStyle['even_background'].'}';
                }

                if ($tableStyle['odd_background']!='') {
                    $customStyle .= '.block'.$model->getId().' .mgs-table-block tr:nth-child(odd) td{background:'.$tableStyle['odd_background'].'}';
                }
            }

            if (isset($data['custom_style_temp']['masonry'])) {
                $masonryStyle = $data['custom_style_temp']['masonry'];
                if ($masonryStyle['border_color']!='' && $masonryStyle['border_width']!='' && $masonryStyle['border_width']>0) {
                    $customStyle .= '.block'.$model->getId().' .mgs-masonry-block .grid-item span{border:'.$masonryStyle['border_width'].'px solid '.$masonryStyle['border_color'].'}';
                }
                if ($masonryStyle['border_radius']!='' && $masonryStyle['border_radius']>0) {
                    $customStyle .= '.block'.$model->getId().' .mgs-masonry-block .grid-item span{border-radius:'.$masonryStyle['border_radius'].'px}';
                }
            }

            if (isset($data['custom_style_temp']['popup'])) {
                $popupStyle = $data['custom_style_temp']['popup'];
                if ($popupStyle['popup_width']!='' && $popupStyle['popup_width']>0) {
                    $customStyle .= '.modal-'.$popupStyle['generate_block_id'].' .modal-inner-wrap{width:'.$popupStyle['popup_width'].'px}';
                }
                if ($popupStyle['popup_background']!='') {
                    $customStyle .= '.modal-'.$popupStyle['generate_block_id'].' .modal-inner-wrap{background:'.$popupStyle['popup_background'].'}';
                }

                if ($popupStyle['popup_color']!='') {
                    $customStyle .= '.modal-'.$popupStyle['generate_block_id'].' .modal-inner-wrap{color:'.$popupStyle['popup_color'].'}';
                }

                if ($popupStyle['popup_font_size']!='' && $popupStyle['popup_font_size']>0) {
                    $customStyle .= '.modal-'.$popupStyle['generate_block_id'].' .modal-inner-wrap{font-size:'.$popupStyle['popup_font_size'].'px}';
                }

                if ($popupStyle['popup_border_radius']!='' && $popupStyle['popup_border_radius']>0) {
                    $customStyle .= '.modal-'.$popupStyle['generate_block_id'].' .modal-inner-wrap{border-radius:'.$popupStyle['popup_border_radius'].'px}';
                }

                if ($popupStyle['title_font_size']!='' && $popupStyle['title_font_size']>0) {
                    $customStyle .= '.modal-'.$popupStyle['generate_block_id'].' .modal-inner-wrap .pop-sletter-title h3{font-size:'.$popupStyle['title_font_size'].'px}';
                }

                if ($popupStyle['title_color']!='') {
                    $customStyle .= '.modal-'.$popupStyle['generate_block_id'].' .modal-inner-wrap .pop-sletter-title h3{color:'.$popupStyle['title_color'].'}';
                }

                if ($popupStyle['title_border_size']!='' && $popupStyle['title_border_size']>0 && $popupStyle['title_boder_color']!='') {
                    $customStyle .= '.modal-'.$popupStyle['generate_block_id'].' .modal-inner-wrap .pop-sletter-title{border-bottom:'.$popupStyle['title_border_size'].'px solid '.$popupStyle['title_boder_color'].'}';
                }
            }

            /* $data['custom_style_temp']['popup'] = [
                'generate_block_id' => $data['setting']['generate_block_id'],
                'title_boder_color' => $data['setting']['title_boder_color'],
                'title_border_size' => $data['setting']['title_border_size']
            ]; */

            if($customStyle!=''){
                $this->getModel('MGS\Fbuilder\Model\Child')->setCustomStyle($customStyle)->setId($model->getId())->save();
                $this->generateBlockCss();
            }

            return $this->getMessageHtml('success', $sessionMessage, true);
        } catch (\Exception $e) {
            return $this->getMessageHtml('danger', $e->getMessage(), false);
        }
    }

    public function getGradientBackground($orientation, $gradientFrom, $gradientTo)
    {
        if ($gradientFrom=='') {
            $gradientFrom = '#ffffff';
        }
        if ($gradientTo=='') {
            $gradientTo = '#ffffff';
        }
        switch ($orientation) {
        case "vertical":
            $html = 'background: '.$gradientFrom.'; background: -moz-linear-gradient(top, '.$gradientFrom.' 0%, '.$gradientTo.' 100%); background: -webkit-linear-gradient(top, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); background: linear-gradient(to bottom, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='.$gradientFrom.', endColorstr='.$gradientTo.',GradientType=0 );';
            break;
        case "diagonal":
            $html = 'background: '.$gradientFrom.'; background: -moz-linear-gradient(-45deg, '.$gradientFrom.' 0%, '.$gradientTo.' 100%); background: -webkit-linear-gradient(-45deg, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); background: linear-gradient(135deg, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='.$gradientFrom.', endColorstr='.$gradientTo.',GradientType=1 );';
            break;
        case "diagonal-bottom":
            $html = 'background: '.$gradientFrom.'; background: -moz-linear-gradient(45deg, '.$gradientFrom.' 0%, '.$gradientTo.' 100%); background: -webkit-linear-gradient(45deg, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); background: linear-gradient(45deg, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='.$gradientFrom.', endColorstr='.$gradientTo.',GradientType=1 );';
            break;
        case "radial":
            $html = 'background: '.$gradientFrom.'; background: -moz-radial-gradient(center, ellipse cover, '.$gradientFrom.' 0%, '.$gradientTo.' 100%); background: -webkit-radial-gradient(center, ellipse cover, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); background: radial-gradient(ellipse at center, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='.$gradientFrom.', endColorstr='.$gradientTo.',GradientType=1 );';
            break;
        default:
            $html = 'background: '.$gradientFrom.'; background: -moz-linear-gradient(left, '.$gradientFrom.' 0%, '.$gradientTo.' 100%); background: -webkit-linear-gradient(left, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); background: linear-gradient(to right, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='.$gradientFrom.', endColorstr='.$gradientTo.',GradientType=1 );';
            break;
        }
        return $html;
    }

    public function generateBlockCss()
    {
        $model = $this->getModel('MGS\Fbuilder\Model\Child');
        $collection = $model->getCollection();
        $customStyle = '';
        foreach ($collection as $child) {
            if ($child->getCustomStyle() != '') {
                $customStyle .= $child->getCustomStyle();
            }
        }
        if ($customStyle!='') {
            try {
                $this->builderHelper->generateFile($customStyle, 'blocks.css', $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/fbuilder/css/'));
                $this->builderHelper->generateFile($customStyle, 'blocks.min.css', $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/fbuilder/css/'));
            } catch (\Exception $e) {

            }
        }
    }

    /* Set value 0 for not exist data */
    public function reInitData($data, $dataInit)
    {
        foreach ($dataInit as $item) {
            if (!isset($data['setting'][$item])) {
                $data['setting'][$item] = 0;
            }
        }
        return $data;
    }

    /* Get position of new block for sort */
    public function getNewPositionOfChild($storeId, $blockName)
    {
        $child = $this->getModel('MGS\Fbuilder\Model\Child')
            ->getCollection()
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('block_name', $blockName)
            ->setOrder('position', 'DESC')
            ->getFirstItem();

        if ($child->getId()) {
            $position = (int) $child->getPosition() + 1;
        } else {
            $position = 1;
        }

        return $position;
    }

    /* Show message after save block */
    public function getMessageHtml($type, $message, $reload)
    {
        $html = '<style type="text/css">
			.container {
				padding: 0px 15px;
				margin-top:60px;
			}
			.page.messages .message {
				padding: 15px;
				font-family: "Lato",arial,tahoma;
				font-size: 14px;
			}
			.page.messages .message-success {
				background-color: #dff0d8;
			}
			.page.messages .message-danger {
				background-color: #f2dede;
			}
		</style>';
        $html .= '<main class="page-main container">
			<div class="page messages"><div data-placeholder="messages"></div><div>
				<div class="messages">
					<div class="message-'.$type.' '.$type.' message" data-ui-id="message-'.$type.'">
						<div>'.$message.'</div>
					</div>
				</div>
			</div>
		</div></main>';

        if ($reload) {
            $html .= '<script type="text/javascript">window.parent.location.reload();</script>';
        }

        return $this->getResponse()->setBody($html);
    }

    public function removePanelImages($type, $data)
    {
        if (isset($data['remove']) && (count($data['remove'])>0)) {
            foreach ($data['remove'] as $filename) {
                $filePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('wysiwyg/'.$type.'/') . $filename;
                if ($this->_file->isExists($filePath)) {
                    $this->_file->deleteFile($filePath);
                }
            }
        }
    }

    public function encodeHtml($html)
    {
        $result = str_replace("<", "&ltchange;", $html);
        $result = str_replace(">", "&gtchange;", $result);
        $result = str_replace('"', '&#34change;', $result);
        $result = str_replace('&#34change;', 'mgs_change_quotation_marks', $result);
        $result = str_replace("'", "&#39change;", $result);
        $result = str_replace("&#39change;", "mgs_apostrophe_change", $result);
        $result = str_replace(",", "&commachange;", $result);
        $result = str_replace("+", "&pluschange;", $result);
        $result = str_replace("{", "&leftcurlybracket;", $result);
        $result = str_replace("}", "&rightcurlybracket;", $result);
        return $result;
    }

    public function noSpace($text)
    {
        $result = str_replace(" ", "&mgs_space;", $text);
        return $result;
    }
}
