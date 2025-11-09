<?php
namespace App\Libraries;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\Encryption\Algorithm\KeyEncryption\RSAOAEP256;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A256GCM;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Encryption\JWEDecrypter;
use Jose\Component\Core\JWKSet;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Encryption\Serializer\CompactSerializer;

class JweService
{
    protected string $privateKeyPath;
    protected string $publicKeyPath;

    public function __construct()
    {
        $this->privateKeyPath = getenv('JWT_PRIVATE_KEY_PATH');
        $this->publicKeyPath  = getenv('JWT_PUBLIC_KEY_PATH');
    }

    /** 🔑 Load private key as JWK */
    protected function loadPrivateJwk(): JWK
    {
        return JWKFactory::createFromKeyFile(
            $this->privateKeyPath,
            null,
            [
                'use' => 'enc',
                'alg' => 'RSA-OAEP-256',
                'kid' => getenv('JWT_KID') ?: 'kid-private',
            ]
        );
    }

    /** 🔑 Load public key as JWK */
    protected function loadPublicJwk(): JWK
    {
        return JWKFactory::createFromKeyFile(
            $this->publicKeyPath,
            null,
            [
                'use' => 'enc',
                'alg' => 'RSA-OAEP-256',
                'kid' => getenv('JWT_KID') ?: 'kid-public',
            ]
        );
    }

    /** ✉️ Encrypt payload into JWE Compact */
    public function encrypt(array $payload, int $expSeconds = 600): string
    {
        $jwk = $this->loadPublicJwk();
        $payload['iat'] = time();
        $payload['exp'] = time() + $expSeconds;

        // ✅ AlgorithmManager expects objects, not strings
        $keyEnc = new AlgorithmManager([new RSAOAEP256()]);
        $contentEnc = new AlgorithmManager([new A256GCM()]);

        $builder = new JWEBuilder($keyEnc, $contentEnc);

        $jwe = $builder
            ->create()
            ->withPayload(json_encode($payload, JSON_UNESCAPED_SLASHES))
            ->withSharedProtectedHeader([
                'alg' => 'RSA-OAEP-256',
                'enc' => 'A256GCM',
                'kid' => getenv('JWT_KID') ?: 'kid-public',
            ])
            ->addRecipient($jwk)
            ->build();

        return (new CompactSerializer())->serialize($jwe, 0);
    }

    /** 🔓 Decrypt JWE into payload array */
    public function decrypt(string $token): array
    {
        $privateJwk = $this->loadPrivateJwk();

        $keyEnc = new AlgorithmManager([new RSAOAEP256()]);
        $contentEnc = new AlgorithmManager([new A256GCM()]);

        $decrypter = new JWEDecrypter($keyEnc, $contentEnc);
        $serializer = new CompactSerializer();
        $jwe = $serializer->unserialize($token);

        $jwkSet = new JWKSet([$privateJwk]);
        $success = $decrypter->decryptUsingKeySet($jwe, $jwkSet, 0);

        if (!$success) {
            throw new \Exception('Failed to decrypt JWE');
        }

        $payload = json_decode($jwe->getPayload(), true);
        if (!$payload) {
            throw new \Exception('Invalid payload JSON');
        }

        if (isset($payload['exp']) && time() > $payload['exp']) {
            throw new \Exception('Token expired');
        }

        return $payload;
    }
}
