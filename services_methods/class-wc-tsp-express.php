<?php

if (class_exists('WC_Melhor_Envio_Shipping')) {
	class WC_Melhor_Envio_Shipping_TSP_Express extends WC_Melhor_Envio_Shipping
	{

		const ID = 'melhorenvio_tsp_express';

		const TITLE = 'TSP Express';

		const METHOD_TITLE = 'TSP Express';

		public $code = 95;

		public $company = 'TSP Express';

		/**
		 * Initialize TSP Express.
		 *
		 * @param int $instance_id Shipping zone instance.
		 */
		public function __construct($instance_id = 0)
		{
			$this->id           = self::ID;
			$this->method_title = self::METHOD_TITLE;
			parent::__construct($instance_id);
		}
	}
}
