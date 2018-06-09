<?php
class Pakettikauppa_Logistics_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getShipmentStatusText($code){
      switch ($code) {
          case "13":
              $status = "Item is collected from sender - picked up";
              break;
          case "20":
              $status = "Exception";
              break;
          case "22":
              $status = "Item has been handed over to the recipient";
              break;
          case "31":
              $status = "Item is in transport";
              break;
          case "38":
              $status = "C.O.D payment is paid to the sender";
              break;
          case "45":
              $status = "Informed consignee of arrival";
              break;
          case "48":
              $status = "Item is loaded onto a means of transport";
              break;
          case "56":
              $status = "Item not delivered – delivery attempt made";
              break;
          case "68":
              $status = "Pre-information is received from sender";
              break;
          case "71":
              $status = "Item is ready for delivery transportation";
              break;
          case "77":
              $status = "Item is returning to the sender";
              break;
          case "91":
              $status = "Item is arrived to a post office";
              break;
          case "99":
              $status = "Outbound";
              break;
          default:
              $status = "Unknown";
      }
      return $status;
    }

    public function getCarrierTitleBasedonTracking($tracking_number){
      $trackings = Mage::getResourceModel('sales/order_shipment_track_collection')->addAttributeToSelect('*')
                    ->addAttributeToFilter('track_number',$tracking_number)
                    ->addAttributeToFilter('carrier_code',['in' => ['pktkp_pickuppoint', 'pktkp_homedelivery']]);
     if(count($trackings)==1){
       foreach($trackings as $track){
         return $track->getTitle();
       }
     }else{
       return 'Unknown carrier or multiple carriers';
     }
    }

    public function getCurrentCarrierTitle($code){
      $methods = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getShippingRatesCollection()->getData();
      foreach($methods as $method){
        if($method['code'] == $code){
          if($method['carrier'] == 'pktkp_homedelivery'){
            $title = $method['method_title'];
          }
          if($method['carrier'] == 'pktkp_pickuppoint'){
            $title =  $method['method_description'];
          }
        }
      }
      if(isset($title)){
        return $title;
      }else{
        return 'Unknown';
      }
    }
    public function getZip(){
      $zip_pickup = Mage::getSingleton('checkout/cart')->getQuote()->getData('pickup_point_zip');
      $zip_shipping = Mage::getSingleton('checkout/cart')->getQuote()->getShippingAddress()->getPostcode();
       if(isset($zip_pickup)){
         return $zip_pickup;
      }elseif($zip_shipping != '-'){
        return $zip_shipping;
      }else{
        return false;
      }
    }

    public function getMethodImage($code,$provider){

      $icons = Mage::registry('shipping-icons');
      if($icons[$provider] == 'missing'){
        return false;
      }else{
        return '<img class="shipping_provider_logo_logistics" src="'.$icons[$provider].'" alt="'.$provider.'"/>';
      }
    }

    public function isPakettikauppa($code){
      if(strpos($code, 'pktkp_pickuppoint') !== false || strpos($code, 'pktkp_homedelivery') !== false) {
        return true;
      }else{
        return false;
      }
    }

    public function getMethod($code){
      if(strpos($code, 'pktkp_pickuppoint') !== false) {
        return 'pktkp_pickuppoint';
      }
      if(strpos($code, 'pktkp_homedelivery') !== false) {
        return 'pktkp_homedelivery';
      }
    }

    public function getCode($code){
      if(strpos($code, 'pktkp_pickuppoint') !== false || strpos($code, 'pktkp_homedelivery') !== false) {
        $shipping_code = str_replace('pktkp_pickuppoint', '', $code);
        $shipping_code = str_replace('pktkp_homedelivery', '', $shipping_code);
        return $shipping_code;
      }
    }

    public function sortPickupPointsByDistance($data){
      $distance = [];
      $nulls = [];
      $results = [];
      foreach($data as $d){
        if($d->distance == null){
          array_push($nulls,$d->distance);
        }else{
          array_push($distance,$d->distance);
        }
      }
      asort($distance);
      foreach($distance as $dist){
        foreach($data as $d){
          if($d->distance == $dist){
            array_push($results,$d);
          }
        }
      }
      foreach($nulls as $nu){
        foreach($data as $d){
          if($d->distance == $nu){
            array_push($results,$d);
          }
        }
      }
      return $results;
    }

    public function getPickupPointServiceCode($data, $provider){
      $result = 0;
      foreach($data as $d){
        if($d->service_provider == $provider){
          if(count($d->additional_services)>0){
           foreach($d->additional_services as $service){
              if($service->service_code == '2106'){
                $result = $d->shipping_method_code;
                break;
              }
           }
          }
        }
      }
      return $result;
    }

    public function laskeViite($tmpId) {
      // Painoarvot
      $tmpMultip = array(7, 3, 1);
      // Muutetaan parametri merkkijonoksi
      $tmpStr = (string)$tmpId;
      $tmpSum = 0;
      $tmpIndex = 0;
      for ($i=strlen($tmpStr)-1; $i>=0; $i--) {
      $tmpSum += intval(substr($tmpStr, $i, 1)) * intval($tmpMultip[$tmpIndex %
      3]);
      $tmpIndex++;
      }

      // Laskettua summaa vastaava seuraava täysi kymmenluku:
      $nextTen = ceil(intval($tmpSum)/10)*10;
      return $tmpStr . (string)(abs($nextTen-$tmpSum));
    }
  }
