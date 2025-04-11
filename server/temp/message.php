<?php
/* message class
 * ~ extension for sse
 * authored by 9r3i
 * https://github.com/9r3i
 * started at september 10th 2018
 */
class message{
  private $sent=false;
  public function condition(){
    return $this->sent?false:true;
  }
  public function result(){
    global $sse_extensions;
    $key=array_search(__CLASS__,$sse_extensions);
    if($key!==false){unset($sse_extensions[$key]);}
    $this->sent=true;
    return [
      'date'=>date(DATE_ISO8601),
      'extensions'=>array_values($sse_extensions),
    ];
  }
}
