<?php
class Pakettikauppa_Logistics_Model_Selecthome
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
            )
        );
    }
}
