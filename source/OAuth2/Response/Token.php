<?php
/*
 * Copyright 2015 Alexey Maslov <alexey.y.maslov@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace alxmsl\Google\OAuth2\Response;

use alxmsl\Google\InitializationInterface;
use OutOfBoundsException;

/**
 * Authorized token object
 * @author alxmsl
 * @date 2/4/13
 */ 
final class Token implements InitializationInterface {
    /**
     * Token type constants
     */
    const TYPE_UNKNOWN = 0,
          TYPE_BEARER  = 1;

    /**
     * @var string access token
     */
    private $accessToken = '';

    /**
     * @var int token type
     */
    private $tokenType = self::TYPE_BEARER;

    /**
     * @var int access token expires in
     */
    private $expiresIn = 0;

    /**
     * @var string id token
     */
    private $idToken = '';

    /**
     * @var string refresh token
     */
    private $refreshToken = '';

    /**
     * @var bool online or offline access token
     */
    private $online = false;

    private function __construct() {}

    /**
     * Setter for access token
     * @param string $accessToken access token
     * @return Token self
     */
    private function setAccessToken($accessToken) {
        $this->accessToken = (string) $accessToken;
        return $this;
    }

    /**
     * Getter for access token
     * @return string access token
     */
    public function getAccessToken() {
        return $this->accessToken;
    }

    /**
     * Setter for access token expires in
     * @param int $expiresIn access token expires in
     * @return Token self
     */
    public function setExpiresIn($expiresIn) {
        $this->expiresIn = (int) $expiresIn;
        return $this;
    }

    /**
     * Getter for access token expires in
     * @return int access token expires in
     */
    public function getExpiresIn() {
        return $this->expiresIn;
    }

    /**
     * Setter for id token
     * @param string $idToken id token
     * @return Token self
     */
    public function setIdToken($idToken) {
        $this->idToken = (string) $idToken;
        return $this;
    }

    /**
     * Getter for id token
     * @return string id token
     */
    public function getIdToken() {
        return $this->idToken;
    }

    /**
     * Setter for refresh token
     * @param string $refreshToken refresh token
     * @return Token self
     */
    public function setRefreshToken($refreshToken) {
        $this->refreshToken = (string) $refreshToken;
        return $this;
    }

    /**
     * Getter for refresh token
     * @return string refresh token
     * @throws OutOfBoundsException for online access tokens
     */
    public function getRefreshToken() {
        if ($this->isOnline()) {
            throw new OutOfBoundsException('online tokens has not refresh tokens');
        }
        return $this->refreshToken;
    }

    /**
     * Setter for token type
     * @param string $tokenType token type string
     * @return Token self
     */
    public function setTokenType($tokenType) {
        switch ($tokenType) {
            case 'Bearer':
                $this->tokenType = self::TYPE_BEARER;
                break;
            default:
                $this->tokenType = self::TYPE_UNKNOWN;
        }
        return $this;
    }

    /**
     * Getter for token type code
     * @return int token type code
     */
    public function getTokenType() {
        return $this->tokenType;
    }

    /**
     * Setter for online flag value
     * @param bool $online online flag value
     */
    private function setOnline($online) {
        $this->online = !!$online;
    }

    /**
     * Getter for online flag
     * @return bool online flag value
     */
    public function isOnline() {
        return $this->online;
    }

    /**
     * Method for object initialization by the string
     * @param string $string response string
     * @return Token response object
     */
    public static function initializeByString($string) {
        $object = json_decode($string);
        $Response = new self();
        $Response->setAccessToken($object->access_token)
            ->setExpiresIn($object->expires_in)
            ->setTokenType($object->token_type);

        if (isset($object->id_token)) {
            $Response->setIdToken($object->id_token);
        }

        $Response->setOnline(!isset($object->refresh_token));
        if (!$Response->isOnline()) {
            $Response->setRefreshToken($object->refresh_token);
        }
        return $Response;
    }

    /**
     * @inheritdoc
     */
    public function __toString() {
        $format = <<<'EOD'
    access token:  %s
    expires in:    %s
    id token:      %s
    refresh token: %s
    token type:    %s
EOD;
        try {
            $refreshToken = $this->getRefreshToken();
        } catch (OutOfBoundsException $Ex) {
            $refreshToken = 'not available for online type';
        }
        return sprintf($format
            , $this->getAccessToken()
            , $this->getExpiresIn()
            , $this->getIdToken() ?: '-'
            , $refreshToken
            , $this->getTokenType());
    }
}
