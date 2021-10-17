<?php

/**
 * @copyright (c) the authors
 * @author Nicolas Dumermuth nd@nidum.org (2021-)
 * @license MIT License
 */

namespace Ndum\Laravel;

use CFPropertyList\CFPropertyList;
use CFPropertyList\CFTypeDetector;
use CFPropertyList\PListException;
use Exception;
use Illuminate\Support\Collection;

class SebConfigGenerator
{
    /**
     * @var CFPropertyList
     */
    private CFPropertyList $propertyList;

    /**
     * @var CFTypeDetector
     */
    private CFTypeDetector $typeDetector;

    /**
     * @var int
     */
    private int $iterations = 10000;

    /**
     * @var int
     */
    private int $keyLength = 32;

    /**
     * @var string
     */
    private string $pbkdf2Algo = 'sha1';

    /**
     * @var string
     */
    private string $cipherAlgo = 'aes-256-cbc';

    /**
     * @var string
     */
    private string $hmacAlgo = 'sha256';

    /**
     * @var string
     */
    private string $prefix = 'pswd';

    /**
     * @var int
     */
    private int $saltLength = 8;

    /**
     * @var int
     */
    private int $ivLength = 16;

    /**
     * @var string
     */
    private string $bytePrefix;

    /**
     * @var Collection
     */
    private Collection $keys;

    /**
     * @var Collection
     */
    private Collection $cryptoPayload;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->propertyList = new CFPropertyList();
        $this->typeDetector = new CFTypeDetector();

        $this->keys = collect(['cryptKey' => '', 'authKey' => '']);
        $this->cryptoPayload = collect(['firstSalt' => '', 'secondSalt' => '', 'iv' => '']);
        $this->bytePrefix = chr(2) . chr(1);
    }

    /**
     * @param array $sebConfig
     * @param string $startPassword
     * @param string $quitPassword
     * @param string $adminPassword
     * @return string
     * @throws PListException
     * @throws Exception
     */
    public function createSebConfig(array $sebConfig, string $startPassword = 'default', string $quitPassword = 'default', string $adminPassword = 'default'): string
    {
        $guessedStructure = $this->typeDetector->toCFType($sebConfig);
        $this->propertyList->add( $guessedStructure );

        $this->setQuitPassword($quitPassword);
        $this->setAdminPassword($adminPassword);

        return $this->encryptConfigWithPassword($this->propertyList->toXML(true), $startPassword);
    }

    /**
     * @param string $quitPassword
     */
    private function setQuitPassword(string $quitPassword): void
    {
        foreach ($this->propertyList->getValue(true) as $key => $value) {
            if ('hashedQuitPassword' === $key) {
                $value->setValue(hash('sha256', $quitPassword));
            }
        }
    }

    /**
     * @param string $adminPassword
     */
    private function setAdminPassword(string $adminPassword): void
    {
        foreach ($this->propertyList->getValue(true) as $key => $value) {
            if ('hashedAdminPassword' === $key) {
                $value->setValue(hash('sha256', $adminPassword));
            }
        }
    }

    /**
     * @param string $data
     * @param string $password
     * @return string
     * @throws Exception
     */
    private function encryptConfigWithPassword(string $data, string $password): string
    {
        $this->generateCryptoPayload();
        $this->generateKeys($password);

        return gzencode($this->generatePrefixWithPayloadAndHash(gzencode($data)));
    }

    /**
     * @param string $data
     * @return string
     */
    private function generatePrefixWithPayloadAndHash(string $data): string
    {
        return $this->prefix
            . $this->generatePayloadWithIvAndData($this->generateEncryptedData($data))
            . $this->generateHash($this->generatePayloadWithIvAndData($this->generateEncryptedData($data)));
    }

    /**
     * @param string $payload
     * @return string
     */
    private function generateHash(string $payload): string
    {
        return hash_hmac($this->hmacAlgo, $payload, $this->keys->get('authKey'), true);
    }

    /**
     * @param string $encryptedData
     * @return string
     */
    private function generatePayloadWithIvAndData(string $encryptedData): string
    {
        return $this->bytePrefix
            . implode('', $this->cryptoPayload->toArray())
            . $encryptedData;
    }

    /**
     * @param string $data
     * @return string
     */
    private function generateEncryptedData(string $data): string
    {
        return openssl_encrypt(
            $data,
            $this->cipherAlgo,
            $this->keys->get('cryptKey'),
            OPENSSL_RAW_DATA,
            $this->cryptoPayload->get('iv')
        );
    }

    /**
     * @throws Exception
     */
    private function generateCryptoPayload(): void
    {
        $this->cryptoPayload->each(function ($value, $key) {
            $this->cryptoPayload[$key] = ('iv' === $key) ? $this->generateBytes($this->ivLength)
                : $this->generateBytes($this->saltLength);
        });
    }

    /**
     * @param string $password
     */
    private function generateKeys(string $password): void
    {
        $this->keys->each(function ($value, $key) use ($password) {
            $this->keys[$key] = ('cryptKey' === $key) ? $this->generatePbkdf2Hash($password, $this->cryptoPayload->get('firstSalt'))
            : $this->generatePbkdf2Hash($password, $this->cryptoPayload->get('secondSalt'));
        });
    }

    /**
     * @param string $password
     * @param string $salt
     * @return string
     */
    private function generatePbkdf2Hash(string $password, string $salt): string
    {
        return hash_pbkdf2(
            $this->pbkdf2Algo,
            $password,
            $salt,
            $this->iterations,
            $this->keyLength,
            true
        );
    }

    /**
     * @param int $length
     * @return string
     * @throws Exception
     */
    private function generateBytes(int $length): string
    {
        return random_bytes($length);
    }
}
