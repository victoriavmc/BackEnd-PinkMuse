<?php

namespace App\Http\Controllers;

use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;

class PreferenciaMP
{
    protected array $items = [];

    public function __construct()
    {
        MercadoPagoConfig::setAccessToken(env('MERCADOPAGO_ACCESS_TOKEN'));
    }

    /**
     * Agregar un ítem (ticket evento o producto)
     */
    public function agregarItem(string $nombre, float $precio, int $cantidad): void
    {
        $this->items[] = [
            'title' => $nombre,
            'quantity' => $cantidad,
            'unit_price' => $precio,
        ];
    }

    /**
     * Crear la preferencia unificada
     */
    public function crearPreferencia(): object
    {
        if (empty($this->items)) {
            throw new \Exception('No hay ítems agregados para crear la preferencia.');
        }

        $client = new PreferenceClient();

        $preferenceData = [
            'items' => $this->items,
            'back_urls' => [
                'success' => env('MP_SUCCESS_URL'),
                'failure' => env('MP_FAILURE_URL'),
                'pending' => env('MP_PENDING_URL'),
            ],
            'auto_return' => 'approved',
        ];

        $preference = $client->create($preferenceData);

        return $preference;
    }

    /**
     * Obtener los ítems actuales (mostrar antes de pagar)
     */
    public function obtenerItems(): array
    {
        return $this->items;
    }
}
