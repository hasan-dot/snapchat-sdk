<?php


namespace Snapchat\HttpHandler;

use GuzzleHttp\Client;
use Snapchat\Entity;
use Exception;
use GuzzleHttp\Exception\ClientException;

class Request {

  /**
   * @var Response
   */
  private $response;

  /**
   * @var string
   */
  private $requestUri;

  /**
   * @var array
   */
  private $requestExtras;


  /**
   * @var Client
   */
  private $httpClient;
  /**
   * @var array
   */
  private $auth;


  /**
   * Request constructor.
   * @param array $requestExtras
   */
  public function __construct($requestExtras = []) {
    $this->httpClient = new Client();
    $this->requestExtras = $requestExtras;
  }

  /**
   * @param Entity | string $entity
   * @return Request
   */
  public function build($entity = null) {
    if ($entity instanceof Entity){
      $this->requestUri = sprintf($entity->getEntityUri(), $entity->getEntityUri());
    } elseif (is_string($entity)) {
      $this->requestUri = $entity;
    } else {
      throw new Exception('Entity must be a string or instance of ' . get_class(Entity::class));
    }
    return $this;
  }

  /**
   * @param array $requestExtras
   * @return Request
   */
  public function setRequestExtras(array $requestExtras): Request {
    $this->requestExtras = $requestExtras;
    return $this;
  }


  /**
   * @return Response
   */
  public function post() {
    $extras['form_params'] = $this->requestExtras;
    $this->requestExtras = $extras;
    $rawResponse = $this->httpClient->post($this->requestUri, $this->requestExtras);
    $this->response = new Response($rawResponse);
    return $this->response;

  }

  /**
   * @return Response
   * @throws Exception
   */
  public function get($debug = false) {
    $query = '?';
    foreach ($this->requestExtras as $key => $value) {
      $query .= $key . '=' . $value . '&';
    }
    $uri = $this->requestUri  . $query;
    $extras['headers'] =  $this->auth;
    $extras['debug'] =  $debug;
    try {
      $rawResponse = $this->httpClient
        ->get($uri, $extras);
    } catch (ClientException $exception) {
      throw new Exception($exception->getResponse()->getBody()->getContents());
    }

    $this->response = new Response($rawResponse);
    return $this->response;
  }

  public function setAuth(array $auth) {
    $this->auth = $auth;
    return $this;
  }

}