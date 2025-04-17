<?php
/* sse (client side)
 * ~ stands for server-send-event
 * ~ reponse for sse client api
 * authored by 9r3i
 * https://github.com/9r3i
 * started at serptember 18th 2018
 * require: sseClient class
 */

/* define root directory */
defined('CLIENT_ROOT') or define('CLIENT_ROOT',str_replace('\\','/',__DIR__).'/');

/* prepare auto-load */
spl_autoload_register(function($c){
  $f=CLIENT_ROOT.'classes/'.$c.'.php';
  if(!is_file($f)){
    header('content-type:text/plain');
    exit('Error: Failed to load "'.$c.'".');
  }require_once($f);
});

//header('content-type:text/plain');
new sseClient(
  'http://127.0.0.1:9302/projects/sse/server/',
  '2jmj7l5rSw0yVbvlWAYkKYBwk',
  CLIENT_ROOT.'ext',
  [
    'chat'
  ]
);


