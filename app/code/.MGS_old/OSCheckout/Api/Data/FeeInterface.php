<?php
/**
 * FeeInterface
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 */

namespace MGS\OSCheckout\Api\Data;


interface FeeInterface
{
    const ENTITY_ID = 'id';
    const QUOTE_ID = 'quote_id';
    const ORDER_ID = 'order_id';
    const BASE_AMOUNT = 'base_amount';
    const AMOUNT = 'amount';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return int|null
     */
    public function getOrderId();

    /**
     * @return int|null
     */
    public function getQuoteId();

    /**
     * @return int
     */
    public function getAmount();

    /**
     * @return int
     */
    public function getBaseAmount();

    /**
     * @param int $id
     * @return \MGS\OSCheckout\Api\Data\FeeInterface
     */
    public function setOrderId($id);

    /**
     * @param int $amount
     * @return \MGS\OSCheckout\Api\Data\FeeInterface
     */
    public function setAmount($amount);

    /**
     * @param int $id
     * @return \MGS\OSCheckout\Api\Data\FeeInterface
     */
    public function setQuoteId($id);

    /**
     * @param int $amount
     * @return \MGS\OSCheckout\Api\Data\FeeInterface
     */
    public function setBaseAmount($amount);
}
