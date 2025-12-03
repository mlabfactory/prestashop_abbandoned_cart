<?php
/**
 * Admin Controller for Abandoned Cart Management
 *
 * @author    MLAB Factory
 * @copyright 2025 MLAB Factory
 * @license   MIT License
 */

class AdminPeAbandonedCartController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'abandoned_cart';
        $this->className = 'MLAB\PE\Model\AbandonedCart';
        $this->identifier = 'id_abandoned_cart';
        $this->lang = false;

        parent::__construct();

        $this->fields_list = [
            'id_abandoned_cart' => [
                'title' => $this->module->l('ID', 'AdminPeAbandonedCartController'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ],
            'id_cart' => [
                'title' => $this->module->l('Cart ID', 'AdminPeAbandonedCartController'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ],
            'email' => [
                'title' => $this->module->l('Customer Email', 'AdminPeAbandonedCartController'),
                'align' => 'left'
            ],
            'date_add' => [
                'title' => $this->module->l('Date Added', 'AdminPeAbandonedCartController'),
                'type' => 'datetime',
                'align' => 'center'
            ],
            'date_upd' => [
                'title' => $this->module->l('Last Update', 'AdminPeAbandonedCartController'),
                'type' => 'datetime',
                'align' => 'center'
            ],
            'email_sent' => [
                'title' => $this->module->l('Email Sent', 'AdminPeAbandonedCartController'),
                'align' => 'center',
                'active' => 'email_sent',
                'type' => 'bool',
                'class' => 'fixed-width-sm'
            ],
            'date_email_sent' => [
                'title' => $this->module->l('Email Sent Date', 'AdminPeAbandonedCartController'),
                'type' => 'datetime',
                'align' => 'center'
            ],
            'recovered' => [
                'title' => $this->module->l('Recovered', 'AdminPeAbandonedCartController'),
                'align' => 'center',
                'active' => 'recovered',
                'type' => 'bool',
                'class' => 'fixed-width-sm'
            ],
            'date_recovered' => [
                'title' => $this->module->l('Recovery Date', 'AdminPeAbandonedCartController'),
                'type' => 'datetime',
                'align' => 'center'
            ]
        ];

        $this->addRowAction('view');
        $this->addRowAction('delete');
    }

    public function renderView()
    {
        $abandonedCart = new MLAB\PE\Model\AbandonedCart((int)Tools::getValue('id_abandoned_cart'));
        
        if (!Validate::isLoadedObject($abandonedCart)) {
            $this->errors[] = $this->module->l('An error occurred while loading the object.', 'AdminPeAbandonedCartController');
            return false;
        }

        $cart = new Cart($abandonedCart->id_cart);
        $customer = new Customer($abandonedCart->id_customer);
        $cartData = json_decode($abandonedCart->cart_data, true);
        
        if (json_last_error() !== JSON_ERROR_NONE || $cartData === null) {
            $cartData = ['products' => [], 'total' => 0];
        }

        $this->context->smarty->assign([
            'abandonedCart' => $abandonedCart,
            'cart' => $cart,
            'customer' => $customer,
            'cartData' => $cartData,
            'recovery_url' => $this->context->link->getModuleLink(
                'pe_abandonedcart',
                'recovery',
                ['token' => $abandonedCart->recovery_token],
                true
            )
        ]);

        return parent::renderView();
    }

    public function initContent()
    {
        $this->addToolbarModuleButton();
        parent::initContent();
    }

    /**
     * Add module button to toolbar
     */
    public function addToolbarModuleButton()
    {
        if ($this->display != 'view' && $this->display != 'add' && $this->display != 'edit') {
            $this->page_header_toolbar_btn['module_config'] = [
                'href' => $this->context->link->getAdminLink('AdminModules', true) . '&configure=pe_abandonedcart',
                'desc' => $this->module->l('Configure Module', 'AdminPeAbandonedCartController'),
                'icon' => 'process-icon-configure'
            ];
        }
    }
}
