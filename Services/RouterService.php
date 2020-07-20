<?php

namespace Services;

use Controllers\ConfigurationController;
use Controllers\CotationController;
use Controllers\OrdersController;
use Controllers\StatusController;
use Controllers\TokenController;
use Controllers\UsersController;

class RouterService
{
    public function handler()
    {
        $this->loadRoutesOrders();
        $this->loadRoutesUsers();
        $this->loadRoutesQuotations();
        $this->loadRoutesConfigurations();
        $this->loadRoutesStatus();
        $this->loadRoutesTokens();
        $this->loadRoutesTest();
    }

    /**
     * function to start users routes
     *
     * @return void
     */
    private function loadRoutesUsers()
    {
        $usersController = new UsersController();

        add_action('wp_ajax_me', [$usersController, 'getMe']);
        add_action('wp_ajax_get_balance', [$usersController, 'getBalance']);
    }

   /**
     * function to start users routes
     *
     * @return void
     */
    private function loadRoutesOrders()
    {
        $ordersController = new OrdersController();

        add_action('wp_ajax_get_orders', [$ordersController, 'getOrders']);
        add_action('wp_ajax_add_order', [$ordersController, 'sendOrder']);
        add_action('wp_ajax_buy_click', [$ordersController, 'buyOnClick']);
        add_action('wp_ajax_remove_order', [$ordersController, 'removeOrder']);
        add_action('wp_ajax_cancel_order', [$ordersController, 'cancelOrder']);
        add_action('wp_ajax_pay_ticket', [$ordersController, 'payTicket']);
        add_action('wp_ajax_create_ticket', [$ordersController, 'createTicket']);
        add_action('wp_ajax_print_ticket', [$ordersController, 'printTicket']);
        add_action('wp_ajax_insert_invoice_order', [$ordersController, 'insertInvoiceOrder']);
    }

    /**
     * function to start quotations routes
     *
     * @return void
     */
    private function loadRoutesQuotations()
    {
        $quotationsController = new CotationController();

        add_action('wp_ajax_nopriv_cotation_product_page', [$quotationsController, 'cotationProductPage']);
        add_action('wp_ajax_cotation_product_page', [$quotationsController, 'cotationProductPage']);
        add_action('wp_ajax_update_order', [$quotationsController, 'refreshCotation']);
    }

    /**
     * function to start configurations routes
     *
     * @return void
     */
    private function loadRoutesConfigurations()
    {
        $configurationsController = new ConfigurationController();

        add_action('wp_ajax_get_agency_jadlog', [$configurationsController, 'getAgencyJadlog']);
        add_action('wp_ajax_get_all_agencies_jadlog', [$configurationsController, 'getAgencyJadlog']);        
        add_action('wp_ajax_get_configuracoes', [$configurationsController, 'getConfigurations']);
        add_action('wp_ajax_get_metodos', [$configurationsController, 'getMethodsEnables']);
        add_action('wp_ajax_save_configuracoes', [$configurationsController, 'saveAll']);
            
    }

    /**
     * function to start status routes
     *
     * @return void
     */
    private function loadRoutesStatus()
    {
        $statusController = new StatusController();

        add_action('wp_ajax_get_status_woocommerce', [$statusController, 'getStatus']);

    }
    
    /**
     * function to start tokens routes
     *
     * @return void
     */
    private function loadRoutesTokens()
    {
        $tokensController = new TokenController();

        add_action('wp_ajax_get_token', [$tokensController, 'getToken']);
        add_action('wp_ajax_save_token', [$tokensController, 'saveToken']);
    }

    /**
     * function to start tests routes
     *
     * @return void
     */
    private function loadRoutesTest()
    {
        add_action('wp_ajax_nopriv_environment', function() {
            (new TestService('2.7.8'))->run();
        });

        add_action('wp_ajax_environment', function() {
            (new TestService('2.7.8'))->run();
        });
    }
}