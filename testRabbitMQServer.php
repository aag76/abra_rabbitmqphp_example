#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
$mydb = new mysqli('127.0.0.1','testUser','12345','testdb');

if($mydb->errno != 0){
	echo "failed to connect to database: ". $mydb->error . PHP_EOL;
	end(0);
}
echo "successfully connected to database" . PHP_EOL;

function doLogin($username,$password)
{
    // lookup username in databas
	// check password

	$query = "SELECT password FROM students WHERE name = '$username' LIMIT 1";
	$result = $mydb->query($query);

	if($result && $result->num_rows ===1){
		$row = $result->fetch_assoc();
		if($row['password']===$password){
			echo "true";
			return true;
		}
	}
    return false;
    //return false if not valid
}

function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "login":
      return doLogin($request['username'],$request['password']);
    case "validate_session":
      return doValidate($request['sessionId']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

