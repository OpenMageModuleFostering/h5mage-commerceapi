<?php
/**
 * H5mag
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@h5mag.com so we can send you a copy immediately.
 *
 * @category    H5mag ShopApi
 * @package     H5mag_ShopApi
 * @copyright   Copyright (c) 2015 H5mag (http://www.h5mag.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 class H5mag_ShopApi_CheckoutController extends Mage_Core_Controller_Front_Action {

	/**
	* Put products in shopping cart, update session and redirect to checkout/cart
	*/
	public function indexAction() {
		$skus = $this->getRequest()->getParam('sku');
		// Redirect user to checkout cart if no skus have been sent
		if (empty($skus)) $this->_redirect('checkout/cart');

		// Add products to shopping cart
		$cart = Mage::getModel('checkout/cart')->init()->truncate();
		foreach ($skus as $sku => $quantity) {
			$product = Mage::getModel('catalog/product');
			$product->load($product->getIdBySku($sku));
			if ($product->getId() && $product->isAvailable()) {
				$cart->addProduct($product, array('qty' => $quantity));
			}
		}
		$cart->save();
		Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
		
		// Update cart in session
		if ($cart->getItemsCount() > 0) {
			$empty_cart = $this->getRequest()->getParam('cart');
			$url = Mage::app()->getStore()->getConfig('h5mag_shopapi_magazine/general/url');
			$url = rtrim($url, '/');
			Mage::getSingleton('core/session')->setH5magShopApiCartUrl("{$url}/system/shop/empty-cart?cart={$empty_cart}");
			$this->_redirect('checkout/cart');
		} else {
			echo 'Products out-of-stock.';
		}
	}

}
