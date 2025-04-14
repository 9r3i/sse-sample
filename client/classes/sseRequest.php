<?php
/* sseRequest
 * ~ sse request client class
 * authored by 9r3i
 * https://github.com/9r3i
 * started at september 18th 2018
 */
class sseRequest{
  private $host=null;
  private $token=null;
  public function __construct($host=null,$token=null){
    if(is_string($host)&&is_string($token)&&preg_match('/^[a-z0-9]+$/i',$token)){
      $this->host=$host;
      $this->token=$token;
      return $this;
    }return false;
  }
  public function request($ext=null,$method=null,$args=[]){
    if(!is_string($ext)||!is_string($method)
      ||!is_string($this->host)||!is_string($this->token)){
      return false;
    }
    $data=['sse'=>base64_encode(@json_encode([
      'ext'=>$ext,
      'method'=>$method,
      'args'=>$args,
      'token'=>$this->token,
    ]))];
    $cookie='';
    $context=[
      'http'=>[
        'method'=>'POST',
        'header'=>"content-type:application/x-www-form-urlencoded;charset=utf-8;\r\ncookie:".$cookie,
        'content'=>@http_build_query($data,'','&') // 4th parameter: PHP_QUERY_RFC1738 for PHP >= 5.4
      ],
      'ssl'=>[
        'verify_peer'=>false,
        'verify_peer_name'=>false,
        'capture_session_meta'=>true,
        'crypto_method'=>STREAM_CRYPTO_METHOD_TLS_CLIENT,
      ]
    ];return @file_get_contents($this->host,false,@stream_context_create($context));
  }
}
