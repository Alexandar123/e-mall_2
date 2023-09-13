<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Testimonial\Helper;

/**
 * Contact base helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function decodeHtmlTag($content){
		$result = str_replace("&lt;","<",$content);
		$result = str_replace("&gt;",">",$result);
		$result = str_replace('&#34;','"',$result);
		$result = str_replace("&#39;","'",$result);
		return $result;
	}
    public function convertPerRowtoCol($perRow){
		switch ($perRow) {
            case 1:
                $result = 12;
                break;
            case 2:
                $result = 6;
                break;
            case 3:
                $result = 4;
                break;
            case 4:
                $result = 3;
                break;
            case 5:
                $result = 'custom-5';
                break;
            case 6:
                $result = 2;
				break;
			case 7:
                $result = 'custom-7';
				break;
			case 8:
                $result = 'custom-8';
                break;
        }
		return $result;
	}
    public function convertColClass($col, $type){
		if(($type=='row') && ($col=='custom-5' || $col=='custom-7' || $col=='custom-8')){
			return 'row-'.$col;
		}
		if($type=='col'){
			if(($col=='custom-5' || $col=='custom-7' || $col=='custom-8')){
				return 'col-md-'.$col. ' col-sm-3 col-xs-6';
			}else{
				$class = 'col-lg-'.$col.' col-md-'.$col;
				if($col==12){
					$class .= ' col-sm-12 col-xs-12';
				}
				if($col==6){
					$class .= ' col-sm-6 col-xs-6';
				}
				if(($col==4) || ($col==3)){
					$class .= ' col-sm-4 col-xs-6';
				}
				if($col==2){
					$class .= ' col-sm-3 col-xs-6';
				}
				
				return $class;
			}
		}
	}
}