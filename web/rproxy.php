<?php
// Set JSON header
JResponse::setHeader('Content-Type', 'application/json; charset=utf-8');
JResponse::sendHeaders();

$op = strtolower(JRequest::getVar('op'));
$req = array();
$rsp = array();

$req['data'] = json_decode(file_get_contents('php://input'));

// read project ini
$path_project_ini = JPATH_BASE . DS . 'conf' . DS . 'install.ini';
$project_ini = parse_ini_file($path_project_ini, true);

// read setup.cfg
$path_setup_cfg = $project_ini['package']['tmscp'] . DS . 'setup.cfg';
$path_setup_cmd = "/usr/bin/python " . $project_ini['package']['tmscp'] . DS . 'setup.py';
$ini = parse_ini_file($path_setup_cfg, true);

if ( $op == 'get' ) {
	$data = array();
	foreach($ini['http_reverse_proxy'] as $path => $url) {
		$data[] = array('path'=>$path, 'url'=>$url);
	}

	$rsp['data'] = $data;
	$rsp['total'] = count($data);

	echo json_encode($rsp);
}
else if ( $op = 'set' ) {
	$ini['http_reverse_proxy'] = array();
	foreach($req['data'] as $i => $item){
		$ini['http_reverse_proxy']["{$item->path}"] = $item->url;
	}
	file_put_contents($path_setup_cfg, create_ini_str($ini), LOCK_EX);
	sudo_exec($path_setup_cmd, $ret);
	
	if ( $ret == 0 )
		echo json_encode($ini['http_reverse_proxy']);
	else
		echo json_encode($rsp);
}

// Close the application.
JFactory::getApplication()->close();

function create_ini_str($arr)
{
	if ( !is_array($arr) ) return false;

	$str = '';
	foreach($arr as $section => $pair) {
		if ( !is_array($pair) )
			continue;
		
		$str .= "[$section]\n";
		foreach($pair as $key => $val){
			$str .= "$key=$val\n";
		}
		$str .= "\n";
	}
	
	return $str;
}

function sudo_exec($cmd, &$ret = 0)
{
	define('SUDO', '/usr/bin/sudo');
	//$new_cmd = sprintf(SUDO." sh -c '%s' 2>/dev/null", $cmd);
	$new_cmd = sprintf(SUDO." %s", $cmd);

	ob_start();
	@system($new_cmd, $ret);
	$output = ob_get_contents();
	ob_end_clean();

	return $output;
}
