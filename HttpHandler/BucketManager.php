<?php


namespace Snapchat\HttpHandler;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;


class BucketManager
{
  private $s3Client;
  private $bucketName;
  private $path;

  public function __construct(array $config, $bucketName, $path)
  {
    $this->bucketName = $bucketName;
    $this->s3Client = new S3Client($config);
    $this->path = $path;
  }

  public function putObject(array $fileArray){
    try {
      $this->s3Client->putObject($fileArray);
      print "Uploaded Successfully" . PHP_EOL;
    } catch (S3Exception $e) {
      echo $e->getAwsErrorMessage() . "\n";
    }

  }

  public function getObject($fileName) {
    try {
      $result = $this->s3Client->getObject([
        'Bucket' => $this->bucketName,
        'Key'    => $this->path . $fileName
      ]);
      return (string) $result['Body'];
    } catch (S3Exception $e) {
      echo "File not found";
      return false;
    }
  }

  public function removeObject($fileName){
    try {
      $this->s3Client->deleteObject([
        'Bucket' => $this->bucketName,
        'Key' => $this->path . $fileName,
      ]);
      print "Uploaded Successfully" . PHP_EOL;
    } catch (S3Exception $e) {
      echo $e->getMessage() . "\n";
    }
  }

  public function printAll($dir) {
    $results = $this->s3Client->getPaginator('ListObjects', [
      'Bucket' => $this->bucketName,
      "Prefix" => $dir
    ]);

    foreach ($results as $result) {
      foreach ($result['Contents'] as $object) {
        echo $object['Key'] . PHP_EOL;
      }
    }
  }


}