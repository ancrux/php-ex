<?php
$base = dirname(__FILE__);
$path = $base . $_SERVER['PATH_INFO'];

// check allowed content-type
$path_ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$ctypes = array();
$ctypes['js'] = 'text/javascript';
$ctypes['css'] = 'text/css';
$ctypes['html'] = 'text/html';
$ctypes['txt'] = 'text/plain';
if ( array_key_exists($path_ext, $ctypes) )
{
	header("Content-type: {$ctypes[$path_ext]}", true);
}
else
{
	//!important: avoid to output source code (e.g. php here)
	header("HTTP/1.1 403 Forbidden"); 
	exit;
}

// check file availability
$fp = @fopen($path, 'rb');
if ( !$fp )
{
	header("HTTP/1.1 404 Not Found");
	exit;
}

// check last-modified
$last_modified_time = filemtime($path); 
$etag = md5_file($path);
// always send headers
header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified_time)." GMT"); 
header("Etag: $etag");
// exit if not modified
if ( @strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time || 
	@trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag)
{ 
	header("HTTP/1.1 304 Not Modified");
	@fclose($fp);
	exit; 
}

/*
// header cache-control
if ( strrpos($_SERVER['REQUEST_URI'], '?') === false )
{
	$seconds_to_cache = 86400 * 7;
	$expire_time = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
	header("Expires: {$expire_time}");
	header("Pragma: cache");
	header("Cache-Control: max-age={$seconds_to_cache}");
}
//*/

// check accept-encoding has gzip
if ( substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') ) ob_start("ob_gzhandler");

// output file content
while ( !feof($fp) )
{
	echo fread($fp, 32768);
}
fclose($fp);
