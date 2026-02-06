<?php
/**
 * JSON Web Token implementation
 * Minimum implementation used by Firebase\JWT
 */
namespace Firebase\JWT;

use \DomainException;
use \InvalidArgumentException;
use \UnexpectedValueException;
use \DateTime;

class JWT {
    /**
     * Encode a PHP object into a JSON Web Token.
     */
    public static function encode($payload, $key, $alg = 'HS256') {
        $header = ['typ' => 'JWT', 'alg' => $alg];
        
        $segments = [];
        $segments[] = static::urlsafeB64Encode(static::jsonEncode($header));
        $segments[] = static::urlsafeB64Encode(static::jsonEncode($payload));
        
        $signing_input = implode('.', $segments);
        $signature = static::sign($signing_input, $key, $alg);
        $segments[] = static::urlsafeB64Encode($signature);
        
        return implode('.', $segments);
    }
    
    /**
     * Decode a JSON Web Token.
     */
    public static function decode($jwt, $key) {
        $tks = explode('.', $jwt);
        
        if (count($tks) != 3) {
            throw new UnexpectedValueException('Wrong number of segments');
        }
        
        list($headb64, $bodyb64, $cryptob64) = $tks;
        
        if (null === ($header = static::jsonDecode(static::urlsafeB64Decode($headb64)))) {
            throw new UnexpectedValueException('Invalid header encoding');
        }
        
        if (null === $payload = static::jsonDecode(static::urlsafeB64Decode($bodyb64))) {
            throw new UnexpectedValueException('Invalid claims encoding');
        }
        
        if (false === ($sig = static::urlsafeB64Decode($cryptob64))) {
            throw new UnexpectedValueException('Invalid signature encoding');
        }
        
        // Check signature
        if (!static::verify("$headb64.$bodyb64", $sig, $key->getKeyMaterial(), $key->getAlgorithm())) {
            throw new UnexpectedValueException('Signature verification failed');
        }
        
        // Check if token is expired
        if (isset($payload->exp) && $payload->exp < time()) {
            throw new UnexpectedValueException('Expired token');
        }
        
        return $payload;
    }
    
    /**
     * Sign a string with a given key and algorithm.
     */
    private static function sign($msg, $key, $alg = 'HS256') {
        $methods = [
            'HS256' => 'sha256',
            'HS384' => 'sha384',
            'HS512' => 'sha512',
        ];
        
        if (empty($methods[$alg])) {
            throw new DomainException('Algorithm not supported');
        }
        
        return hash_hmac($methods[$alg], $msg, $key, true);
    }
    
    /**
     * Verify a signature with the message, key and method.
     */
    private static function verify($msg, $signature, $key, $alg = 'HS256') {
        $hash = static::sign($msg, $key, $alg);
        return hash_equals($signature, $hash);
    }
    
    /**
     * Encode a PHP object into a JSON string.
     */
    private static function jsonEncode($input) {
        $json = json_encode($input);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            throw new DomainException('JSON encoding error: ' . json_last_error_msg());
        }
        return $json;
    }
    
    /**
     * Decode a JSON string into a PHP object.
     */
    private static function jsonDecode($input) {
        $obj = json_decode($input);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            throw new DomainException('JSON decoding error: ' . json_last_error_msg());
        }
        return $obj;
    }
    
    /**
     * Encode a string with URL-safe Base64.
     */
    private static function urlsafeB64Encode($input) {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }
    
    /**
     * Decode a string with URL-safe Base64.
     */
    private static function urlsafeB64Decode($input) {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }
}

class Key {
    private $keyMaterial;
    private $algorithm;
    
    public function __construct($keyMaterial, $algorithm) {
        $this->keyMaterial = $keyMaterial;
        $this->algorithm = $algorithm;
    }
    
    public function getKeyMaterial() {
        return $this->keyMaterial;
    }
    
    public function getAlgorithm() {
        return $this->algorithm;
    }
}
