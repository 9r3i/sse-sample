<?php
/* sseData
 * ~ sse data client class
 * authored by 9r3i
 * https://github.com/9r3i
 * started at september 18th 2018
 */
class sseData{
  public function __construct($raw=null){
    $data=@json_decode($raw);
    foreach($data as $k=>$v){
      $this->{$k}=$v;
    }
  }
}
