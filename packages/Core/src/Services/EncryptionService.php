<?php

namespace Packages\Core\Src\Services;

use RuntimeException;

class EncryptionService
{
    private const ALGORITHM = 'aes-256-gcm';

    private const IV_LENGTH = 12;

    private const TAG_LENGTH = 16;

    private const KEY_ID = 'v1';

    private string $encryptionKey;

    public function __construct()
    {
        $key = config('app.enc_key') ?: env('APP_ENC_KEY');

        if (empty($key)) {
            throw new RuntimeException('APP_ENC_KEY is not configured');
        }

        $this->encryptionKey = $this->normalizeKey($key);
    }

    /**
     * Encrypt a value with AES-256-GCM
     */
    public function encrypt(string $plaintext, string $table, string $column, string $recordId): string
    {
        $iv = random_bytes(self::IV_LENGTH);
        $aad = $this->buildAad($table, $column, $recordId);

        $ciphertext = openssl_encrypt(
            $plaintext,
            self::ALGORITHM,
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            $aad,
            self::TAG_LENGTH
        );

        if ($ciphertext === false) {
            throw new RuntimeException('Encryption failed');
        }

        return json_encode([
            'alg' => 'AES-256-GCM',
            'key_id' => self::KEY_ID,
            'iv' => base64_encode($iv),
            'ciphertext' => base64_encode($ciphertext),
            'tag' => base64_encode($tag),
            'aad' => $aad,
        ]);
    }

    /**
     * Decrypt a value encrypted with AES-256-GCM
     */
    public function decrypt(string $encryptedData): string
    {
        $data = json_decode($encryptedData, true);

        if (! $data || ! isset($data['iv'], $data['ciphertext'], $data['tag'], $data['aad'])) {
            throw new RuntimeException('Invalid encrypted data format');
        }

        if ($data['alg'] !== 'AES-256-GCM') {
            throw new RuntimeException('Unsupported encryption algorithm');
        }

        $iv = base64_decode($data['iv']);
        $ciphertext = base64_decode($data['ciphertext']);
        $tag = base64_decode($data['tag']);
        $aad = $data['aad'];

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::ALGORITHM,
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            $aad
        );

        if ($plaintext === false) {
            throw new RuntimeException('credential_decrypt_failed');
        }

        return $plaintext;
    }

    /**
     * Build AAD string for encryption context
     */
    private function buildAad(string $table, string $column, string $recordId): string
    {
        return sprintf('app:%s:%s:%s:%s', self::KEY_ID, $table, $column, $recordId);
    }

    /**
     * Normalize key to 32 bytes
     */
    private function normalizeKey(string $key): string
    {
        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        if (strlen($key) !== 32) {
            $key = hash('sha256', $key, true);
        }

        return $key;
    }
}
