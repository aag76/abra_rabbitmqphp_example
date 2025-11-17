#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$apiKey = "X06HO4GPPMMFGJJ";
$stockSymbols = array("AAPL","GOOGL","AMZN","MSFT","TSLA");

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");

function fetchStockData($symbol,$apiKey){
	$apiURL = "https://www.alphavantage.co/query?function=TIME_SERIS_DAILY"."&symbol={$symbol}&interval=5min&apikey={$apiKey}";
	$response = @file_get_contents($apiURL);
	if($response === FALSE){
		echo "data can't be reached";
		return null;
	}
	$data = json_decode($response,true);
	if(!data || isset($data['Error Meessage'])){
		echo "Invalid data receieved";
		return null;
	}

	$timeSeries = $data['Time Series (5mi)'] ?? null;
	if(!timeSeries){
		echo "No time series data found";
		return null;
	}
	$latestTime = array_key_first($timeSeries);
	$latestData = $timSeries[$latestTime];
	return array(
		'symbol' => $symbol,
		'price' => $latestData['1. open'],
		'timestamp' => $latestTime,
	);
}

foreach($stocksymbols as $symbol){
	echo "fetching data for {$symbol}";

	$stockInfo = fetchStockData($symbol,$apiKey);

	$request = array(
		'type' = "StockData",
		'symbol'= $stockInfo['symbol'],
		'price'=> $stockInfo['price'],
		'timestamp'=>$stockInfo['timestamp'],
		'source'=>"alphavantage"
	);
	echo "sending {$symbol} data to broker";
	$response = $client->send_request($request);
	sleep(15);
}

/*	
if (isset($argv[1]))
{
  $msg = $argv[1];
}
else
{
  $msg = "test message";
}

$request = array();
$request['type'] = "Login";
$request['username'] = "steve";
$request['password'] = "password";
$request['message'] = $msg;
$response = $client->send_request($request);
//$response = $client->publish($request);

echo "client received response: ".PHP_EOL;
print_r($response);
echo "\n\n";

echo $argv[0]." END".PHP_EOL;
 */
