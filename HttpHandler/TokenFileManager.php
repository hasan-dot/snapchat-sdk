<?php


namespace Snapchat\HttpHandler;


/**
 * Class TokenFileManager
 * @package Snapchat\HttpHandler
 */
class TokenFileManager {

  private $authToken;
  private $refreshToken;
  private $expiresIn;
  private $lastTimeRefreshed;
  private $valid;

  /**
   * TokenFileManager constructor.
   */
  public function __construct() {
    $this->build();
  }

  private function build() {
    $this->valid = false;
    if ($this->tokenFileExist()) {
      $bucketManager = self::getBucketInstance();
      $response = $bucketManager->getObject(SnapHttpConstants::tokenFileName);
      $response = json_decode($response, true);
      if (key_exists('access_token', $response) && key_exists('refresh_token', $response)) {
        $this->authToken = $response['access_token'];
        $this->refreshToken = $response['refresh_token'];
        $this->expiresIn = $response['expires_in'];
        $this->valid = true;
      }
    }
  }

  public function updateFile($response) {
    $bucketManager = self::getBucketInstance();
    $bucketManager->putObject(array(
      'ACL' => 'public-read',
      'Bucket' => SnapHttpConstants::BUCKET_NAME,
      'Key' => SnapHttpConstants::tokenFileName,
      'Body' => $response
    ));
    $response = json_decode($response, true);
    $this->authToken = $response['access_token'];
    $this->refreshToken = $response['refresh_token'];
    $this->expiresIn = $response['expires_in'];
    $this->lastTimeRefreshed = time();
    return $this;
  }

  private function tokenFileExist() {
    $bucketManager = self::getBucketInstance();
    $token = $bucketManager->getObject(SnapHttpConstants::tokenFileName);
    return $token === false ? false : true;
  }

  public function tokenFileValid() {
    return $this->valid;
  }

  /**
   * @return mixed
   */
  public function getAuthToken() {
    $tokenValid = $this->lastTimeRefreshed && (time() - $this->lastTimeRefreshed <= 1800);
    if ($tokenValid)
      return $this->authToken;
    return false;
  }

  /**
   * @return mixed
   */
  public function getRefreshToken() {
    return $this->refreshToken;
  }

  private static function getBucketInstance() {
    $config =  array(
      'key' => SnapHttpConstants::AWS_KEY,
      'secret' => SnapHttpConstants::AWS_SECRET,
      'certificate_authority' => false,
      'region' => SnapHttpConstants::REGION,
      'version' => SnapHttpConstants::VERSION,
      'credentials' => array(
        'key' => SnapHttpConstants::AWS_KEY,
        'secret'  => SnapHttpConstants::AWS_SECRET,
      )
    );
    return new BucketManager( $config, SnapHttpConstants::BUCKET_NAME, SnapHttpConstants::BUCKET_PATH);
  }
}