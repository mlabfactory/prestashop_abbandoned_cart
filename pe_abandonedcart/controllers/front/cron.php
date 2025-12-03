<?php
/**
 * Cron Controller
 *
 * @author    MLAB Factory
 * @copyright 2025 MLAB Factory
 * @license   MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Pe_AbandonedCartCronModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        // Security check
        $token = Tools::getValue('token');
        $configToken = Configuration::get('PE_ABANDONED_CART_CRON_TOKEN');

        if (!$token || $token !== $configToken) {
            header('HTTP/1.1 403 Forbidden');
            header('Content-Type: application/json');
            die(json_encode(['error' => 'Invalid token']));
        }

        try {
            // Load autoloader
            $autoloadPath = dirname(__FILE__) . '/../../vendor/autoload.php';
            if (file_exists($autoloadPath)) {
                require_once $autoloadPath;
            }

            // Check if class exists
            if (!class_exists('MLAB\PE\Service\AbandonedCartService')) {
                throw new Exception('AbandonedCartService class not found');
            }

            // Process abandoned carts
            $service = new MLAB\PE\Service\AbandonedCartService();
            $emailsSent = $service->processAbandonedCarts();

            header('Content-Type: application/json');
            die(json_encode([
                'success' => true,
                'emails_sent' => $emailsSent,
                'timestamp' => date('Y-m-d H:i:s')
            ]));

        } catch (Exception $e) {
            header('Content-Type: application/json');
            header('HTTP/1.1 500 Internal Server Error');
            die(json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]));
        }
    }
}
