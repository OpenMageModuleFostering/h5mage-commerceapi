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
 * @copyright   Copyright (c) 2013 H5mag Inc. (http://www.h5mag.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Generic Product model for H5mag
 *
 * @category    H5mag ShopApi
 * @package     H5mag_ShopApi
 */
class H5mag_ShopApi_Model_Generic extends H5mag_ShopApi_Model_Product {

	/**
	* Constructor of this object
	*
	*/
	public function __construct() {
		$this->_init('h5mag_shopapi/generic');
		parent::_construct();
	}

	/**
	* Set the locale that will be used when fetching product information 
	*
	* @param string $locale Locale to be used. e.g. en_us
	* @return void
	*/
	public function setLocale($locale) {
		$stores = Mage::getModel('core/store')->getCollection();
		foreach ($stores as $store) {
			$store->load();
			if (strtolower($store->getConfig('general/locale/code')) == strtolower($locale)) {
				$this->storeId = $store->getId();
				$this->locale = $locale;
				break;
			}
		}
	}
	
	/**
	* Get the JSON representation of this product
	*
	* @return string json encoded string
	*/
	public function fetch($id) {
		if (empty($id)) throw new Exception(E_NO_PRODUCT_ID_SPECIFIED);

		// Use locale to set current store or use the default one
		if (empty($this->storeId)) $this->storeId = Mage::app()->getStore()->getId();
		Mage::app()->setCurrentStore($this->storeId);
		
		// Fetch our product
		$product = Mage::getModel('catalog/product')->load($id);
		
		
		if ($product->getId()) {
			// Add product description to data
			$this->data = array(
				'id' => $product->getId(), 
				'name' => $product->getName(), 
				'text' => $product->getDescription(),
			);
			$this->data['locale'] = (!empty($this->locale)) ? $this->locale : Mage::app()->getStore()->getConfig('general/locale/code');

			// Get variants of this product
			$variants = array();
			if ($product->isConfigurable()) {
				$variants = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $product);
				$this->data['variants'] = array();
			} else if ($product->getTypeId() == 'simple') {
				$parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product->getId());
				if(!$parentIds) {
					$parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
			    if(isset($parentIds[0])){
						$parent = Mage::getModel('catalog/product')->load($parentIds[0]);
						$variants = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null,$parent);
						$variants_list = array();
						foreach($variants as $variant) {
							array_push($variants_list, Mage::getModel('catalog/product')->load($variant->getId()));
						}
						$variants = $variants_list;
					} else {
						$variants[] = $product;
					}
				} else {
					$variants[] = $product;
				}
			}
			
			// Add variants to data
			foreach ($variants as $variant) {
				$store = Mage::getModel('core/store')->load($variant->getStoreId());
				$data = array();
				$data['currency'] = $store->getCurrentCurrencyCode();
				$data['id'] = $variant->getSku(); // Send the SKU instead of the actual database id
				$data['name'] = $variant->getName();
				var_dump($variant);
				$data['price'] = $variant->getPrice() * 100; // H5mag uses cents
				$data['pictures'] = array();
				$data['stock'] = 0;
				$data['available'] = false;
				if ($variant->isAvailable()) {
					$inventory = Mage::getModel('cataloginventory/stock_item')->loadByProduct($variant);
					$data['stock'] = (int)$inventory->getQty();
					$data['available'] = true;
				}
				// Get images
				if ($image = $variant->getImageUrl()) $data['pictures'][] = $image;
				if ($image = $variant->getSmallImageUrl()) $data['pictures'][] = $image; 
				if ($image = $variant->getThumbnailUrl()) $data['pictures'][] = $image;
			
				// Optional properties 
				// TODO: Move these to config.xml and make an admin panel where these can be configured
				/*if ($variant->offsetExists('color') && $color = $variant->getAttributeText('color') ) {
					$data['color'] = $color;
				}
				if ($variant->offsetExists('hexcolor') && $hex_color = $variant->getAttributeText('hexcolor') ) {
					$data['display-color'] = "#{$hex_color}";
				}*/
				
				if ($dimensions = $variant->getDimensions()) $data['dimensions'] = $dimensions;
				$this->data['variants'][] = $data;
			}
		}
	}
	
	/**
	* Get the JSON representation of this product
	*
	* @return string json encoded string
	*/
	public function json() {
			if (!empty($this->data)) {
				return json_encode($this->data);
			} else {
				return json_encode(array('message' => self::E_PRODUCT_NOT_FOUND));
			}
		}

}
