<?php
/**
 *
 * Copyright Â© 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\ClaueTheme\Controller\Element;

use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Cache\Manager as CacheManager;


class Save extends \MGS\Fbuilder\Controller\Element\Save
{
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

        if (isset($_FILES['background_image']['name']) && $_FILES['background_image']['name'] != '') {
            $uploader = $this->_fileUploaderFactory->create(['fileId' => 'background_image']);
            $file = $uploader->validateFile();

            if (($file['name']!='') && ($file['size'] >0)) {
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);

                $path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/fbuilder/backgrounds');

                $uploader->save($path);
                $data['background_image'] = $uploader->getUploadedFileName();
//                var_dump($uploader->getUploadedFileName());die('11111');
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
            $this->getModel('MGS\Fbuilder\Model\Child')->setCustomStyle($customStyle)->setId($model->getId())->save();
            $this->generateBlockCss();
            return $this->getMessageHtml('success', $sessionMessage, true);
        } catch (\Exception $e) {
            return $this->getMessageHtml('danger', $e->getMessage(), false);
        }
    }

}
