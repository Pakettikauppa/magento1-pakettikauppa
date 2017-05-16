<?php
require_once 'Mage/Checkout/controllers/OnepageController.php';
class Pakettikauppa_Logistics_Checkout_OnepageController
extends Mage_Checkout_OnepageController{
  public function savePickuppointZipAction(){
    $code = $_GET['code'];
    $checkout = Mage::getSingleton('checkout/session')->getQuote();
    $methods = $checkout->getShippingAddress()->getShippingRatesCollection()->getData();
     foreach($methods as $method){
       if($method['carrier'] == 'pakettikauppa_pickuppoint' && $method['code'] == $code ){
         $checkout->setData('pickup_point_location', $method['method_description']);
         $checkout->save();
       }
     }
  }
  public function reloadShippingMethodsAction(){

    $cart = Mage::getSingleton('checkout/cart');

    $zip = $cart->getQuote()->getShippingAddress()->getPostcode();
    $country = $cart->getQuote()->getShippingAddress()->getCountryId();

    $zipcode = $_GET['zip'];
    $address = $cart->getQuote()->getShippingAddress();
    $address->setCountryId($country)
            ->setPostcode($zipcode)
            ->setCollectShippingrates(true);
    $cart->save();

    $result['update_section'] = array(
            'name' => 'shipping-method',
            'html' => $this->_getShippingMethodsHtml()
    );

    // RETURN CART TO PREVIOUS STATE
    $cart = Mage::getSingleton('checkout/cart');
    $address = $cart->getQuote()->getShippingAddress();
    $address->setCountryId($country)
            ->setPostcode($zip)
            ->setCollectShippingrates(true);
    $cart->save();

    return $this->_prepareDataJSON($result);

  }
}
