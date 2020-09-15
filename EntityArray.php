<?php


namespace Snapchat;


use Snapchat\HttpHandler\SnapHttpConstants;
use Exception;
use Snapchat\HttpHandler\Request;
use DateTime;
use Snapchat\HttpHandler\AuthBuilder;
use ReflectionClass;

abstract class EntityArray {
  /**
   * @var AuthBuilder
   */
  protected $authBuilder;
  /**
   * @var Entity[]
   */
  protected $elements = array();

  /**
   * @var string
   */
  protected $className;

  /**
   * @var string
   */
  protected $entityUri;
  /**
   * @var string
   */
  protected $parentId;
  /**
   * @var Request
   */
  protected $request;
  /**
   * @var Entity|string
   */
  protected $parent;

  /**
   * EntityArray constructor.
   * @param AuthBuilder $authBuilder
   * @param Entity | string $parent
   * @throws Exception
   */
  public function __construct(AuthBuilder $authBuilder, $parent) {
    $this->authBuilder = $authBuilder;
    if ($parent instanceof Entity){
      $this->parentId = $parent->getId();
    } elseif (is_string($parent)) {
      $this->parentId = $parent;
    } else {
      throw new Exception('Parent must be a string or instance of  Snapchat\\Entity');
    }
    $this->parent = $parent;
    $this->className = explode('EntityArray', EntityFields::getClassName($this))[0];
    $this->entityUri = $this->buildUri();
    $this->request = new Request();
  }

  protected function buildUri() {
    $pluralName = strtolower($this->className . 's');
    $parentName = $key = array_search ($this->className, EntityFields::RELATIONS);
    $pluralParentName = strtolower($parentName . 's');
    return sprintf(SnapHttpConstants::getEntity, $pluralParentName, $this->parentId, $pluralName);
  }

  /**
   * @return array
   */
  protected function getCurrentEntities() {
    $extras = [];
    $auth = [
      'Authorization' => 'Bearer ' . $this->authBuilder->getAccessToken()
    ];
    $response = $this->request->build($this->entityUri)
      ->setAuth($auth)
      ->setRequestExtras($extras)
      ->get()
      ->getRawBody();
    $pluralName = strtolower($this->className . 's');
    return json_decode($response, true)[$pluralName];
  }

  public function buildCurrentEntities() {
    $responseArray = $this->getCurrentEntities();
    foreach ($responseArray as $element) {
      $index = strtolower($this->className);
      $element = $element[$index];
      $class = __NAMESPACE__ . "\\" . $this->className . "Entity";
      $entity = new $class($this->authBuilder, $this->parent);
      $id = $element ['id'];
      $name = $element['name'];
      $createdAt = new DateTime($element['created_at']);
      $updatedAt = new DateTime($element['updated_at']);
      $entity->build($id, $name, $createdAt, $updatedAt);
      array_push($this->elements, $entity);
    }
    $this->linkToParent();
  }

  protected function linkToParent() {
    if ($this->parent instanceof Entity){
      $this->parent->setChildren($this);
    }
  }

  /**
   *
   */
  private function getChildrenEntities() {
    foreach ($this->elements  as $element) {
      $class = __NAMESPACE__ . "\\" . EntityFields::getChildClassName($this);
      if (!$class){
        break;
      }
      $children = new $class($element->getAuthBuilder(), $element);
      $children->buildCurrentEntities();
    }
  }

  /**
   * @param EntityArray $object
   */
  public function getAllSubEntities($object, $date = "") {
    if ($object->getClassName() === EntityFields::ADS){
      $object->getAllCurrentMetrics($date);
      return;
    }
    $object->getChildrenEntities();
    foreach ($object->getElements() as $element) {
      $subElement = $element->getChildren();
      if ($subElement) {
        $subElement->getAllSubEntities($subElement, $date);
      }
    }
  }

  /**
   * @return Entity[]
   */
  public function getElements() {
    return $this->elements;
  }

  public function getAllCurrentMetrics($date = "") {
    foreach ($this->elements as $entity) {
      $entity->getEntityMetrics($date);
    }
    return $this;
  }

  /**
   * @return string
   */
  public function getClassName(): string {
    return $this->className;
  }

  public function getAllEntityNames() {
    foreach ($this->elements as $entity) {
      echo $entity->getName() . PHP_EOL;
    }
  }
}