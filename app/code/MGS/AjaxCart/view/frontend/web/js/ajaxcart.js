/**
* Copyright © 2015 Magento. All rights reserved.
* See COPYING.txt for license details.
*/
define([
	'uiComponent',
	'Magento_Customer/js/customer-data',
	'jquery',
	'ko',
	'mgsAjaxCartFooter',
	'Magento_Ui/js/modal/modal'
], function (Component, customerData, $, ko) {
	'use strict';
	var sidebarCart = $('[data-block="footer_minicart"]');
	var addToCartCalls = 0;
	var sidebarInitialized = false;

	function initSidebar() {
		if (sidebarCart.data('mageSidebar')) {
			sidebarCart.mgsAjaxCartFooter('update');
		}
		sidebarCart.trigger('contentUpdated');
		if (sidebarInitialized) {
			return false;
		}
		sidebarInitialized = true;
		sidebarCart.mgsAjaxCartFooter({
			"targetElement": "#footer-mini-cart",
			"url": {
				"checkout": window.checkout.checkoutUrl,
				"update": window.checkout.updateItemQtyUrl,
				"remove": window.checkout.removeItemUrl,
				"loginUrl": window.checkout.customerLoginUrl,
				"isRedirectRequired": window.checkout.isRedirectRequired
			},
			"button": {
				"checkout": "#footer-cart-btn-checkout",
				"remove": ".item a.action.delete",
				"close": ""
			},
			"minicart": {
				"list": "",
				"content": "",
				"qty": "",
				"subtotal": ""
			},
			"item": {
				"qty": "input.cart-item-qty",
				"button": "button.update-cart-item"
			},
			"confirmMessage": $.mage.__(
				'Are you sure you would like to remove this item from the shopping cart?'
			)
		});
	}

	return Component.extend({
		ajaxcart: ko.observable({}),
		toggleFooterSidebar: function(){
			$('#footer-cart-trigger').toggleClass('active');
			$('#footer-mini-cart').slideToggle(300);	
		},
		initSidebar: initSidebar,
		initialize: function () {
			var self = this;
			this._super();
			this.cartSidebar = customerData.get('cart');
			window.cartSidebar = self.cartSidebar;
			$('#fixed-cart-footer').show();
			initSidebar();
			this.cartSidebar.subscribe(function () {
				addToCartCalls--;
				sidebarInitialized = false;				
				initSidebar();
			}, this);
			$('[data-block="minicart"]').on('contentLoading', function(event) {
				addToCartCalls++;				
			});
		},

	});
});
