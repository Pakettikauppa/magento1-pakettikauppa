<?php
class Pakettikauppa_Logistics_Model_Selectpickup
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'price',
                'label' => 'By Price',
            ),
            array(
                'value' => 'sort',
                'label' => 'By Sort Order',
            ),
            array(
                'value' => 'distance',
                'label' => 'By Distance',
            )
        );
    }
}
