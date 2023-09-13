<?php
/**
 * Copyright © 2017 Sam Granger. All rights reserved.
 *
 * @author Sam Granger <sam.granger@gmail.com>
 */

namespace MGS\ThemeSettings\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;


class AddAttributeToSelect implements ObserverInterface
{
    public function execute(Observer $observer){
        $select = $observer->getSelect();
		$select->columns('fbuilder_thumbnail');
		$select->columns('fbuilder_icon');
		$select->columns('fbuilder_font_class');
		$select->columns('description');
        return $select;
    }
}