<?php

require '../../../../../vendor/autoload.php'; //@TODO Needed to add __DIR__ Constant to do not be folder dependend. => Adding as a console command to pull magento root constant folder
require_once './Dependencies/AD_SimpleCSVScript/class.csv.php';

use Zend\Http\Headers;
use Zend\Http\Request;
use Zend\Http\Client;
use Zend\Stdlib\Parameters;


// basic information needed for requests
$restTokenUri ='https://m2.reactiveparts.com/index.php/rest/V1/integration/admin/token';
$Url = 'http://m2.reactiveparts.com/rest/V1/products/';

//The following query returns only the sku and name parameters for product items whose category_gear attribute includes the value 86.
$restResourceUriQuery ='?searchCriteria[filter_groups][0][filters][0][field]=category_gear&searchCriteria[filter_groups][0][filters][0][value]=86&searchCriteria[filter_groups][0][filters][0][condition_type]=finset&fields=items[sku,name]';

//Search Criteria For Products Based on "created_at" greater Than
$restResourceUriQuery='?searchCriteria[filter_groups][0][filters][0][field]=created_at&searchCriteria[filter_groups][0][filters][0][value]=2020-07-01 00:00:00&searchCriteria[filter_groups][0][filters][0][condition_type]=gt&fields=items[sku,name]';

$restResourceUri=$Url.$restResourceUriQuery;

$tokenUsername = 'orderwise_integrate';
$tokenPassword = '6qf2fCzaL!*6';


// create request for token using username and password
$httpHeaders = new Headers();
$httpHeaders->addHeaders([
    'Content-Type' => 'application/json'
]);
$request = new Request();
$request->setHeaders($httpHeaders);
$request->setUri($restTokenUri);
$request->setMethod(Request::METHOD_POST);
$request->setContent(sprintf('{"username":"%s", "password":"%s"}', $tokenUsername, $tokenPassword));


// create client and get token response
$client = new Client();
$options = [
    'adapter'   => 'Zend\Http\Client\Adapter\Curl',
    'curloptions' => [CURLOPT_FOLLOWLOCATION => true],
    'maxredirects' => 0,
    'timeout' => 30
];
$client->setOptions($options);

$response = $client->send($request);

$token = trim($response->getBody(), '"');


// create request for cms block using bearer token Authorization header
$httpHeaders = new Headers();
$httpHeaders->addHeaders([
    'Authorization' => sprintf('Bearer %s', $token),
    'Content-Type' => 'application/json'
]);
$request = new Request();
$request->setHeaders($httpHeaders);
$request->setUri($restResourceUri);
$request->setMethod(Request::METHOD_GET);


// create client and get cms block response
$client = new Client();
$options = [
    'adapter'   => 'Zend\Http\Client\Adapter\Curl',
    'curloptions' => [CURLOPT_FOLLOWLOCATION => true],
    'maxredirects' => 0,
    'timeout' => 30
];
$client->setOptions($options);

//$response = $client->send($request);// @TODO Uncomment to pull data from M2

//echo $response->getBody();

$CSV='Data/m1_export.csv';

$importer = new CsvImporter($CSV,',',true);
if(!$importer->headerExists('sku')) {echo 'Missing column sku in the CSV.'; exit;}
 foreach($importer->get() as $num=>$ligne) {
    print_r($ligne);
    //echo $ligne['sku'];
 }
