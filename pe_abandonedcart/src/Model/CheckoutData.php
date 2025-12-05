<?php
namespace MLAB\PE\Model;

final class CheckoutData {
    
    private array $checkoutPersonalInformationStep;
    private array $checkoutAddressesStep;
    private array $checkoutDeliveryStep;
    private array $checkoutPaymentStep;
    private string $checksum;
    private int $idCart;
    private int $idCustomer;

    public function __construct()
    {
        $this->checkoutPersonalInformationStep = [];
        $this->checkoutAddressesStep = [];
        $this->checkoutDeliveryStep = [];
        $this->checkoutPaymentStep = [];
        $this->checksum = '';
        $this->idCart = 0;
        $this->idCustomer = 0;
    }

    public function getCheckoutPersonalInformationStep(): array
    {
        return $this->checkoutPersonalInformationStep;
    }

    public function setCheckoutPersonalInformationStep(array $checkoutPersonalInformationStep): void
    {
        $this->checkoutPersonalInformationStep = $checkoutPersonalInformationStep;
    }

    public function getCheckoutAddressesStep(): array
    {
        return $this->checkoutAddressesStep;
    }

    public function setCheckoutAddressesStep(array $checkoutAddressesStep): void
    {
        $this->checkoutAddressesStep = $checkoutAddressesStep;
    }

    public function getCheckoutDeliveryStep(): array
    {
        return $this->checkoutDeliveryStep;
    }

    public function setCheckoutDeliveryStep(array $checkoutDeliveryStep): void
    {
        $this->checkoutDeliveryStep = $checkoutDeliveryStep;
    }

    public function getCheckoutPaymentStep(): array
    {
        return $this->checkoutPaymentStep;
    }

    public function setCheckoutPaymentStep(array $checkoutPaymentStep): void
    {
        $this->checkoutPaymentStep = $checkoutPaymentStep;
    }

    public function getChecksum(): string
    {
        return $this->checksum;
    }

    public function setChecksum(string $checksum): void
    {
        $this->checksum = $checksum;
    }

    public function getIdCart(): int
    {
        return $this->idCart;
    }

    public function setIdCart(int $idCart): void
    {
        $this->idCart = $idCart;
    }

    public function getIdCustomer(): int
    {
        return $this->idCustomer;
    }

    public function setIdCustomer(int $idCustomer): void
    {
        $this->idCustomer = $idCustomer;
    }

    public function toArray(): array
    {
        return [
            'checkout-personal-information-step' => $this->checkoutPersonalInformationStep,
            'checkout-addresses-step' => $this->checkoutAddressesStep,
            'checkout-delivery-step' => $this->checkoutDeliveryStep,
            'checkout-payment-step' => $this->checkoutPaymentStep,
            'checksum' => $this->checksum,
            'id'=> $this->idCart,
            'id_customer'=> $this->idCustomer,
        ];
    }

    public static function fromArray(array $data, int $idCart, int $idCustomer): self
    {
        $instance = new self();
        
        if (isset($data['checkout-personal-information-step'])) {
            $instance->setCheckoutPersonalInformationStep($data['checkout-personal-information-step']);
        }
        
        if (isset($data['checkout-addresses-step'])) {
            $instance->setCheckoutAddressesStep($data['checkout-addresses-step']);
        }
        
        if (isset($data['checkout-delivery-step'])) {
            $instance->setCheckoutDeliveryStep($data['checkout-delivery-step']);
        }
        
        if (isset($data['checkout-payment-step'])) {
            $instance->setCheckoutPaymentStep($data['checkout-payment-step']);
        }
        
        if (isset($data['checksum'])) {
            $instance->setChecksum($data['checksum']);
        }

        $instance->setIdCart($idCart);
        $instance->setIdCustomer($idCustomer);
        
        return $instance;
    }

    public static function createFromJson(string $json, int $idCart, int $idCustomer): self
    {
        $data = json_decode($json, true);
        return self::fromArray($data, $idCart, $idCustomer);
    }
}