<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'usuarios';
    protected $fillable = [
        'nombre',
        'apellido',
        'nacionalidad',
        'fechaNacimiento',
        'correo',
        'password',
        'rol',
        'perfil',
        'preferenciaNotificacion',
        'estado',
        'api_tokens'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_tokens',
    ];

    protected $casts = [
        'fechaNacimiento' => 'date',
        'api_tokens' => 'array',
    ];

    /**
     * Crear un token de acceso para el usuario
     */
    public function createToken($name, array $abilities = ['*'])
    {
        $plainTextToken = Str::random(60);
        $tokenId = Str::random(10);
        $hashedToken = hash('sha256', $plainTextToken);

        $tokens = $this->api_tokens ?? [];

        $newToken = [
            'id' => $tokenId,
            'name' => $name,
            'token' => $hashedToken,
            'abilities' => $abilities,
            'created_at' => now()->toDateTimeString(),
            'last_used_at' => null,
        ];

        $tokens[$tokenId] = $newToken;

        // Actualizar usuario con nuevos tokens
        $this->update(['api_tokens' => $tokens]);

        // Crear objeto compatible con Sanctum
        $accessTokenObject = new \stdClass();
        $accessTokenObject->id = $tokenId;
        $accessTokenObject->name = $name;
        $accessTokenObject->abilities = $abilities;
        $accessTokenObject->token = $hashedToken;
        $accessTokenObject->created_at = $newToken['created_at'];

        // Retornar objeto con plainTextToken
        $result = new \stdClass();
        $result->accessToken = $accessTokenObject;
        $result->plainTextToken = $tokenId . '|' . $plainTextToken;

        return $result;
    }

    /**
     * Obtener todos los tokens del usuario
     */
    public function tokens()
    {
        return collect($this->api_tokens ?? []);
    }

    /**
     * Encontrar usuario por token
     */
    public static function findByToken($tokenString)
    {
        Log::info('Finding user by token', [
            'token_string' => $tokenString,
            'token_length' => strlen($tokenString)
        ]);

        if (empty($tokenString) || strpos($tokenString, '|') === false) {
            Log::info('Invalid token format - no pipe separator');
            return null;
        }

        [$tokenId, $plainToken] = explode('|', $tokenString, 2);
        $hashedToken = hash('sha256', $plainToken);

        Log::info('Token parts', [
            'token_id' => $tokenId,
            'plain_token_length' => strlen($plainToken),
            'hashed_token' => substr($hashedToken, 0, 10) . '...'
        ]);

        $users = static::whereNotNull('api_tokens')->get();

        foreach ($users as $user) {
            $userTokens = $user->api_tokens ?? [];

            if (isset($userTokens[$tokenId])) {
                $storedToken = $userTokens[$tokenId]['token'] ?? '';

                Log::info('Comparing tokens', [
                    'stored_token' => substr($storedToken, 0, 10) . '...',
                    'provided_token' => substr($hashedToken, 0, 10) . '...',
                    'match' => hash_equals($storedToken, $hashedToken)
                ]);

                if (hash_equals($storedToken, $hashedToken)) {
                    $userTokens[$tokenId]['last_used_at'] = now()->toDateTimeString();
                    $user->update(['api_tokens' => $userTokens]);

                    $user->currentAccessToken = (object) $userTokens[$tokenId];

                    Log::info('Token found for user', ['user_id' => $user->id]);
                    return $user;
                }
            }
        }

        Log::info('No user found for token');
        return null;
    }

    /**
     * Verificar si el usuario puede realizar una acción con el token actual
     */
    public function tokenCan($ability)
    {
        if (!isset($this->currentAccessToken)) {
            return false;
        }

        $abilities = $this->currentAccessToken->abilities ?? [];
        return in_array('*', $abilities) || in_array($ability, $abilities);
    }

    /**
     * Obtener el token de acceso actual
     */
    public function currentAccessToken()
    {
        $bearerToken = request()->bearerToken();

        if (!$bearerToken) {
            return null;
        }

        $tokenParts = explode('|', $bearerToken, 2);

        if (count($tokenParts) !== 2) {
            return null;
        }

        [$tokenId, $plainTextToken] = $tokenParts;

        $tokens = $this->api_tokens ?? [];

        if (!isset($tokens[$tokenId])) {
            return null;
        }

        $storedToken = $tokens[$tokenId];
        $hashedToken = hash('sha256', $plainTextToken);

        if ($hashedToken !== $storedToken['token']) {
            return null;
        }

        $accessTokenObject = new \stdClass();
        $accessTokenObject->id = $tokenId;
        $accessTokenObject->name = $storedToken['name'];
        $accessTokenObject->abilities = $storedToken['abilities'];
        $accessTokenObject->token = $storedToken['token'];
        $accessTokenObject->created_at = $storedToken['created_at'];

        return $accessTokenObject;
    }

    /**
     * Revocar un token específico
     */
    public function revokeToken($tokenId)
    {
        $tokens = $this->api_tokens ?? [];
        unset($tokens[$tokenId]);
        $this->update(['api_tokens' => $tokens]);
    }

    /**
     * Revocar todos los tokens
     */
    public function revokeAllTokens()
    {
        $this->update(['api_tokens' => []]);
    }

    // Relación con Rol usando el campo 'rol' como identificador
    public function rolRelacion()
    {
        return $this->belongsTo(Rol::class, 'rol', 'rol');
    }
}
