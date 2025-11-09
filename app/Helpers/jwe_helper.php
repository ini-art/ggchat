<?php
// app/Helpers/jwe_helper.php

if (!function_exists('base64url_encode')) {
    function base64url_encode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    function base64url_decode(string $data): string {
        $pad = 4 - (strlen($data) % 4);
        if ($pad < 4) $data .= str_repeat('=', $pad);
        return base64_decode(strtr($data, '-_', '+/'));
    }
}

// Provide fallback constants if libsodium constants are not defined (makes dev easier)
if (!defined('SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_KEYBYTES')) {
    define('SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_KEYBYTES', 32);
}
if (!defined('SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_NPUBBYTES')) {
    define('SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_NPUBBYTES', 24);
}

if (!function_exists('jwe_encrypt')) {
    function jwe_encrypt(array $header, string $plaintext, string $rsaPublicKeyPem): string {
        $hdr = json_encode($header);
        $hdr_b64 = base64url_encode($hdr); // simpan base64 header
        $cek = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_KEYBYTES);
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_NPUBBYTES);

        // gunakan base64 header sebagai AAD (supaya pasti sama)
        $aad = $hdr_b64;
        $cipher = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
            $plaintext,
            $aad,
            $nonce,
            $cek
        );

        $ok = openssl_public_encrypt($cek, $encryptedCek, $rsaPublicKeyPem, OPENSSL_PKCS1_OAEP_PADDING);
        if (!$ok) {
            throw new \RuntimeException('RSA encrypt failed: ' . openssl_error_string());
        }

        $parts = [
            $hdr_b64,
            base64url_encode($encryptedCek),
            base64url_encode($nonce),
            base64url_encode($cipher)
        ];
        return implode('.', $parts);
    }
}

if (!function_exists('jwe_decrypt')) {
    function jwe_decrypt(string $compactToken, string $rsaPrivateKeyPem): ?array {
        $parts = explode('.', $compactToken);
        if (count($parts) !== 4) return null;
        [$hdr_b64, $enccek_b64, $nonce_b64, $cipher_b64] = $parts;

        $hdr = json_decode(base64url_decode($hdr_b64), true);
        $enccek = base64url_decode($enccek_b64);
        $nonce = base64url_decode($nonce_b64);
        $cipher = base64url_decode($cipher_b64);

        $ok = openssl_private_decrypt($enccek, $cek, $rsaPrivateKeyPem, OPENSSL_PKCS1_OAEP_PADDING);
        if (!$ok) return null;

        // gunakan base64 header yang sama
        $aad = $hdr_b64;
        $plaintext = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
            $cipher,
            $aad,
            $nonce,
            $cek
        );

        if ($plaintext === false) return null;

        return [
            'header' => $hdr,
            'payload' => $plaintext
        ];
    }
}

if (!function_exists('generate_refresh_token')) {
    function generate_refresh_token(int $len = 64): string {
        return bin2hex(random_bytes($len));
    }
}

if (!function_exists('hash_refresh_token')) {
    function hash_refresh_token(string $token): string {
        $pepper = env('APP_TOKEN_PEPPER') ?: '';
        return hash('sha256', $token . $pepper);
    }
}

if (!function_exists('hash_equals_safe')) {
    function hash_equals_safe(string $a, string $b): bool {
        return hash_equals($a, $b);
    }
}
