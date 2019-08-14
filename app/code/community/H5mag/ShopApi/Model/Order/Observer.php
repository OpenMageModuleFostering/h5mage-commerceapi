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
 * @copyright   Copyright (c) 2015 H5mag Inc. (http://www.h5mag.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Observer for H5mag
 *
 * @category   H5mag ShopApi
 * @package    H5mag_ShopApi
 */
class H5mag_ShopApi_Model_Order_Observer {

	/**
	* Constructor
	*/
	public function __construct() {
	}
	
	/**
	* Empty shopping cart
	* 
	* @param   Varien_Event_Observer $observer
	* @return  H5mag_ShopApi_Model_Order_Observer
	*/
	public function emptyCart($observer) {
		$controller = $observer->getAction();
		if ($controller instanceOf Mage_Checkout_OnepageController) {
			$action = $controller->getFullActionName();
			if ($action == 'checkout_onepage_success') {
				$layout = $observer->getLayout();
				$layout->getUpdate()->addUpdate('
					<reference name="content">
						<block name="shopapi.cart" type="core/template" template="h5mag/shopapi/emptycart.phtml">
						</block>
					</reference>
				');
			}
		}
		return $this;
	}
	
}
