<?php
error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", 1);

// proxy thru libcurl

$req_method = strtoupper($_SERVER["REQUEST_METHOD"]);
$req_url = $_SERVER["QUERY_STRING"];
$req_headers = apache_request_headers();
$req_body = file_get_contents("php://input");
$req_body_size = strlen($req_body);

$headers = array();
foreach($req_headers as $name => $value)
{
	$headers[] = "$name: $value";
}

$options = array();
$options[CURLOPT_CUSTOMREQUEST] = $req_method; 
$options[CURLOPT_URL] = $req_url;
//$options[CURLOPT_FOLLOWLOCATION] = 1;
//$options[CURLOPT_HEADER] = 1;
$options[CURLOPT_NOPROGRESS] = false;
$options[CURLOPT_BUFFERSIZE] = 128;
$options[CURLOPT_RETURNTRANSFER] = 1;
$options[CURLOPT_TIMEOUT] = 0;
$options[CURLOPT_SSL_VERIFYPEER] = false;
if ( $req_body_size > 0 )
{
	$options[CURLOPT_POSTFIELDS] = $req_body;
	$headers[] = "Content-Length: {$req_body_size}";
}
else
{
	$options[CURLOPT_POSTFIELDS] = null;
	$headers[] = "Content-Length: 0";
}

$options[CURLOPT_WRITEFUNCTION] = function($ch, $buf)
{
	$len = strlen($buf);
	echo $buf;
	
	return $len;
};

/*
$options[CURLOPT_HEADERFUNCTION] = function($ch, $line)
{
	$len = strlen($line);
	header($line);
	
	return $len;
};
//*/

$options[CURLOPT_HTTPHEADER] = $headers;

$ch = curl_init();

curl_setopt_array($ch, $options);
$response = curl_exec($ch);

var_dump($response);
var_dump(curl_errno($ch));
var_dump(curl_error($ch));

curl_close($ch);

/*
// proxy thru fopen()

$fh = fopen('http://tmscp-ps-mgnt01s.sjc1/rmx/api.php/event/ps/api?h=tmscp-ps-ap01.sjc1', 'r');
stream_set_timeout($fh, 0);
stream_set_blocking($fh, 0); 
while(!feof($fh))
{
	$line = fgets($fh);
	echo $line;
}
fclose($fh);
//*/