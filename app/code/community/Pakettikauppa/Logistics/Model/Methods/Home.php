<?php
class Pakettikauppa_Logistics_Model_Methods_Home
extends Mage_Shipping_Model_Carrier_Abstract
implements Mage_Shipping_Model_Carrier_Interface
{
  protected $_code = 'pktkp_homedelivery';
  protected $_home_methods;

  function __construct(){
    $this->_home_methods = Mage::helper('pakettikauppa_logistics/API')->getHomeDelivery();
  }

  public function collectRates(Mage_Shipping_Model_Rate_Request $request)
  {
      if(!Mage::helper('core')->isModuleOutputEnabled('Pakettikauppa_Logistics')){
        return false;
      }
      /** @var Mage_Shipping_Model_Rate_Result $result */
      $result = Mage::getModel('shipping/rate_result');
      $methods = $this->_home_methods;
      if(count($methods)>0){
        foreach($methods as $method){
          $result->append($this->_getCustomRate($method->service_provider,$method->name,$method->shipping_method_code, 999));
        }
      }
      return $result;

  }
  /**
   * Returns Allowed shipping methods
   *
   * @return array
   */
  public function getAllowedMethods()
  {
    $methods = $this->_home_methods;
    if(count($methods)>0){
      foreach($methods as $carrier){
        $array['shipping_'.$carrier->shipping_method_code] = $carrier->name;
      }
      return $array;
    }

  }


  protected function _getCustomRate($name, $description, $method_code, $price)
  {

      // EDIT SHIPPING PRICE HERE
      $price = $this->getConfigData('price');

      /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
      $rate = Mage::getModel('shipping/rate_result_method');
      $rate->setCarrier($this->_code);
      $rate->setCarrierTitle($this->getConfigData('title'));
      $rate->setMethod($method_code);
      $rate->setMethodTitle($name);
      $rate->setMethodDescription($description);
      $rate->setPrice($price);
      $rate->setCost(0);
      return $rate;
  }

  public function isTrackingAvailable()
  {
      return true;
  }

  public function getTrackingInfo($tracking)
  {
    $title = Mage::helper('pakettikauppa_logistics')->getCarrierTitleBasedonTracking($tracking);
    $base_url = Mage::getUrl('pakettikauppalogistics/shipment/index/');
    $track = Mage::getModel('shipping/tracking_result_status');
    $track->setUrl($base_url.'code/'.$tracking)
          ->setCarrierTitle($title)
          ->setTracking($tracking);
    return $track;
  }
}
