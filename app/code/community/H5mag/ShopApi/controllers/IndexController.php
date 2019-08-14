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
class H5mag_ShopApi_IndexController extends Mage_Core_Controller_Front_Action {
	private function testHash($hash) {
		$previousHashTime = floor((time()-300)/300);
		$currentHashTime = floor(time()/300);
		$apiKey = Mage::app()->getStore()->getConfig('h5mag_shopapi_magazine/general/apikey');
		$previousHash = sha1($apiKey . $previousHashTime);
		$currentHash = sha1($apiKey . $currentHashTime);
		if (strlen($currentHash) === strlen($hash) && $currentHash === $hash) {
			return true;
		} else if (strlen($previousHash) === strlen($hash) && $previousHash === $hash) {
			return true;
		}
		return false;
	}
	/**
	* Get product and variants
	*/
	public function indexAction() {
		$callback = $this->getRequest()->getParam('callback');
		$hash = $this->getRequest()->getParam('key');
		if (Mage::app()->getStore()->getConfig('h5mag_shopapi_magazine/general/apikey') != "" && $this->testHash($hash)) {
			$product_id = $this->getRequest()->getParam('id');
			if (empty($product_id)) throw new Exception(E_NO_PRODUCT_ID_SPECIFIED);
			$product = Mage::getModel('h5mag_shopapi/'. Mage::app()->getStore()->getConfig('h5mag_shopapi_developer/general/model'));
			if (!$product) $product = Mage::getModel('h5mag_shopapi/generic');
			$locale = $this->getRequest()->getParam('locale');
			if (!empty($locale)) $product->setLocale($locale);
			$product->fetch($product_id);
			$this->send($callback.'('.$product->json().')');
		} else {
			$this->send($callback.'([])');
		}
	}

	/**
	* Get product and variants
	*/
	public function productsAction() {
		$hash = $this->getRequest()->getParam('key');
		if (Mage::app()->getStore()->getConfig('h5mag_shopapi_magazine/general/apikey') != "" && $this->testHash($hash)) {
			$product = Mage::getModel('h5mag_shopapi/'. Mage::app()->getStore()->getConfig('h5mag_shopapi_developer/general/model'));
			if (!$product) $product = Mage::getModel('h5mag_shopapi/generic');
			$locale = $this->getRequest()->getParam('locale');
			if (!empty($locale)) $product->setLocale($locale);
			$product->fetchAll($product_id);
			$this->send($callback.'('.$product->json().')');
		} else {
			$this->send($callback.'([])');
		}
	}
	
	/**
	* Send JSON data back to client
	*/
	private function send($data){
		header('Content-Type: application/json; charset=utf-8');
		header('Content-Length: '.strlen($data));
		echo ($data);
		exit;
	}
	
}