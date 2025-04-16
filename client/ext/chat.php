<?php
/* chat class
 * ~ sse client extension
 * ~ reponse for sse client api
 * authored by 9r3i
 * https://github.com/9r3i
 * started at serptember 18th 2018
 */
class chat{
  private $request=null;
  private $started=false;
  public function __construct($request=null){
    $this->request=$request;
  }
  public function data($data){
    if(isset($data->data)){
      $last=count($data->data)-1;
      if(isset($data->data[$last])){
        print_r($data->data[$last]);
        if($this->started){
          sleep(1);
          $send=$this->request->request('chat','insert',['mechine','aku juga']);
          $this->started=false;
        }else{$this->started=true;}
      }else{
        print_r($data->data);
      }
    }else{
      print_r($data);
    }
    echo "\r\n-------------------------------\r\n";
  }
}
