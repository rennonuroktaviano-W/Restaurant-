<?php

namespace App\PaymentGateway;

use Illuminate\Contracts\Container\Container;

class GatewayManager
{
    protected array $resolved = [];

    public function __construct(protected Container $container) {}

    public function driver(?string $provider): PaymentGateway
    {
        $provider = $provider ?: 'mock';

        return $this->resolved[$provider] ??= $this->create($provider);
    }

    protected function create(string $provider): PaymentGateway
    {
        return match ($provider) {
            'mock' => $this->container->make(MockPaymentGateway::class),
            'qris' => $this->container->make(QrisPaymentGateway::class),
            default => throw new PaymentGatewayNotFoundException("Payment gateway [$provider] is not supported."),
        };
    }
}
