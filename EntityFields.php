<?php

namespace Snapchat;


use DateTime;
use DateTimeZone;
use DateInterval;
use ReflectionClass;

class EntityFields {
  // Extra Metrics
  const ANDROID_INSTALLS = 'android_installs';
  const AVG_SCREEN_TIME_MILLIS = 'avg_screen_time_millis';
  const ATTACHMENT_FREQUENCY = 'attachment_frequency';
  const ATTACHMENT_QUARTILE_1 = 'attachment_quartile_1';
  const ATTACHMENT_QUARTILE_2 = 'attachment_quartile_2';
  const ATTACHMENT_QUARTILE_3 = 'attachment_quartile_3';
  const ATTACHMENT_TOTAL_VIEW_TIME_MILLIS = 'attachment_total_view_time_millis';
  const ATTACHMENT_UNIQUES = 'attachment_uniques';
  const ATTACHMENT_VIEW_COMPLETION = 'attachment_view_completion';
  const FREQUENCY = 'frequency';
  const IMPRESSIONS = 'impressions';
  const IOS_INSTALLS = 'ios_installs';
  const QUARTILE_1 = 'quartile_1';
  const QUARTILE_2 = 'quartile_2';
  const QUARTILE_3 = 'quartile_3';
  const SPEND = 'spend';
  const SWIPE_UP_PERCENT = 'swipe_up_percent';
  const SWIPES = 'swipes';
  const TOTAL_INSTALLS = 'total_installs';
  const UNIQUES = 'uniques';
  const VIEW_COMPLETION = 'view_completion';
  const SCREEN_TIME_MILLIS = 'screen_time_millis';

  const ORGANIZATIONS = 'Organization';
  const ADACCOUNTS = 'AdAccount';
  const CAMPAIGNS = 'Campaign';
  const ADSQUADS = 'AdSquad';
  const ADS = 'Ad';


  const RELATIONS = [
    self::ORGANIZATIONS => self::ADACCOUNTS,
    self::ADACCOUNTS => self::CAMPAIGNS,
    self::CAMPAIGNS => self::ADSQUADS,
    self::ADSQUADS => self::ADS
  ];

  // Granularity
  const DAILY = 'DAY';

  public static function getAllFields() {
    return implode(',', [
      self::ANDROID_INSTALLS,
      self::AVG_SCREEN_TIME_MILLIS,
      self::ATTACHMENT_FREQUENCY,
      self::ATTACHMENT_QUARTILE_1,
      self::ATTACHMENT_QUARTILE_2,
      self::ATTACHMENT_QUARTILE_3,
      self::ATTACHMENT_TOTAL_VIEW_TIME_MILLIS,
      self::ATTACHMENT_UNIQUES,
      self::ATTACHMENT_VIEW_COMPLETION,
      self::FREQUENCY,
      self::IMPRESSIONS,
      self::IOS_INSTALLS,
      self::QUARTILE_1,
      self::QUARTILE_2,
      self::QUARTILE_3,
      self::SPEND,
      self::SWIPE_UP_PERCENT,
      self::SWIPES,
      self::TOTAL_INSTALLS,
      self::UNIQUES,
      self::VIEW_COMPLETION,
      self::SCREEN_TIME_MILLIS
    ]);
  }

  public static function yesterdayDate($date = "") {
    if (empty($date)) {
      $date = date("Y-m-d", time());
    }
    $date = date("Y-m-d", time());
    $dateTime = new DateTime($date);
    $dateTime->setTimezone(new DateTimeZone('America/Los_Angeles'));
    $dateTime->setTime(0,0);
    $dateRange = [
      'since' => $dateTime->format(DateTime::ATOM),
      'until' => date_add($dateTime, new DateInterval('P1D'))->format(DateTime::ATOM)
    ];
    return $dateRange;
  }

  public static function getClassName($object) {
    $reflect = new ReflectionClass($object);
    return $reflect->getShortName();
  }


  /**
   * @param EntityArray $object
   * @return bool|string
   */
  public static function getChildClassName($object) {
    if(isset(self::RELATIONS[$object->getClassName()])){
      return self::RELATIONS[$object->getClassName()] . 'EntityArray';
    }
    return false;
  }
}