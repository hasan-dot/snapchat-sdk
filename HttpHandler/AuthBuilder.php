<?php

namespace Snapchat\HttpHandler;

use GuzzleHttp\Client;
use Exception;

class AuthBuilder {
  private $clientId;
  private $clientSecret;
  private $redirectUri;
  private $httpClient;
  private $tokenFileManager;
  /**
   * @var Request
   */
  private $request;

  /**
   * AuthBuilder constructor.
   * @param $clientId
   * @param $clientSecret
   * @param $redirectUri
   */
  public function __construct($clientId, $clientSecret, $redirectUri) {
    $this->clientId = $clientId;
    $this->clientSecret = $clientSecret;
    $this->redirectUri = $redirectUri;
    $this->httpClient = new Client();
    $this->tokenFileManager = new TokenFileManager();
    $this->request = new Request();
  }

  public function build() {
    $this->buildToken();
    return $this;
  }

  private function buildToken() {
    if (!$this->tokenFileManager->tokenFileValid()) {
      $this->printTempCodeURI();
      $code = readline('Please enter the code after you authorize the api through the above link: ');
      $this->getAccessTokenFromCode($code);
    } else {
      $this->refreshAccessToken();
    }
  }

  public function getAccessToken() {
    $authToken = $this->tokenFileManager->getAuthToken();
    if ($authToken === false) {
      $this->refreshAccessToken();
    }
    return $this->tokenFileManager->getAuthToken();
  }

  private function printTempCodeURI() {
    $query = [
      'redirect_uri' => $this->redirectUri,
      'response_type' => 'code',
      'client_id' => $this->clientId,
      'scope' => 'snapchat-marketing-api'
    ];
    echo $this->getRequestLinkBuilder(SnapHttpConstants::tempCodeURI, $query) . PHP_EOL;
  }

  private function getRequestLinkBuilder($uri, $params) {
    $url = $uri . '?';
    foreach ($params as $param => $value) {
      $url = $url . $param . '=' . $value . '&';
    }
    return $url;
  }

  private function getAccessTokenFromCode($code) {
    $extras = [
      'code' => $code,
      'client_id' => $this->clientId,
      'client_secret' => $this->clientSecret,
      'grant_type' => 'authorization_code',
      'redirect_uri' => $this->redirectUri
    ];
    $response = $this->request->build(SnapHttpConstants::authURI)
      ->setRequestExtras($extras)
      ->post()
      ->getRawBody();
    $this->tokenFileManager->updateFile($response);
  }

  private function refreshAccessToken() {
    $extras = [
      'refresh_token' => $this->tokenFileManager->getRefreshToken(),
      'client_id' => $this->clientId,
      'client_secret' => $this->clientSecret,
      'grant_type' => 'refresh_token',
      'redirect_uri' => $this->redirectUri
    ];
    $response = $this->request->build(SnapHttpConstants::authURI)
      ->setRequestExtras($extras)
      ->post()
      ->getRawBody();
    $this->tokenFileManager->updateFile($response);
  }
}