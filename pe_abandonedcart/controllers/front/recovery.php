<?php
/**
 * Recovery Controller
 *
 * @author    MLAB Factory
 * @copyright 2025 MLAB Factory
 * @license   MIT License
 */

use MLAB\PE\Service\AbandonedCartService;

class Pe_AbandonedCartRecoveryModuleFrontController extends ModuleFrontController
{
    /**
     * Process cart recovery
     */
    public function initContent()
    {
        parent::initContent();

        $token = Tools::getValue('token');

        // Validate token format (64 character hex string)
        if (!$token || !preg_match('/^[a-f0-9]{64}$/i', $token)) {
            Tools::redirect('index.php?controller=404');
        }

        $service = new AbandonedCartService();
        
        if ($service->recoverCart($token)) {
            // Redirect to cart page
            Tools::redirect('index.php?controller=cart&action=show');
        } else {
            // Redirect to home or show error
            $this->errors[] = $this->module->l('Unable to recover your cart. It may have expired or already been recovered.');
            $this->setTemplate('module:pe_abandonedcart/views/templates/front/recovery_error.tpl');
        }
    }
}
