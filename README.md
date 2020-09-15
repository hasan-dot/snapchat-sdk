Snapchat Marketing SDK
=======================
A PHP SDK that can be used to get all marketing spending data and other KPIs using Snapchat API calls. 

> Don't forget to edit the `HttpHandler/SnapHttpConstants.php` file.

##Example: 
```php
$auth = new  AuthBuilder('{{CLIENT_ID}}', '{{CLIENT_SECRET}}', '{{REDIRECT_URI}}');
try{
  $auth->build();
  $accounts = new AdAccountEntityArray($auth, '{{ORGANIZATION_ID}}');
  $accounts->buildCurrentEntities();
  $accounts->getAllSubEntities($accounts);
  $org = [
    'id' => '{{ORGANIZATION_ID}}',
    'children' => []
  ];
  foreach ($accounts->getElements() as $acc){
    array_push($org['children'], $acc->printObject());
  }
  echo json_encode($org);
}catch (Exception $e){
  echo $e->getTraceAsString() . PHP_EOL;
  echo $e->getMessage();
}
```
##Response
```json
{
  "id": "ORGANIZATION_ID",
  "children": [
    {
      "adaccount_id": "AD_ACCOUNT_ID",
      "adaccount_name": "AD_ACCOUNT_NAME",
      "adaccount_children": [
        {
          "campaign_id": "CAMPAIGN_ID",
          "campaign_name": "CAMPAIGN_NAME",
          "campaign_children": [
            {
              "adsquad_id": "AD_SQUAD_ID",
              "adsquad_name": "AD_SQUAD_NAME",
              "adsquad_children": [
                {
                  "ad_id": "AD_ID",
                  "ad_name": "AD_NAME",
                  "ad_children": [],
                  "ad_created_at": "2018-10-18",
                  "ad_updated_at": "2018-10-18"
                },
                {
                ...
                }
              ],
              "adsquad_created_at": "2018-10-18",
              "adsquad_updated_at": "2019-10-02"
            },
            {
            ...
            }
          ],
          "campaign_created_at": "2018-10-18",
          "campaign_updated_at": "2018-10-22"
        },
        {
        ...
        }
      ]
    },
    {
    ...
    }
  ]
}
```