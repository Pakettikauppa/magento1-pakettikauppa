<?php
class Pakettikauppa_Logistics_Model_Rate_Result extends Mage_Shipping_Model_Rate_Result{

  // OVERRIDE IN ORDER TO DISPLAY PICKUPPOINTS
  // IN CORRECT ORDER ACCORDING TO DISTANCE
  public function sortRatesByPrice(){
    
      if (!is_array($this->_rates) || !count($this->_rates)) {
        return $this;
      }

      $carrier = null;
      foreach($this->_rates as $i => $rate){
        $carrier = $rate->getCarrier();
      }

      $param = 'price';
      if($carrier == 'pktkp_pickuppoint'){
        $param = Mage::getStoreConfig('pakettikauppa/settings/sorting_pickup');
      }
      if($carrier == 'pktkp_homedelivery'){
        $param = Mage::getStoreConfig('pakettikauppa/settings/sorting_home');
      }
      foreach ($this->_rates as $i => $rate) {
        if($param == 'distance'){
          $tmp[$i] = $rate->getDistance();
        }elseif ($param =='sort') {
          $tmp[$i] = $rate->getSortOrder();
        }else{
          $tmp[$i] = $rate->getPrice();
        }

      }
      natsort($tmp);
      foreach ($tmp as $i => $price) {
          $result[] = $this->_rates[$i];
      }
      $this->reset();
      $this->_rates = $result;
      return $this;
  }
}
