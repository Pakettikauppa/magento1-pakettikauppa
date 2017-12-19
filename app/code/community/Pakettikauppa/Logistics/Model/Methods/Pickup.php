<?php
class Pakettikauppa_Logistics_Model_Methods_Pickup
extends Mage_Shipping_Model_Carrier_Abstract
implements Mage_Shipping_Model_Carrier_Interface
{

  protected $_code = 'pktkp_pickuppoint';
  protected $_pickup_methods;


  function __construct(){
    $zip = Mage::helper('pakettikauppa_logistics')->getZip();
    $this->_pickup_methods = Mage::helper('pakettikauppa_logistics/API')->getPickupPoints($zip);
  }

  private function getZip(){
    return Mage::helper('pakettikauppa_logistics')->getZip();
  }

  public function collectRates(Mage_Shipping_Model_Rate_Request $request)
  {

      if(!Mage::helper('core')->isModuleOutputEnabled('Pakettikauppa_Logistics')){
        return false;
      }

      /** @var Mage_Shipping_Model_Rate_Result $result */
      $result = Mage::getModel('shipping/rate_result');
      if($this->getZip()){
        $methods = $this->_pickup_methods;
        if(count($methods)>0){
          $methods = Mage::helper('pakettikauppa_logistics')->sortPickupPointsByDistance($methods);
          foreach($methods as $method){
            $method_provider = str_replace(' ', '', strtolower($method->provider));
            if(Mage::getStoreConfig('carriers/'.$method_provider.'_pickuppoint/active') == 1){
              $description = '('.$method->provider.') '.$method->name.' | '.$method->street_address.', '.$method->city.', '.$method->postcode;
              $name = $method->provider;
              $price = Mage::getStoreConfig('carriers/'.$method_provider.'_pickuppoint/price');
              if($price == '' || $price == null){
                $price = 99999;
              }else{
                $price = floatval($price);
              }
              $result->append($this->_getCustomRate($name,$description,$method->pickup_point_id, $price));
            }
          }
        }
        return $result;
      }else{
        return $result;
      }
  }
  /**
   * Returns Allowed shipping methods
   *
   * @return array
   */
  public function getAllowedMethods()
  {
    if($this->getZip()){
      $methods = $this->_pickup_methods;
      if(count($methods)>0){
        foreach($methods as $carrier){
          $array['shipping_'.$carrier->shipping_method_code] = $carrier->name;
        }
        return $array;
      }
    }
  }


  protected function _getCustomRate($name, $description, $method_code, $price)
  {
      /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
      $rate = Mage::getModel('shipping/rate_result_method');
      $rate->setCarrier($this->_code);
      $rate->setCarrierTitle($this->getConfigData('title'));
      $rate->setMethod($method_code);
      $rate->setMethodTitle($description);
      $rate->setMethodDescription($name);
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
