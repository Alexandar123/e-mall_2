/**
 * details
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

define(
    [
        'underscore',
        'jquery',
        'Magento_Checkout/js/view/summary/item/details',
        'Magento_Checkout/js/model/quote',
        'MGS_OSCheckout/js/action/update-item',
        'mage/url',
        'mage/translate',
        'Magento_Ui/js/modal/modal'
    ],
    function (_, $, Component, quote, updateItemAction, url, $t, modal) {
        "use strict";

        var products = window.checkoutConfig.quoteItemData,
            giftMessageOptions = window.checkoutConfig.mageConfig.giftMessageOptions,
            qtyIncrements = window.checkoutConfig.mageConfig.qtyIncrements;


        return Component.extend({
            defaults: {
                template: 'MGS_OSCheckout/summary/item/details'
            },
            giftMessageItemsTitleHover: $t('Gift message item'),
            updateQtyDelay: 500,
            updateQtyTimeout: 0,

            getProductUrl: function (parent) {
                var item = _.find(products, function (product) {
                    return product.item_id == parent.item_id;
                });

                if (item && item.hasOwnProperty('product') &&
                    item.product.hasOwnProperty('request_path') && item.product.request_path) {
                    return url.build(item.product.request_path);
                }

                return false;
            },

            /**
             * Init popup gift message item window
             * @param element
             */
            setModalElement: function (element, item_id) {
                var self = this;
                this.modalWindow = element;
                var options = {
                    'type': 'popup',
                    'title': $t('Gift Message Item &#40' + element.title + '&#41'),
                    'modalClass': 'popup-gift-message-item',
                    'responsive': true,
                    'innerScroll': true,
                    'trigger': '#' + element.id,
                    'buttons': [],
                    'opened': function () {
                        self.loadGiftMessageItem(item_id);
                    }
                };
                modal(options, $(this.modalWindow));
            },

            loadGiftMessageItem: function (itemId) {
                $('.popup-gift-message-item._show #item' + itemId).find('input:text,textarea').val('');
                if (giftMessageOptions.giftMessage.itemLevel[itemId].hasOwnProperty('message')
                    && typeof giftMessageOptions.giftMessage.itemLevel[itemId]['message'] == 'object') {
                    $(this.createSelectorElement(itemId + ' .action.delete')).show();
                    return this;
                }

                $(this.createSelectorElement(itemId + ' .action.delete')).hide();
            },

            createSelectorElement: function (selector) {
                return '.popup-gift-message-item._show #item' + selector;
            },


            updateGiftMessageItem: function (itemId) {
                var data = {
                    gift_message: {}
                };
                giftMessageItem(data, itemId, false);
                this.closePopup();
            },

            deleteGiftMessageItem: function (itemId) {
                giftMessageItem({
                    gift_message: {
                        sender: '',
                        recipient: '',
                        message: ''
                    }
                }, itemId, true);
                this.closePopup();
            },

            /**
             * Close popup gift message item
             */
            closePopup: function () {
                $('.action-close').trigger('click');
            },

            isItemAvailable: function (itemId) {
                var isGloballyAvailable,
                    itemConfig;
                var item = _.find(products, function (product) {
                    return product.item_id == itemId;
                });
                if (item.is_virtual == true || !giftMessageOptions.isEnableGiftMessageItems) return false;

                // gift message product configuration must override system configuration
                isGloballyAvailable = this.getConfigValue('isItemLevelGiftOptionsEnabled');
                itemConfig = giftMessageOptions.giftMessage.hasOwnProperty('itemLevel')
                && giftMessageOptions.giftMessage.itemLevel.hasOwnProperty(itemId) ?
                    giftMessageOptions.giftMessage.itemLevel[itemId] : {};

                return itemConfig.hasOwnProperty('is_available') ? itemConfig['is_available'] : isGloballyAvailable;
            },
            getConfigValue: function (key) {
                return giftMessageOptions.hasOwnProperty(key) ?
                    giftMessageOptions[key]
                    : false;
            },

            plusQty: function (item, event) {
                var self = this;
                clearTimeout(this.updateQtyTimeout);

                var target = $(event.target).parent().siblings(".item_qty"),
                    itemId = parseInt(target.attr("id")),
                    qty = parseInt(target.val());

                if (qtyIncrements.hasOwnProperty(itemId)) {
                    var qtyDelta = qtyIncrements[itemId];

                    qty = (Math.floor(qty / qtyDelta) + 1) * qtyDelta;
                } else {
                    qty += 1;
                }

                target.val(qty);

                this.updateQtyTimeout = setTimeout(function () {
                    self.updateItem(itemId, qty, target)
                }, this.updateQtyDelay);
            },

            minusQty: function (item, event) {
                var self = this;
                clearTimeout(this.updateQtyTimeout);

                var target = $(event.target).parent().siblings(".item_qty"),
                    itemId = parseInt(target.attr("id")),
                    qty = parseInt(target.val());

                if (qtyIncrements.hasOwnProperty(itemId)) {
                    var qtyDelta = qtyIncrements[itemId];

                    qty = (Math.ceil(qty / qtyDelta) - 1) * qtyDelta;
                } else {
                    qty -= 1;
                }

                target.val(qty);

                this.updateQtyTimeout = setTimeout(function () {
                    self.updateItem(itemId, qty, target)
                }, this.updateQtyDelay);
            },
            changeQty: function (item, event) {
                var target = $(event.target),
                    itemId = parseInt(target.attr("id")),
                    qty = parseInt(target.val());

                if (qtyIncrements.hasOwnProperty(itemId) && (qty % qtyIncrements[itemId])) {
                    var qtyDelta = qtyIncrements[itemId];

                    qty = (Math.ceil(qty / qtyDelta) - 1) * qtyDelta;
                }

                this.updateItem(itemId, qty, target);
            },

            removeItem: function (itemId) {
                this.updateItem(itemId);
            },

            updateItem: function (itemId, itemQty, target) {
                var self = this,
                    payload = {
                        item_id: itemId
                    };

                if (typeof itemQty !== 'undefined') {
                    payload['item_qty'] = itemQty;
                }

                updateItemAction(payload).fail(function (response) {
                    target.val(self.getProductQty(itemId));
                });

                return this;
            },

            getProductQty: function (itemId) {
                var item = _.find(quote.totals().items, function (product) {
                    return product.item_id == itemId;
                });

                if (item && item.hasOwnProperty('qty')) {
                    return item.qty;
                }

                return 0;
            }
        });
    }
);
