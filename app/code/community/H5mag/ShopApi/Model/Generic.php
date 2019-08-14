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
			
			$variants = array();

			if ($product->isConfigurable()) {
				$variants = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $product);
			} else if ($product->getTypeId() == 'simple') {
				$parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product->getTypeId);
				if (empty($parentIds)) {
					$parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
					if (isset($parentIds[0])) {
						$product = Mage::getModel('catalog/product')->load($parentIds[0]);
						$variants = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null,$product);       
					} else {
						$variants[] = $product;
					}
				} else {
					$variants[] = $product;
				}
			}

			$this->data = array(
				'id' => $product->getId(), 
				'name' => $product->getName(), 
				'text' => $product->getDescription(),
			);
			$this->data['locale'] = (!empty($this->locale)) ? $this->locale : Mage::app()->getStore()->getConfig('general/locale/code');

			
			// Add variants to data
			foreach ($variants as $variant) {
				$store = Mage::getModel('core/store')->load($variant->getStoreId());
				$data = array();
				$data['currency'] = $store->getCurrentCurrencyCode();
				$data['id'] = $variant->getSKU(); // Send the SKU instead of the actual database id
				if ($product->isConfigurable()) {
					if ($variant->getName() == null) {
						$nameSuffix = '';
						$productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
						$attributeOptions = array();
						foreach ($productAttributeOptions as $productAttribute) {
					       $nameSuffix .= ' ' . $variant->getAttributeText(strtolower($productAttribute['label']));
						}

						$data['name'] = $product->getName() . $nameSuffix; 
						$data['price'] = $product->getPrice() * 100;
					} else {
						$nameSuffix = '';
						$nameSuffix = '';
						$productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
						$attributeOptions = array();
						foreach ($productAttributeOptions as $productAttribute) {
					       $nameSuffix .= ' ' . $variant->getAttributeText(strtolower($productAttribute['label']));
						}
						$data['name'] = $variant->getName() . $nameSuffix; 
						$data['price'] = $variant->getPrice() * 100; // H5mag uses cents
					}
				} else {
					$data['name'] = $variant->getName();
					$data['price'] = $variant->getPrice() * 100;
				}
				
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
				
				if ($dimensions = $variant->getDimensions()) $data['dimensions'] = $dimensions;
				
				if ($id == $variant->getId()) {
					array_unshift($this->data['variants'], $data);
				} else {
					$this->data['variants'][] = $data;
				}
			}
		}
	}
	/**
	* Get the JSON representation of this product
	*
	* @return string json encoded string
	*/
	public function fetchAll() {
		
		// Use locale to set current store or use the default one
		if (empty($this->storeId)) $this->storeId = Mage::app()->getStore()->getId();
		Mage::app()->setCurrentStore($this->storeId);
		
		// Fetch our product
		$products = Mage::getModel('catalog/product')->getCollection()->getData(); 
		$productList = array();
		foreach($products as $product) {
			if ($product['type_id'] == 'simple') {
				$prod = Mage::getModel('catalog/product')->load($product['entity_id']);
				$store = Mage::getModel('core/store')->load($prod->getStoreId());
				array_push($productList, array("name" => $prod->name, "description" => $prod->description, "sku" => $prod->sku, "currency" => $store->getCurrentCurrencyCode(), "image"=> $prod->getSmallImageUrl(), "price" => $prod->price));
			}
		}
		
		$this->data = $productList;
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
