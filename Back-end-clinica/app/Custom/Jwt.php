<?php

namespace App\Custom;

use App\Models\User;
use Firebase\JWT\JWT as JWTFirebase;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use UnexpectedValueException;

class Jwt
{
    public static function key(): string
    {
        $key = env('JWT_KEY');

        if (! is_string($key) || $key === '') {
            throw new UnexpectedValueException('JWT_KEY não configurada.');
        }

        return $key;
    }

    /**
     * @return array{id:int,name:string,email:string,profile_id:int|null,clinic_slug:?string,clinic_nome:?string}
     */
    public static function claimsFromUser(User $user): array
    {
        $clinic = \App\Support\TenantContext::clinic();

        return [
            'id' => (int) $user->id,
            'name' => (string) $user->name,
            'email' => (string) $user->email,
            'profile_id' => $user->profile_id !== null ? (int) $user->profile_id : null,
            'clinic_slug' => $clinic?->slug,
            'clinic_nome' => $clinic?->nome,
        ];
    }

    public static function ttlSeconds(): int
    {
        $ttl = (int) config('jwt.ttl_seconds', 60 * 60 * 4);

        return max(300, $ttl); // mínimo 5 minutos
    }

    public static function create(User $user): string
    {
        $payload = [
            'exp' => time() + self::ttlSeconds(),
            'iat' => time(),
            'data' => self::claimsFromUser($user),
        ];

        return JWTFirebase::encode($payload, self::key(), 'HS256');
    }

    /**
     * @return object{exp:int,iat:int,data:object}
     */
    public static function decode(string $token): object
    {
        return JWTFirebase::decode($token, new Key(self::key(), 'HS256'));
    }

    public static function bearerToken(?Request $request = null): ?string
    {
        $request ??= request();
        $header = $request->header('Authorization') ?? $request->server('HTTP_AUTHORIZATION');

        if (! is_string($header) || $header === '') {
            return null;
        }

        if (preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }
}
