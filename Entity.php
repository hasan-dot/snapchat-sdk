<?php

namespace Snapchat;

use DateTime;
use Exception;
use Snapchat\HttpHandler\Request;
use Snapchat\HttpHandler\AuthBuilder;
use Snapchat\HttpHandler\SnapHttpConstants;
use phpDocumentor\Reflection\Types\This;

abstract class Entity {

  /**
   * @var string
   */
  protected $id;
  /**
   * @var string
   */
  protected $name;
  /**
   * @var Entity
   */
  protected $parent;
  /**
   * @var EntityArray
   */
  protected $children;
  /**
   * @var DateTime
   */
  protected $createdAt;
  /**
   * @var DateTime
   */
  protected $updatedAt;
  /**
   * @var string
   */
  protected $metricsUri;
  /**
   * @var Request
   */
  protected $request;
  /**
   * @var AuthBuilder
   */
  protected $authBuilder;

  /**
   * @var []
   */
  protected $metrics;



  /**
   * Entity constructor.
   * @param AuthBuilder $authBuilder
   * @param Entity | string $parent
   * @throws Exception
   */
  public function __construct(AuthBuilder $authBuilder, $parent) {
    $this->authBuilder = $authBuilder;
    if (!($parent instanceof Entity) && !is_string($parent)) {
      throw new Exception('Parent must be a string or instance of Snapchat\\Entity');
    }
    $this->parent = $parent;
    $this->request = new Request();
  }


  /**
   * Entity builder.
   * @param string $id
   * @param string $name
   * @param DateTime $createdAt
   * @param DateTime $updatedAt
   */
  public function build(string $id, string $name, DateTime $createdAt, DateTime $updatedAt) {
    $this->id = $id;
    $this->name = $name;
    $this->createdAt = $createdAt;
    $this->updatedAt = $updatedAt;
    $classPlural = explode('Entity', EntityFields::getClassName($this))[0] . 's';
    $this->metricsUri = sprintf(SnapHttpConstants::getMetricsUri, strtolower($classPlural), $this->id);
  }

  protected function getCurrentEntityMetrics($date = "") {
    $dateRange = EntityFields::yesterdayDate($date);
    $extras = [
      'fields' => EntityFields::getAllFields(),
      'granularity' => EntityFields::DAILY,
      'start_time' => $dateRange['since'],
      'end_time' => $dateRange['until'],
    ];
    $auth = [
      'Authorization' => 'Bearer ' . $this->authBuilder->getAccessToken()
    ];
    $response = $this->request->build($this->metricsUri)
      ->setAuth($auth)
      ->setRequestExtras($extras)
      ->get()
      ->getRawBody();
    $return = json_decode($response, true);
    return $return['timeseries_stats'][0]['timeseries_stat']['timeseries'][0]['stats'];
  }

  public function getEntityMetrics($date = "") {
    $this->metrics = $this->getCurrentEntityMetrics($date);
    return $this;
  }

  /**
   * @return string
   */
  public function getId(): string {
    return $this->id;
  }

  /**
   * @return string
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * @return Entity
   */
  public function getParent(): Entity {
    return $this->parent;
  }
  /**
   * @return EntityArray
   */
  public function getChildren(): ?EntityArray {
    return $this->children;
  }

  /**
   * @param EntityArray $children
   */
  public function setChildren(EntityArray $children): void {
    $this->children = $children;
  }

  /**
   * @return array
   */
  public function getMetrics() {
    return $this->metrics;
  }



  /**
   * @return AuthBuilder
   */
  public function getAuthBuilder(): AuthBuilder {
    return $this->authBuilder;
  }

  public function printObject() {
    $entity = strtolower(explode('Entity', EntityFields::getClassName($this))[0]);
    $idKey = $entity . '_id';
    $nameKey = $entity . '_name';
    $childrenKey = $entity . '_children';
    $createdKey = $entity . '_created_at';
    $updatedKey = $entity . '_updated_at';
    $metricsKey = $entity . '_metrics';
    $children = [];
    if ($this->parent->getChildren()->getClassName() === EntityFields::ADS){
      return [
        $idKey => $this->id,
        $nameKey => $this->name,
        $metricsKey => $this->metrics,
        $createdKey => $this->createdAt->format("Y-m-d"),
        $updatedKey => $this->updatedAt->format("Y-m-d"),
      ];
    }
    foreach ($this->getChildren()->getElements() as $child)
      array_push($children, $child->printObject());
    return [
      $idKey => $this->id,
      $nameKey => $this->name,
      $childrenKey => $children,
      $createdKey => $this->createdAt->format("Y-m-d"),
      $updatedKey => $this->updatedAt->format("Y-m-d"),
    ];
  }

}