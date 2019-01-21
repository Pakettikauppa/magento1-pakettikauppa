<?php
class Pakettikauppa_Logistics_Model_Observer {
  public function salesOrderShipmentSaveBefore($observer){
    $shipment = $observer->getEvent()->getShipment();
    $shipping_method = $shipment->getOrder()->getData('shipping_method');

      if(Mage::helper('pakettikauppa_logistics')->isPakettikauppa($shipping_method)){
      if(count($shipment->getAllTracks())==0){

        $code = Mage::helper('pakettikauppa_logistics')->getMethod($shipping_method);

        if(Mage::helper('pakettikauppa_logistics')->getMethod($shipping_method)=='pktkp_homedelivery'){
          $carrier = $shipment->getOrder()->getData('home_delivery_service_provider');
        }
        if(Mage::helper('pakettikauppa_logistics')->getMethod($shipping_method)=='pktkp_pickuppoint'){
          $carrier = $shipment->getOrder()->getData('pickup_point_provider');
        }

        $orderId = $shipment->getOrder()->getID();
        $order = Mage::getModel('sales/order')->load($orderId);

        $tracking_number = Mage::helper('pakettikauppa_logistics/API')->createShipment($order);
        $name = Mage::helper('pakettikauppa_logistics')->getCurrentCarrierTitle($shipping_method);

        // GET TRACKING NUMBER HERE
        $track = Mage::getModel('sales/order_shipment_track')
                        ->setCarrierCode($code)
                        ->setTitle($name)
                        ->setNumber($tracking_number['number']);
        $shipment->addTrack($track);
        $shipment->setShippingLabel($tracking_number['label']);
      }
    }
   }

   public function salesOrderSaveBefore($observer){
       $order = $observer->getEvent()->getData('order');

       $quote = Mage::getSingleton('checkout/session')->getQuote();

       $shipping_method_code = $quote->getShippingAddress()->getShippingMethod();

       $klarnaHelper = Mage::helper('klarnaCheckout');
       if($klarnaHelper != null && $klarnaHelper->isKcoOrder($order)) {
           $shipping_method_code = $order->getShippingMethod();
       }


       if(Mage::helper('pakettikauppa_logistics')->isPakettikauppa($shipping_method_code)){

         $method = Mage::helper('pakettikauppa_logistics')->getMethod($shipping_method_code);
         $homedelivery_methods = Mage::helper('pakettikauppa_logistics/API')->getHomeDelivery(true);
         $method_available = false;

         // PICKUP POINT
         if($method == 'pktkp_pickuppoint'){
           $zip = Mage::helper('pakettikauppa_logistics')->getZip();

           if(empty($zip)) {
             $zip = $order->getShippingAddress()->getPostcode();
           }

             $pickup_methods = Mage::helper('pakettikauppa_logistics/API')->getPickupPoints($zip);
           foreach($pickup_methods as $pickup_method){
             if('pktkp_pickuppoint_'.$pickup_method->pickup_point_id == $shipping_method_code){
                $order->setData('pickup_point_provider', $pickup_method->provider);
                $order->setData('pickup_point_id', $pickup_method->pickup_point_id);
                $order->setData('pickup_point_name', $pickup_method->name);
                $order->setData('pickup_point_street_address', $pickup_method->street_address);
                $order->setData('pickup_point_postcode', $pickup_method->postcode);
                $order->setData('pickup_point_city', $pickup_method->city);
                $order->setData('pickup_point_country', $pickup_method->country);
                $order->setData('pickup_point_description', $pickup_method->description);
                $pktkp_smc = Mage::helper('pakettikauppa_logistics')->getPickupPointServiceCode($homedelivery_methods, $pickup_method->provider);
                $order->setData('paketikauppa_smc', $pktkp_smc);
                $method_available = true;
             }
           }
         }

         // HOME DELIVERY
         if($method == 'pktkp_homedelivery'){
           foreach($homedelivery_methods as $homedelivery_method){
             if('pktkp_homedelivery_'.$homedelivery_method->shipping_method_code == $shipping_method_code){
               $order->setData('home_delivery_service_provider', $homedelivery_method->service_provider);
               $order->setData('paketikauppa_smc', $homedelivery_method->shipping_method_code);
               $method_available = true;
             }
           }
       }

       if(!$method_available){
         Mage::throwException('Method error, please choose another method.');
       }

     }
  }
}
