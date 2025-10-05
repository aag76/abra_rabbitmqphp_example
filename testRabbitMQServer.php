#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
$mydb = new mysqli('100.85.190.111','rmq_user','Rabbit321!','testdb');

if($mydb->errno != 0){
	echo "failed to connect to database: ". $mydb->error . PHP_EOL;
	end(0);
}
echo "successfully connected to database" . PHP_EOL;

function doLogin($username,$password)
{
	global $mydb;
    // lookup username in databas
	// check password
	
	$query = "SELECT password FROM users WHERE username = '$username' LIMIT 1";
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

function doRegister($username,$password){
	global $mydb;

	//usercheck
	$query = "select * from users where username = '$username' limit 1";
	$result = $mydb->query($query);

	if($result && $result->num_rows>0){
		return array("returnCode"=>'1','message'=>"Username already exists");
	}
	//user addition
	
	$stmt = $mydb->prepare("insert into users(username,password) values(?,?)");
	
	if(!$stmt){
		return array("returnCode"=>'1','message'=>"Database error: ".$mydb->error);
	}
	//user addition confirmation
	
	$stmt->bind_param("ss",$username,$password);
	if($stmt->execute()){
		return array("returnCode"=>'0','message'=>"Registration successful");
	}
	else{
		return array("returnCode"=>'1','message'=>"Failed Registration: ".$stmt->error);
	}
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
    case "register":
      return doRegister($request['username'],$request['password']);
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

