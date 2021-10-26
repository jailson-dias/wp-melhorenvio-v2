<?php

namespace Services;

class ShippingClassService
{
    protected $maxTaxExtra = 0;

    protected $maxTimeExtra = 0;

    protected $maxPercentExtra = 0;

    protected $shippingClassesId = [];

    protected $shippingClasses = [];

    /**
     * @param object $package
    * @return array
    */
    public function getExtasOnCart()
    {
        $this->setShippingClassesId();

        if (empty($this->shippingClassesId)) {
            return $this->normalizeArray();
        }

        $this->setExtraTax();

        $this->filterMaxValue();

        return $this->normalizeArray();
    }

    private function setShippingClassesId()
    {
        global $woocommerce;

        $dataCart = $woocommerce->cart->get_cart();

        if (!empty($dataCart)) {
            foreach ($dataCart as $cart) {
                if (!empty($cart['data']->shipping_class_id)) {
                    $shippingClassId = $cart['data']->shipping_class_id;
                    $this->shippingClassesId[] = $shippingClassId;
                }
            }
        }
    }

    public function setExtraTax()
    {
        global $woocommerce;

        $shipping_packages =  $woocommerce->cart->get_shipping_packages();

        $deliveryZones = wc_get_shipping_zone(reset($shipping_packages));

        $methods = $deliveryZones->get_shipping_methods();

        if (!empty($methods)) {
            foreach ($methods as $method) {
                if ($this->isValidToAdd($method)) {
                    $this->shippingClasses[$method->instance_settings['shipping_class_id']] = [
                        'additional_tax' => floatval($method->instance_settings['additional_tax']),
                        'additional_time' => floatval($method->instance_settings['additional_time']),
                        'percent_tax' => floatval($method->instance_settings['percent_tax'])
                    ];
                }
            }
        }
    }

    /**
     * @param array $method
     * @return bool
     */
    private function isValidToAdd($method)
    {
        return in_array($method->instance_settings['shipping_class_id'], $this->shippingClassesId) &&
        $method->instance_settings['shipping_class_id'] != CalculateShippingMethodService::ANY_DELIVERY;
    }

    /**
     * @return array
     */
    public function filterMaxValue()
    {
        if (!empty($this->shippingClasses)) {
            foreach ($this->shippingClasses as $data) {
                if ($data['additional_tax'] > $this->maxTaxExtra) {
                    $this->maxTaxExtra = $data['additional_tax'];
                }

                if ($data['additional_time'] > $this->maxTimeExtra) {
                    $this->maxTimeExtra = $data['additional_time'];
                }

                if ($data['percent_tax'] > $this->maxPercentExtra) {
                    $this->maxPercentExtra = $data['percent_tax'];
                }
            }
        }
    }

    /**
     * @return array
     */
    private function normalizeArray()
    {
        return [
            'taxExtra' => $this->maxTaxExtra,
            'timeExtra' =>$this->maxTimeExtra,
            'percent' =>  $this->maxTimeExtra
        ];
    }
}
