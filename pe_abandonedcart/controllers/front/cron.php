<?php
/**
 * Cron Controller
 *
 * @author    MLAB Factory
 * @copyright 2025 MLAB Factory
 * @license   MIT License
 */

use MLAB\PE\Service\AbandonedCartService;

class Pe_AbandonedCartCronModuleFrontController extends ModuleFrontController
{
    /**
     * Initialize controller
     */
    public function init()
    {
        parent::init();
        
        // Check token for security
        $token = Tools::getValue('token');
        $configToken = Configuration::get('PE_ABANDONED_CART_CRON_TOKEN');

        if (!$token || $token !== $configToken) {
            header('HTTP/1.1 403 Forbidden');
            die('Invalid token');
        }
    }

    /**
     * Process cron job
     */
    public function initContent()
    {
        parent::initContent();

        $service = new AbandonedCartService();
        $emailsSent = $service->processAbandonedCarts();

        header('Content-Type: application/json');
        die(json_encode([
            'success' => true,
            'emails_sent' => $emailsSent,
            'timestamp' => date('Y-m-d H:i:s')
        ]));
    }
}
