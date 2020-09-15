<?php


namespace Snapchat\HttpHandler;


use Psr\Http\Message\ResponseInterface;

class Response {


  /**
   * @var ResponseInterface
   */
  private $rawResponse;

  /**
   * Response constructor.
   * @param ResponseInterface $rawResponse
   */
  public function __construct($rawResponse) {
    $this->rawResponse = $rawResponse;
  }

  /**
   * @return ResponseInterface
   */
  public function getRawResponse(): ResponseInterface {
    return $this->rawResponse;
  }

  public function getStatusCode() {
    return $this->rawResponse->getStatusCode();
  }

  public function getHeader($headerKey) {
    return $this->rawResponse->getHeaderLine($headerKey);
  }

  public function getRawBody() {
    return $this->rawResponse->getBody()->getContents();
  }

}