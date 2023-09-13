<?php
/**
 * GiftMessageInformationManagement
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

namespace MGS\OSCheckout\Model;

use MGS\OSCheckout\Api\GiftMessageInformationManagementInterface;

class GiftMessageInformationManagement implements GiftMessageInformationManagementInterface
{
    /**
     * @var \Magento\GiftMessage\Api\CartRepositoryInterface
     */
    protected $cartRepository;
    /**
     * @var \Magento\GiftMessage\Api\ItemRepositoryInterface
     */
    protected $itemRepository;
    /**
     * @var \Magento\GiftMessage\Model\MessageFactory
     */
    protected $messageFactory;

    public function __construct(
        \Magento\GiftMessage\Api\CartRepositoryInterface $cartRepository,
        \Magento\GiftMessage\Api\ItemRepositoryInterface $itemRepository,
        \Magento\GiftMessage\Model\MessageFactory        $messageFactory
    )
    {
        $this->cartRepository = $cartRepository;
        $this->itemRepository = $itemRepository;
        $this->messageFactory = $messageFactory;
    }

    public function update($cartId, $giftMessage)
    {
        foreach ($giftMessage as $messageData) {

            /** @var \Magento\GiftMessage\Model\Message $message */
            $message = $this->messageFactory->create();

            $message->setData([
                'message' => $messageData['message'],
                'sender' => $messageData['sender'],
                'recipient' => $messageData['recipient'],
            ]);

            if ($messageData['item_id'] == \MGS\OSCheckout\Model\Gift\Messages::QUOTE_MESSAGE_INDEX) {
                $this->cartRepository->save($cartId, $message);
            } else {
                $this->itemRepository->save($cartId, $message, $messageData['item_id']);
            }
        }

        return true;
    }
}
