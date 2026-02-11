<?php
/**
 * Abandoned Cart Service
 *
 * @author    MLAB Factory
 * @copyright 2025 MLAB Factory
 * @license   MIT License
 */

namespace MLAB\PE\Service;

use MLAB\PE\Model\AbandonedCart;
use MLAB\PE\Model\CheckoutData;

class AbandonedCartService
{

    /**
     * Process abandoned carts and send emails
     *
     * @return int Number of emails sent
     */
    public function processAbandonedCarts()
    {
        

        if (!\Configuration::get('PE_ABANDONED_CART_ENABLED')) {
            return 0;
        }

        $delay = (int)\Configuration::get('PE_ABANDONED_CART_DELAY');

        $abandonedCarts = $this->getAbandonedCartsToNotify($delay);

        $emailsSent = 0;


        /**
         * @var CheckoutData $cartData
         */
        foreach ($abandonedCarts as $cartData) {

            if ($cartData->getCheckoutPaymentStep()['step_is_complete'] === true) {
                continue;
            }

            $cart = new \Cart($cartData->getIdCart());
            $customer = new \Customer($cartData->getIdCustomer());
            
            if ($this->sendRecoveryEmailForCart($cart, $customer)) {
                // Registra l'invio dell'email nella tabella abandoned_cart
                $this->recordEmailSent($cart, $customer);
                $emailsSent++;
            }
        }

        return $emailsSent;
    }

    /**
     * Get abandoned carts that need email notification
     *
     * @param int $delay Delay in minutes
     * @return array <CheckoutData>
     */
    private function getAbandonedCartsToNotify($delay = 60)
    {
        $sql = 'SELECT cart.id_cart, customer.*,cart.checkout_session_data
                FROM `' . _DB_PREFIX_ . 'cart` as cart
                LEFT JOIN `' . _DB_PREFIX_ . 'customer` as customer ON customer.id_customer = cart.id_customer
                LEFT JOIN `' . _DB_PREFIX_ . 'abandoned_cart` as ac ON ac.id_cart = cart.id_cart
                LEFT JOIN `' . _DB_PREFIX_ . 'orders` as orders ON orders.id_cart = cart.id_cart
                WHERE TIMESTAMPDIFF(MINUTE, cart.date_upd, NOW()) >= ' . (int)$delay . '
                AND cart.id_customer != 0
                AND (ac.email_sent = 0 OR ac.email_sent IS NULL)
                AND (ac.recovered = 0 OR ac.recovered IS NULL)
                AND orders.id_order IS NULL
                ORDER BY cart.date_add ASC';

        $result = \Db::getInstance()->executeS($sql);
        
        if ($result === false) {
            return [];
        }

        $checkoutDataList = [];
        foreach ($result as $row) {
            if(!isset($row['checkout_session_data']) || empty($row['checkout_session_data'])) {
                continue;
            }
            $checkoutDataList[] = CheckoutData::createFromJson($row['checkout_session_data'], (int)$row['id_cart'], (int)$row['id_customer']);
        }

        return $checkoutDataList;
    }

    /**
     * Record that email was sent for this cart
     *
     * @param \Cart $cart
     * @param \Customer $customer
     * @return bool
     */
    private function recordEmailSent($cart, $customer)
    {
        // Check if record already exists
        $abandonedCart = AbandonedCart::getByCartId($cart->id);
        
        if (!$abandonedCart) {
            // Create new record
            $abandonedCart = new AbandonedCart();
            $abandonedCart->id_cart = $cart->id;
            $abandonedCart->id_customer = $cart->id_customer;
            $abandonedCart->email = $customer->email;
            $abandonedCart->generateRecoveryToken();
            $abandonedCart->date_add = date('Y-m-d H:i:s');
            $abandonedCart->date_upd = date('Y-m-d H:i:s');
            $result = $abandonedCart->add();
        } else {
            $result = true;
        }
        
        if ($result) {
            $abandonedCart->markEmailAsSent();
        }
        
        return $result;
    }

    /**
     * Send recovery email for a cart
     *
     * @param \Cart $cart
     * @param \Customer $customer
     * @return bool
     */
    private function sendRecoveryEmailForCart($cart, $customer)
    {
        if (!$customer->id || !$cart->id || !$cart->nbProducts()) {
            return false;
        }

        // Check if cart has been converted to an order
        $sql = 'SELECT id_order FROM `' . _DB_PREFIX_ . 'orders` WHERE id_cart = ' . (int)$cart->id;
        $orderId = \Db::getInstance()->getValue($sql);
        if ($orderId) {
            return false;
        }

        $context = \Context::getContext();
        $language = new \Language($customer->id_lang);

        // Get or create recovery token
        $abandonedCart = AbandonedCart::getByCartId($cart->id);
        if (!$abandonedCart) {
            $abandonedCart = new AbandonedCart();
            $abandonedCart->generateRecoveryToken();
        }

        $recoveryUrl = $context->link->getPageLink(
                'cart',
                true,
                (int) $cart->getAssociatedLanguage()->getId(),
                [
                    'recover_cart' => $cart->id,
                    'token_cart' => md5(_COOKIE_KEY_ . 'recover_cart_' . (int) $cart->id),
                ]
            );

        // Get cart products
        $products = $cart->getProducts();

        // Prepare template variables
        $templateVars = [
            '{firstname}' => $customer->firstname,
            '{lastname}' => $customer->lastname,
            '{email}' => $customer->email,
            '{recovery_url}' => $recoveryUrl,
            '{shop_name}' => \Configuration::get('PS_SHOP_NAME'),
            '{shop_url}' => $context->link->getPageLink('index', true),
            '{cart_total}' => $this->formatPrice($cart->getOrderTotal(), $cart->id_currency),
            '{products_list}' => $this->generateProductsList($products),
        ];

        // Get email template
        $templatePath = _PS_MODULE_DIR_ . 'pe_abandonedcart/views/templates/email/';

        // Send email
        $mailResult = \Mail::Send(
            $language->id,
            'abandoned_cart',
            'Abbiamo salvato il tuo carrello! - ' . \Configuration::get('PS_SHOP_NAME'),
            $templateVars,
            $customer->email,
            $customer->firstname . ' ' . $customer->lastname,
            null,
            null,
            null,
            null,
            $templatePath,
            false,
            $context->shop->id
        );

        return $mailResult;
    }

    /**
     * Generate products list HTML
     *
     * @param array $products
     * @return string
     */
    private function generateProductsList($products)
    {
        $html = '<ul class="product-list">';
        
        foreach ($products as $product) {
            $imageUrl = '';
            if (isset($product['id_image']) && $product['id_image']) {
                $image = new \Image($product['id_image']);
                $imageUrl = \Context::getContext()->link->getImageLink(
                    $product['link_rewrite'],
                    $image->id,
                    'small_default'
                );
            }

            $html .= '<li class="product-item">';
            
            if ($imageUrl) {
                $html .= '<img src="' . $imageUrl . '" alt="' . htmlspecialchars($product['name']) . '" class="product-image">';
            } else {
                $html .= '<div class="product-image" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center;">📦</div>';
            }
            
            $html .= '<div class="product-details">';
            $html .= '<div class="product-name">' . htmlspecialchars($product['name']) . '</div>';
            
            // Add product attributes if available
            if (isset($product['attributes_small']) && !empty($product['attributes_small'])) {
                $html .= '<div class="product-specs">' . htmlspecialchars($product['attributes_small']) . '</div>';
            }
            
            $html .= '<div class="product-specs">Quantità: ' . (int)$product['quantity'] . '</div>';
            $html .= '</div>';
            
            $html .= '<div class="product-price">' . $this->formatPrice($product['price_wt'], $product['id_currency'] ?? \Context::getContext()->currency->id) . '</div>';
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        
        return $html;
    }

    /**
     * Format price with currency
     *
     * @param float $price
     * @param int $idCurrency
     * @return string
     */
    private function formatPrice($price, $idCurrency)
    {
        try {
            $currency = new \Currency($idCurrency);
            return number_format($price, 2, ',', '.') . ' ' . $currency->sign;
        } catch (Exception $e) {
            return '€' . number_format($price, 2, ',', '.');
        }
    }

    /**
     * Recover abandoned cart
     *
     * @param string $token
     * @return bool
     */
    public function recoverCart($token)
    {
        $abandonedCart = AbandonedCart::getByRecoveryToken($token);

        if (!$abandonedCart) {
            return false;
        }

        // Load cart and restore it to customer's session
        $cart = new \Cart($abandonedCart->id_cart);
        
        if (!$cart->id) {
            return false;
        }

        // Mark as recovered
        $abandonedCart->markAsRecovered();

        // Set cart in context
        \Context::getContext()->cart = $cart;
        \Context::getContext()->cookie->id_cart = $cart->id;

        return true;
    }
}
