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

class AbandonedCartService
{
    /**
     * Track a cart for abandonment
     *
     * @param \Cart $cart
     * @return bool
     */
    public function trackCart($cart)
    {
        // Don't track if cart is empty
        if (!$cart || !$cart->id || !$cart->nbProducts()) {
            return false;
        }

        // Don't track if customer is not logged in or has no email
        $customer = new \Customer($cart->id_customer);
        if (!$customer->id || !$customer->email) {
            return false;
        }

        // Check if cart is already tracked
        $abandonedCart = AbandonedCart::getByCartId($cart->id);

        if (!$abandonedCart) {
            // Create new abandoned cart record
            $abandonedCart = new AbandonedCart();
            $abandonedCart->id_cart = $cart->id;
            $abandonedCart->id_customer = $cart->id_customer;
            $abandonedCart->email = $customer->email;
            
            $cartData = json_encode([
                'products' => $cart->getProducts(),
                'total' => $cart->getOrderTotal()
            ]);
            
            if ($cartData === false) {
                return false;
            }
            
            $abandonedCart->cart_data = $cartData;
            $abandonedCart->generateRecoveryToken();
            $abandonedCart->date_add = date('Y-m-d H:i:s');
            $abandonedCart->date_upd = date('Y-m-d H:i:s');
            
            return $abandonedCart->add();
        } else {
            // Update existing record
            $abandonedCart->date_upd = date('Y-m-d H:i:s');
            
            $cartData = json_encode([
                'products' => $cart->getProducts(),
                'total' => $cart->getOrderTotal()
            ]);
            
            if ($cartData === false) {
                return false;
            }
            
            $abandonedCart->cart_data = $cartData;
            
            return $abandonedCart->update();
        }
    }

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
        $abandonedCarts = AbandonedCart::getAbandonedCartsToNotify($delay);
        $emailsSent = 0;

        foreach ($abandonedCarts as $cartData) {
            $abandonedCart = new AbandonedCart($cartData['id_abandoned_cart']);
            
            if ($this->sendRecoveryEmail($abandonedCart)) {
                $abandonedCart->markEmailAsSent();
                $emailsSent++;
            }
        }

        return $emailsSent;
    }

    /**
     * Send recovery email
     *
     * @param AbandonedCart $abandonedCart
     * @return bool
     */
    private function sendRecoveryEmail($abandonedCart)
    {
        $customer = new \Customer($abandonedCart->id_customer);
        $cart = new \Cart($abandonedCart->id_cart);

        if (!$customer->id || !$cart->id) {
            return false;
        }

        $context = \Context::getContext();
        $language = new \Language($customer->id_lang);

        // Generate recovery URL
        $recoveryUrl = $context->link->getModuleLink(
            'pe_abandonedcart',
            'recovery',
            ['token' => $abandonedCart->recovery_token],
            true,
            $language->id
        );

        // Get cart products
        $products = $cart->getProducts();
        $cartData = json_decode($abandonedCart->cart_data, true);
        
        if (json_last_error() !== JSON_ERROR_NONE || $cartData === null || !isset($cartData['total'])) {
            // Fallback to current cart data if decode fails
            $cartData = [
                'products' => $products,
                'total' => $cart->getOrderTotal()
            ];
        }

        // Prepare template variables
        $templateVars = [
            '{firstname}' => $customer->firstname,
            '{lastname}' => $customer->lastname,
            '{email}' => $customer->email,
            '{recovery_url}' => $recoveryUrl,
            '{shop_name}' => \Configuration::get('PS_SHOP_NAME'),
            '{shop_url}' => $context->link->getPageLink('index', true),
            '{cart_total}' => \Tools::displayPrice($cartData['total']),
            '{products_list}' => $this->generateProductsList($products),
        ];

        // Get email template
        $templatePath = _PS_MODULE_DIR_ . 'pe_abandonedcart/views/templates/email/';

        // Send email
        return \Mail::Send(
            $language->id,
            'abandoned_cart',
            'Recover your cart - ' . \Configuration::get('PS_SHOP_NAME'),
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
    }

    /**
     * Generate products list HTML
     *
     * @param array $products
     * @return string
     */
    private function generateProductsList($products)
    {
        $html = '<table style="width: 100%; border-collapse: collapse;">';
        
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

            $html .= '<tr style="border-bottom: 1px solid #ddd;">';
            $html .= '<td style="padding: 10px;">';
            if ($imageUrl) {
                $html .= '<img src="' . $imageUrl . '" alt="' . htmlspecialchars($product['name']) . '" style="width: 80px; height: auto;">';
            }
            $html .= '</td>';
            $html .= '<td style="padding: 10px;">';
            $html .= '<strong>' . htmlspecialchars($product['name']) . '</strong><br>';
            $html .= 'Qty: ' . (int)$product['quantity'] . '<br>';
            $html .= 'Price: ' . \Tools::displayPrice($product['price_wt']);
            $html .= '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        
        return $html;
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
