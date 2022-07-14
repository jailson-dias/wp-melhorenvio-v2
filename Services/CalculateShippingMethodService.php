<?php

namespace MelhorEnvio\Services;

use MelhorEnvio\Helpers\MoneyHelper;
use MelhorEnvio\Helpers\TimeHelper;
use MelhorEnvio\Models\ShippingService;
use MelhorEnvio\Helpers\PostalCodeHelper;
use MelhorEnvio\Services\WooCommerceBundleProductsService;
use stdClass;

class CalculateShippingMethodService
{
	const DISTANCE_CITIES = array(
		"58188" => array(
			"58188" => 1.2,
			"58170" => 31.3,
			"59220" => 53.4,
			"58167" => 58.8,
			"58175" => 37.1,
			"58173" => 53.4,
			"58195" => 44.4,
			"59225" => 47.1,
			"58178" => 43.8,
			"58184" => 47,
			"58180" => 57.2,
			"58187" => 23.3,
			"58158" => 71.8,
			"58155" => 85.7,
			"58177" => 37.5
		),
		"58170" => array(
			"58170" => 2.5,
			"59220" => 51.4,
			"58167" => 56.7,
			"58175" => 35.1,
			"58173" => 24.2,
			"58195" => 63.4,
			"59225" => 45.1,
			"58178" => 42.1,
			"58184" => 65.5,
			"58180" => 76.2,
			"58187" => 42.3,
			"58158" => 69.8,
			"58155" => 83.6,
			"58177" => 35.5
		),
		"59220" => array(
			"59220" => 1.3,
			"58167" => 78.8,
			"58175" => 16.5,
			"58173" => 73.4,
			"58195" => 52.2,
			"59225" => 6.4,
			"58178" => 9.7,
			"58184" => 54.7,
			"58180" => 65.9,
			"58187" => 32.1,
			"58158" => 91.4,
			"58155" => 105,
			"58177" => 57.6
		),
		"58167" => array(
			"58167" => 1.5,
			"58175" => 62.5,
			"58173" => 78.8,
			"58195" => 83.4,
			"59225" => 72.5,
			"58178" => 69.5,
			"58184" => 39.9,
			"58180" => 28.4,
			"58187" => 62.6,
			"58158" => 13.7,
			"58155" => 27.5,
			"58177" => 21.3
		),
		"58175" => array(
			"58175" => 3,
			"58173" => 57.1,
			"58195" => 50.2,
			"59225" => 10.1,
			"58178" => 6.8,
			"58184" => 51.9,
			"58180" => 62.9,
			"58187" => 29.1,
			"58158" => 75.5,
			"58155" => 89.3,
			"58177" => 41.2
		),
		"58173" => array(
			"58173" => 1.2,
			"58195" => 85.4,
			"59225" => 67.1,
			"58178" => 64.1,
			"58184" => 87.5,
			"58180" => 98.2,
			"58187" => 64.4,
			"58158" => 91.8,
			"58155" => 106,
			"58177" => 57.6
		),
		"58195" => array(
			"58195" => 1.1,
			"59225" => 79.1,
			"58178" => 43.7,
			"58184" => 43.1,
			"58180" => 55,
			"58187" => 21.4,
			"58158" => 82.7,
			"58155" => 96.3,
			"58177" => 69.6
		),
		"59225" => array(
			"59225" => 2.1,
			"58178" => 3.5,
			"58184" => 48.8,
			"58180" => 59.5,
			"58187" => 25.7,
			"58158" => 85.5,
			"58155" => 99.3,
			"58177" => 51.2
		),
		"58178" => array(
			"58178" => 3.1,
			"58184" => 45.8,
			"58180" => 56.4,
			"58187" => 22.4,
			"58158" => 82.3,
			"58155" => 96.1,
			"58177" => 48.3
		),
		"58184" => array(
			"58184" => 1.5,
			"58180" => 11.5,
			"58187" => 23.5,
			"58158" => 40.1,
			"58155" => 53.8,
			"58177" => 60.5
		),
		"58180" => array(
			"58180" => 1.4,
			"58187" => 34.6,
			"58158" => 28.8,
			"58155" => 42.6,
			"58177" => 49
		),
		"58187" => array(
			"58187" => 3.5,
			"58158" => 63,
			"58155" => 76.8,
			"58177" => 48.5
		),
		"58158" => array(
			"58158" => 2.1,
			"58155" => 17.6,
			"58177" => 34.3
		),
		"58155" => array(
			"58155" => 3.5,
			"58177" => 48.1
		),
		"58177" => array(
			"58177" => 1.3
		)
	);

	/**
	 * Constant for delivery class of any class
	 */
	const ANY_DELIVERY = -1;

	/**
	 * Constant for no delivery class
	 */

	const WITHOUT_DELIVERY = 0;

	/**
	 * Constant that defines the quantity of items in a shipment that it considers to have multiple volumes
	 */
	const QUANTITY_DEFINE_VOLUME = 2;

	private function sellersPostalCode($data)
	{
		$postalCodes = array();
		foreach ($data as $item) {
			$seller = get_post_field('post_author', $item["data"]->get_id());
			$vendor = dokan()->vendor->get($seller);
			$address = $vendor->get_address();
			$postalCodes[] = $address["zip"];
		}

		return array_unique($postalCodes);
	}

	private function priceFromDistance($distance)
	{
		switch (true) {
			case $distance < 2:
				return 3.5;
			case $distance < 5:
				return 5;
			case $distance < 10:
				return 9;
			case $distance < 30:
				return 12;
			case $distance < 60:
				return 15.5;
			case $distance < 90:
				return 19;
			case $distance < 120:
				return 21;
			case $distance < 150:
				return 23.5;
		}
		return false;
	}

	private function shippingValue($from, $to)
	{
		$from = substr($from, 0, 5);
		$to = substr($to, 0, 5);
		if (isset(self::DISTANCE_CITIES[$from]) && isset(self::DISTANCE_CITIES[$from][$to])) {
			return $this->priceFromDistance(self::DISTANCE_CITIES[$from][$to]);
		} elseif (isset(self::DISTANCE_CITIES[$to]) && isset(self::DISTANCE_CITIES[$to][$from])) {
			return $this->priceFromDistance(self::DISTANCE_CITIES[$to][$from]);
		}

		return false;
	}

	/**
	 * Function to carry out the freight quote in the Melhor Envio api.
	 *
	 * @param array  $package
	 * @param int    $code
	 * @param int    $id
	 * @param string $company
	 * @param string $title
	 * @param float  $taxExtra
	 * @param int    $timeExtra
	 * @param int    $percent
	 * @return bool
	 */
	public function calculateShipping( $package = array(), $code, $id, $company, $title, $taxExtra, $timeExtra, $percent ) {
		$to = PostalCodeHelper::postalcode( $package['destination']['postcode'] );
		if ( strlen( $to ) != PostalCodeHelper::SIZE_POSTAL_CODE ) {
			return false;
		}

		$products = ( isset( $package['contents'] ) )
			? $package['contents']
			: ( new CartWooCommerceService() )->getProducts();

		if ( WooCommerceBundleProductsService::isWooCommerceProductBundle( $products ) ) {
			$products = ( new WooCommerceBundleProductsService() )->manageProductsBundle( $products );
		}

		if ($this->isTSPExpress($code)) {
			$postalCodes = $this->sellersPostalCode($products);
			$price = 0;
			foreach ($postalCodes as $item) {
				$itemPrice = $this->shippingValue($item, $to);
				if (!$itemPrice) {
					return false;
				}
				$price += $itemPrice;
			}

			$delivery_range = new stdClass();
			$delivery_range->min = 2;
			$delivery_range->max = 5;

			return array(
				'id'        => $id,
				'label'     => $title . TimeHelper::label(
					$delivery_range,
					$timeExtra
				),
				'cost'      => MoneyHelper::cost(
					$price,
					$taxExtra,
					$percent
				),
				'calc_tax'  => 'per_item',
				'meta_data' => array(
					'delivery_time' => TimeHelper::label(
						$delivery_range,
						$timeExtra
					),
					'price'         => MoneyHelper::price(
						$price,
						$taxExtra,
						$percent
					),
					'company'       => $company,
				),
			);
		}

		$result = (new QuotationService())->calculateQuotationByProducts(
			$products,
			$to,
			$code
		);

		if ( is_array( $result ) ) {
			$result = $this->extractOnlyQuotationByService( $result, $code );
		}

		if ( $result ) {
			if ( isset( $result->price ) && isset( $result->name ) ) {
				if ( $this->isCorreios( $code ) && $this->hasMultipleVolumes( $result ) ) {
					return false;
				}

				$additionalData = ( new ShippingClassService() )->getExtrasOnCart();

				if ( ! empty( $additionalData['taxExtra'] ) ) {
					$taxExtra = ( $additionalData['taxExtra'] >= $taxExtra )
						? $additionalData['taxExtra']
						: $taxExtra;
				}

				if ( ! empty( $additionalData['timeExtra'] ) ) {
					$timeExtra = ( $additionalData['timeExtra'] >= $timeExtra )
						? $additionalData['timeExtra']
						: $timeExtra;
				}

				if ( ! empty( $additionalData['percent'] ) ) {
					$percent = ( $additionalData['percent'] >= $percent )
						? $additionalData['percent']
						: $percent;
				}

				$rate = array(
					'id'        => $id,
					'label'     => $title . TimeHelper::label(
						$result->delivery_range,
						$timeExtra
					),
					'cost'      => MoneyHelper::cost(
						$result->price,
						$taxExtra,
						$percent
					),
					'calc_tax'  => 'per_item',
					'meta_data' => array(
						'delivery_time' => TimeHelper::label(
							$result->delivery_range,
							$timeExtra
						),
						'price'         => MoneyHelper::price(
							$result->price,
							$taxExtra,
							$percent
						),
						'company'       => $company,
					),
				);
			}

			if ( ! empty( $rate ) ) {
				return $rate;
			}
		}

		return false;
	}

	/**
	 * Check if it has more than one volume
	 *
	 * @param stdClass $quotation
	 * @return boolean
	 */
	public function hasMultipleVolumes( $quotation ) {
		if ( ! isset( $quotation->packages ) ) {
			return false;
		}

		return count( $quotation->packages ) >= self::QUANTITY_DEFINE_VOLUME;
	}

	/**
	 * Check if it is "Correios"
	 *
	 * @param int $code
	 * @return boolean
	 */
	public function isCorreios( $code ) {
		return in_array( $code, ShippingService::SERVICES_CORREIOS );
	}

	/**
	 * Check if it is "Jadlog"
	 *
	 * @param int $code
	 * @return boolean
	 */
	public function isJadlog( $code ) {
		return in_array( $code, ShippingService::SERVICES_JADLOG );
	}

	/**
	 * Check if it is "Azul Cargo"
	 *
	 * @param int $code
	 * @return boolean
	 */
	public function isAzulCargo( $code ) {
		return in_array( $code, ShippingService::SERVICES_AZUL );
	}

	/**
	 * Check if it is "LATAM Cargo"
	 *
	 * @param int $code
	 * @return boolean
	 */
	public function isLatamCargo( $code ) {
		return in_array( $code, ShippingService::SERVICES_LATAM );
	}

	/**
	 * Check if it is "TSP Express"
	 *
	 * @param int $code
	 * @return boolean
	 */
	public function isTSPExpress($code)
	{
		return in_array($code, ShippingService::TSP_EXPRESS);
	}

	/**
	 * Function to extract the quotation by the shipping method
	 *
	 * @param array $quotations
	 * @param int   $service
	 * @return object
	 */
	public function extractOnlyQuotationByService( $quotations, $service ) {
		$quotationByService = array_filter(
			$quotations,
			function ( $item ) use ( $service ) {
				if ( isset( $item->id ) && $item->id == $service ) {
					return $item;
				}
			}
		);

		if ( ! is_array( $quotationByService ) ) {
			return false;
		}

		return end( $quotationByService );
	}

	/**
	 * Get shipping classes options.
	 *
	 * @return array
	 */
	public function getShippingClassesOptions() {
		$shippingClasses = WC()->shipping->get_shipping_classes();
		$options         = array(
			self::WITHOUT_DELIVERY => 'Sem classe de entrega',
		);

		if ( ! empty( $shippingClasses ) ) {
			$options += wp_list_pluck( $shippingClasses, 'name', 'term_id' );
		}

		return $options;
	}

	/**
	 * Check if package uses only the selected shipping class.
	 *
	 * @param  array $package Cart package.
	 * @param int   $shippingClassId
	 * @return bool
	 */
	public function needShowShippginMethod( $package, $shippingClassId ) {
		$show = false;

		if ( ! empty( $package['cotationProduct'] ) ) {
			foreach ( $package['cotationProduct'] as $product ) {

				if ( $this->isProductWithouShippingClass( $product->shipping_class_id, $shippingClassId ) ) {
					$show = true;
					break;
				}

				$show = ( $product->shipping_class_id == $shippingClassId );
			}
			return $show;
		}

		foreach ( $package['contents'] as $values ) {
			$product = $values['data'];
			$qty     = $values['quantity'];
			if ( $qty > 0 && $product->needs_shipping() ) {
				if ( $this->isProductWithouShippingClass( $product->get_shipping_class_id(), $shippingClassId ) ) {
					$show = true;
					break;
				}
				$show = ( $product->get_shipping_class_id() == $shippingClassId );
			}
		}

		return $show;
	}

	/**
	 * Function to check if product not has shipping class.
	 *
	 * @param int $productShippingClassId
	 * @param int $shippingClassId
	 * @return boolean
	 */
	private function isProductWithouShippingClass( $productShippingClassId, $shippingClassId ) {
		$shippingsMehodsWithoutClass = array(
			self::ANY_DELIVERY,
			self::WITHOUT_DELIVERY,
		);

		return ( in_array( $productShippingClassId, $shippingsMehodsWithoutClass ) && in_array( $shippingClassId, $shippingsMehodsWithoutClass ) );
	}

	/**
	 * Function to check if the insured amount is mandatory
	 *
	 * @param bool   $optionalInsuredAmount
	 * @param string $serviceId
	 * @return bool
	 */
	public function insuranceValueIsRequired( $optionalInsuredAmount, $serviceId ) {
		if ( $optionalInsuredAmount && is_null( $serviceId ) ) {
			return true;
		}

		if ( ! $this->isCorreios( $serviceId ) ) {
			return true;
		}

		if ( is_null( $optionalInsuredAmount ) ) {
			return true;
		}

		return $optionalInsuredAmount;
	}
}
