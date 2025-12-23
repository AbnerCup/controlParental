<?php
namespace App\Services;

use App\Models\ApiClient;
use Illuminate\Support\Facades\Crypt;

class HmacClientValidator
{
    public function validate(string $keyId, string $signature, string $rawBody): ?ApiClient
    {
        $client = ApiClient::where('key_id', $keyId)->where('active', true)->first();
        if (!$client || !$client->key_secret_enc) {
            return null;
        }

        $secret = Crypt::decryptString($client->key_secret_enc);

        $expected = hash_hmac('sha256', $rawBody, $secret);

        if (!hash_equals($expected, $signature)) {
            return null;
        }

        $client->last_used_at = now('UTC');
        $client->save();

        return $client;
    }
}
