<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Exceptions\MPApiException;
use Illuminate\Support\Facades\Log;


class PreferenciaMP
{
    public $usuario;

    public function __construct()
    {
        $this->usuario = Auth::user();
        MercadoPagoConfig::setAccessToken(env('MERCADOPAGO_ACCESS_TOKEN'));
    }

    public function crearPreferencia(Request $request)
    {
        try {
            Log::info('📥 Request recibido:', $request->all());

            $validated = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.title' => 'required|string',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
            ]);

            Log::info('✅ Datos validados:', $validated);

            $client = new PreferenceClient();

            $preferenceData = [
                'items' => array_map(function ($item) {
                    return [
                        'title' => $item['title'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'id' => $item['id'] ?? uniqid(),
                    ];
                }, $validated['items']),
                'metadata' => [
                    'user_id' => $request->user_id ?? 'guest',
                    'event_id' => $request->event_id ?? null,
                ],
            ];;

            // Solo agregar back_urls si están configuradas
            $successUrl = env('MP_SUCCESS_URL');
            $failureUrl = env('MP_FAILURE_URL');
            $pendingUrl = env('MP_PENDING_URL');

            if ($successUrl && $failureUrl && $pendingUrl) {
                $preferenceData['back_urls'] = [
                    'success' => $successUrl,
                    'failure' => $failureUrl,
                    'pending' => $pendingUrl,
                ];
                $preferenceData['auto_return'] = 'approved';
            }

            Log::info('🚀 Enviando a MercadoPago:', $preferenceData);

            $preference = $client->create($preferenceData);

            Log::info('✅ Preferencia creada exitosamente:', [
                'id' => $preference->id
            ]);

            return response()->json([
                'success' => true,
                'preference_id' => $preference->id,
            ]);
        } catch (MPApiException $e) {
            Log::error('❌ Error de API de MercadoPago:', [
                'status' => $e->getStatusCode(),
                'message' => $e->getMessage(),
                'api_response' => $e->getApiResponse(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la preferencia de pago',
                'error' => $e->getMessage(),
                'status_code' => $e->getStatusCode(),
                'api_response' => $e->getApiResponse(),
            ], 500);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Error de validación:', ['errors' => $e->errors()]);

            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ Error general:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la preferencia de pago',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
