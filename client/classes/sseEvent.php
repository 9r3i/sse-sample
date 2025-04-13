<?php
/* sseEvent
 * ~ sse event client class
 * authored by 9r3i
 * https://github.com/9r3i
 * started at september 18th 2018
 */
class sseEvent{
  public $event=null;
  public $data=null;
  public function __construct($raw=null){
    if(is_string($raw)&&preg_match('/^event:/',$raw)){
      $this->event=preg_replace('/^event:\s*/','',$raw);
    }return $this;
  }
  public function data($raw=null){
    if(!$this->event){return false;}
    if(is_string($raw)&&preg_match('/^data:/',$raw)){
      $data=preg_replace('/^data:\s*/','',$raw);
      $this->data=new sseData($data);
      return $this;
    }return false;
  }
}
