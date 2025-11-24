<?php
/**
 * PrestaShop Abandoned Cart Recovery Module
 *
 * @author    MLAB Factory
 * @copyright 2025 MLAB Factory
 * @license   MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use MLAB\PE\Service\AbandonedCartService;

class Pe_AbandonedCart extends Module
{
    /**
     * @var AbandonedCartService
     */
    private $abandonedCartService;

    public function __construct()
    {
        $this->name = 'pe_abandonedcart';
        $this->tab = 'emailing';
        $this->version = '1.0.0';
        $this->author = 'MLAB Factory';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Abandoned Cart Recovery');
        $this->description = $this->l('Recover abandoned carts by sending reminder emails to customers');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');

        if (file_exists(__DIR__ . '/vendor/autoload.php')) {
            $this->abandonedCartService = new AbandonedCartService();
        }
    }

    /**
     * Install module
     *
     * @return bool
     */
    public function install()
    {
        if (!parent::install() ||
            !$this->registerHook('actionCartSave') ||
            !$this->registerHook('displayHeader') ||
            !$this->installDb()
        ) {
            return false;
        }

        // Create cron task
        $this->createCronTask();

        return true;
    }

    /**
     * Uninstall module
     *
     * @return bool
     */
    public function uninstall()
    {
        if (!parent::uninstall() ||
            !$this->uninstallDb()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Install database tables
     *
     * @return bool
     */
    private function installDb()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'abandoned_cart` (
            `id_abandoned_cart` int(11) NOT NULL AUTO_INCREMENT,
            `id_cart` int(11) NOT NULL,
            `id_customer` int(11) NOT NULL,
            `email` varchar(255) NOT NULL,
            `cart_data` text NOT NULL,
            `recovery_token` varchar(64) NOT NULL,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            `email_sent` tinyint(1) DEFAULT 0,
            `date_email_sent` datetime DEFAULT NULL,
            `recovered` tinyint(1) DEFAULT 0,
            `date_recovered` datetime DEFAULT NULL,
            PRIMARY KEY (`id_abandoned_cart`),
            KEY `id_cart` (`id_cart`),
            KEY `id_customer` (`id_customer`),
            KEY `recovery_token` (`recovery_token`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        return Db::getInstance()->execute($sql);
    }

    /**
     * Uninstall database tables
     *
     * @return bool
     */
    private function uninstallDb()
    {
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'abandoned_cart`';
        return Db::getInstance()->execute($sql);
    }

    /**
     * Create cron task
     */
    private function createCronTask()
    {
        Configuration::updateValue('PE_ABANDONED_CART_CRON_TOKEN', md5(uniqid(rand(), true)));
        Configuration::updateValue('PE_ABANDONED_CART_DELAY', 60); // 60 minutes default
        Configuration::updateValue('PE_ABANDONED_CART_ENABLED', 1);
    }

    /**
     * Hook: Cart save
     *
     * @param array $params
     * @return void
     */
    public function hookActionCartSave($params)
    {
        if (!isset($params['cart']) || !$this->abandonedCartService) {
            return;
        }

        $cart = $params['cart'];
        $this->abandonedCartService->trackCart($cart);
    }

    /**
     * Module configuration page
     *
     * @return string
     */
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitPeAbandonedCartConfig')) {
            $delay = (int)Tools::getValue('PE_ABANDONED_CART_DELAY');
            $enabled = (int)Tools::getValue('PE_ABANDONED_CART_ENABLED');

            Configuration::updateValue('PE_ABANDONED_CART_DELAY', $delay);
            Configuration::updateValue('PE_ABANDONED_CART_ENABLED', $enabled);

            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }

        return $output . $this->displayForm();
    }

    /**
     * Display configuration form
     *
     * @return string
     */
    private function displayForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable abandoned cart recovery'),
                        'name' => 'PE_ABANDONED_CART_ENABLED',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            ]
                        ]
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Delay before sending email (minutes)'),
                        'name' => 'PE_ABANDONED_CART_DELAY',
                        'desc' => $this->l('Time to wait before considering a cart as abandoned'),
                        'class' => 'input fixed-width-sm'
                    ],
                    [
                        'type' => 'html',
                        'label' => $this->l('Cron URL'),
                        'name' => 'cron_url',
                        'html_content' => '<p class="form-control-static">' . 
                            $this->context->link->getModuleLink(
                                $this->name,
                                'cron',
                                ['token' => Configuration::get('PE_ABANDONED_CART_CRON_TOKEN')],
                                true
                            ) . '</p>'
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right'
                ]
            ]
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitPeAbandonedCartConfig';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => [
                'PE_ABANDONED_CART_ENABLED' => Configuration::get('PE_ABANDONED_CART_ENABLED'),
                'PE_ABANDONED_CART_DELAY' => Configuration::get('PE_ABANDONED_CART_DELAY')
            ],
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        ];

        return $helper->generateForm([$fields_form]);
    }
}
