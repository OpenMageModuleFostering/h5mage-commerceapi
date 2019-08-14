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
 * Abstract model for Product
 * @category    H5mag ShopApi
 * @package     H5mag_ShopApi
 *
 */
abstract class H5mag_ShopApi_Model_Product extends Mage_Core_Model_Abstract {
	
	/* Loaded data goes here */ 
	protected $data; 
	
	/* Locale */
	protected $locale;
	
	/* Store id to use */
	protected $storeId;
	
	/* Error messages */
	const E_NO_PRODUCT_ID_SPECIFIED = 'No product ID specified';
	const E_PRODUCT_NOT_FOUND = 'Sorry, we cannot find the requested product in our shop.';
	
	/**
	* Set the locale that will be used when fetching product information 
	*
	* @param string $locale Locale to be used. e.g. en_us
	* @return void
	*/
	abstract public function setLocale($locale);
	
	/**
	* Load the product.
	*
	* @param integer $id product ID
	* @return void
	*/
	abstract public function fetch($id);
	
	/**
	* Get the JSON representation of this product
	*
	* @return string json encoded string
	*/
	abstract public function json();

}