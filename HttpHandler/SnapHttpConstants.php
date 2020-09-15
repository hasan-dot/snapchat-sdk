<?php

namespace Snapchat\HttpHandler;

class SnapHttpConstants {
  const tempCodeURI = "https://accounts.snapchat.com/login/oauth2/authorize";
  const authURI = "https://accounts.snapchat.com/login/oauth2/access_token";
  const baseAPIUri = "https://adsapi.snapchat.com/v1/";
  const getEntity = self::baseAPIUri . "%s/%s/%s/";
  const getMetricsUri = self::baseAPIUri . "%s/%s/stats/";
  const tokenFileName = 'snap-token.json';
  const REGION = 'eu-west-1';
  const VERSION = 'latest';
  const BUCKET_NAME = 'marketing';
  const BUCKET_PATH = '';
  const AWS_KEY = '';
  const AWS_SECRET = '';

}