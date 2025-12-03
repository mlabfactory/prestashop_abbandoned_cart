<?php
/**
 * Logs Controller
 *
 * @author    MLAB Factory
 * @copyright 2025 MLAB Factory
 * @license   MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Pe_AbandonedCartLogsModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        // Security check
        $token = Tools::getValue('token');
        $configToken = Configuration::get('PE_ABANDONED_CART_CRON_TOKEN');

        if (!$token || $token !== $configToken) {
            header('HTTP/1.1 403 Forbidden');
            header('Content-Type: text/plain');
            die('Invalid token');
        }

        header('Content-Type: text/plain');

        // Get recent logs
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'log` 
                WHERE `object_type` = "Pe_AbandonedCart" 
                ORDER BY `date_add` DESC 
                LIMIT 50';
        
        $logs = Db::getInstance()->executeS($sql);

        if (empty($logs)) {
            echo "No logs found for Pe_AbandonedCart\n";
            echo "Current time: " . date('Y-m-d H:i:s') . "\n";
        } else {
            echo "=== ABANDONED CART LOGS ===\n";
            echo "Current time: " . date('Y-m-d H:i:s') . "\n\n";
            
            foreach ($logs as $log) {
                echo "[" . $log['date_add'] . "] ";
                echo "Level " . $log['severity'] . ": ";
                echo $log['message'] . "\n";
            }
        }
    }
}