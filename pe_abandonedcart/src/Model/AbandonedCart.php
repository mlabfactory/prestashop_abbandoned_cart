<?php
/**
 * Abandoned Cart Model
 *
 * @author    MLAB Factory
 * @copyright 2025 MLAB Factory
 * @license   MIT License
 */

namespace MLAB\PE\Model;

class AbandonedCart extends \ObjectModel
{
    /**
     * @var int
     */
    public $id_abandoned_cart;

    /**
     * @var int
     */
    public $id_cart;

    /**
     * @var int
     */
    public $id_customer;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $cart_data;

    /**
     * @var string
     */
    public $recovery_token;

    /**
     * @var string
     */
    public $date_add;

    /**
     * @var string
     */
    public $date_upd;

    /**
     * @var bool
     */
    public $email_sent;

    /**
     * @var string
     */
    public $date_email_sent;

    /**
     * @var bool
     */
    public $recovered;

    /**
     * @var string
     */
    public $date_recovered;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'abandoned_cart',
        'primary' => 'id_abandoned_cart',
        'fields' => [
            'id_cart' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_customer' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'email' => ['type' => self::TYPE_STRING, 'validate' => 'isEmail', 'required' => true, 'size' => 255],
            'cart_data' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'recovery_token' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 64],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'email_sent' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_email_sent' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'recovered' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_recovered' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ]
    ];

    /**
     * Get abandoned cart by recovery token
     *
     * @param string $token
     * @return AbandonedCart|false
     */
    public static function getByRecoveryToken($token)
    {
        $sql = 'SELECT id_abandoned_cart
                FROM `' . _DB_PREFIX_ . 'abandoned_cart`
                WHERE recovery_token = "' . pSQL($token) . '"';

        $id = \Db::getInstance()->getValue($sql);

        if ($id) {
            return new self($id);
        }

        return false;
    }

    /**
     * Get abandoned cart by cart ID
     *
     * @param int $idCart
     * @return AbandonedCart|false
     */
    public static function getByCartId($idCart)
    {
        $sql = 'SELECT id_abandoned_cart
                FROM `' . _DB_PREFIX_ . 'abandoned_cart`
                WHERE id_cart = ' . (int)$idCart;

        $id = \Db::getInstance()->getValue($sql);

        if ($id) {
            return new self($id);
        }

        return false;
    }

    /**
     * Mark cart as recovered
     *
     * @return bool
     */
    public function markAsRecovered()
    {
        $this->recovered = true;
        $this->date_recovered = date('Y-m-d H:i:s');
        return $this->update();
    }

    /**
     * Mark email as sent
     *
     * @return bool
     */
    public function markEmailAsSent()
    {
        $this->email_sent = true;
        $this->date_email_sent = date('Y-m-d H:i:s');
        return $this->update();
    }

    /**
     * Generate recovery token
     *
     * @return string
     */
    public function generateRecoveryToken()
    {
        $this->recovery_token = bin2hex(random_bytes(32));
        return $this->recovery_token;
    }
}
