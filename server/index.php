<?php
/* sse (server side)
 * ~ stands for server-send-event
 * ~ reponse for sse api
 * authored by 9r3i
 * https://github.com/9r3i
 * started at serptember 10th 2018
 * require: sse class
 */

/* define root directory */
defined('SERVER_ROOT') or define('SERVER_ROOT',str_replace('\\','/',__DIR__).'/');

/* prepare auto-load */
spl_autoload_register(function($c){
  $f=SERVER_ROOT.'classes/'.$c.'.php';
  if(!is_file($f)){
    header('content-type:text/plain');
    exit('Error: Failed to load "'.$c.'".');
  }require_once($f);
});

/* set sse cli directory */
defined('SSE_CLI_DIR') or define('SSE_CLI_DIR',SERVER_ROOT);

/* set sdb cli directory */
defined('SDB_CLI_DIR') or define('SDB_CLI_DIR',SERVER_ROOT.'sdb/');

/* set extension directory */
$sse_extension_dir='ext';

/* prepare extensions */
$sse_extensions=sseExtensions(SSE_CLI_DIR.'/'.$sse_extension_dir);

/* ----- testing script ----- *
header('content-type:text/plain');var_dump($sse_extensions);exit;
//*/

/* start sse api */
new sse(
  /* extensions */
  $sse_extensions,
  /* extension directory */
  $sse_extension_dir,
  /* default token */
  '2jmj7l5rSw0yVbvlWAYkKYBwk',
  /* server timezone */
  'Asia/Jakarta'
);

/* get sse extensions */
function sseExtensions($d=null){
  if(!is_dir($d)){return $d;}
  $d=str_replace('\\','/',$d);
  $d.=substr($d,-1)!='/'?'/':'';
  $s=@scandir($d);$r=[];
  if(!is_array($s)){return false;}
  foreach($s as $f){
    if(!is_file($d.$f)
      ||!preg_match('/^[a-z][a-z0-9]+\.php$/i',$f)){
      continue;
    }$r[]=preg_replace('/\.php$/','',$f);
  }return $r;
}


