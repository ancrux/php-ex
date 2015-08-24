#!/usr/bin/php
<?php
if ( $argc < 3 ) 
	die("usage: {$argv[0]} [file1] [file2]\n");

$file1 = $argv[1];
$file2 = $argv[2];

$ini1 = sort_ini_file($file1);
$ini2 = sort_ini_file($file2);

$n_changed = 0;
$n_added = 0;
$n_removed = 0;
foreach($ini1 as $sec_key => $val)
{
	if ( isset($ini2[$sec_key]) )
	{
		if ( $ini1[$sec_key] != $ini2[$sec_key] )
		{
			echo "C:{$sec_key} = {$ini1[$sec_key]} => {$ini2[$sec_key]}\n"; $n_changed++;
		}
		
		unset($ini2[$sec_key]);
	}
	else
	{
		echo "-:{$sec_key} = {$ini1[$sec_key]}\n"; $n_removed++;
	}
}

foreach($ini2 as $sec_key => $val)
{
	echo "+:{$sec_key} = {$ini2[$sec_key]}\n"; $n_added++;
}

echo "ini key (added/changed/removed) = ({$n_added}/{$n_changed}/{$n_removed})\n";

function sort_ini_file($filename)
{
	$arr = array();
	
	$lines = @file($filename);
	$sec = '';
	foreach($lines as $n => $line)
	{
		$line = trim($line);
		$len = strlen($line);
		if ( $len > 2 && $line[0] == '[' && $line[$len-1] == ']' )
		{
			$sec = substr($line, 1, $len-2);
		}
		else
		{
			$split = explode('=', $line, 2);
			if ( count($split) == 2 )
			{
				$key = trim($split[0]);
				$val = trim($split[1]);
				
				$arr["{$sec}\t{$key}"] = $val;
			}
		}
	}
	
	return $arr;
}