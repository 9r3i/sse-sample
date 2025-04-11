<?php
/* test class
 * ~ extension for sse
 * authored by 9r3i
 * https://github.com/9r3i
 * started at september 10th 2018
 */
class test{
  private $total=0;
  private $data=null;
  private $root=null;
  public function condition(){
    $db=$this->root?$this->root:$this->root();
    $this->root=$db;
    $get=$db->query('select count(id) as row from test');
    $total=(int)$get[0]['row'];
    if($total>$this->total){
      $this->total=$total;
      $this->data=$db->query('select * from test');
      return true;
    }return false;
  }
  public function result(){
    return [
      'date'=>date(DATE_ISO8601),
      'total'=>$this->total,
      'data'=>$this->data,
    ];
  }
  public function insert($s=null){
    $s=is_string($s)?$s:'[blank]';
    $db=$this->root();
    $ins=$db->query('insert into test date=string('.date('Y-m-d H:i:s').')&content=string:'.$s);
    return !$db->error?$ins:'Error: '.$db->error;
  }
  private function root(){
    $db=new sdb('localhost','test','test','test');
    if(!$db->error){return $db;}
    $db=new sdb('localhost','root','','root');
    $db->query('create database db=test&user=test&pass=test');
    $db=new sdb('localhost','test','test','test');
    $db->query('create table test id=aid()&date=string()&content=string()');
    return $db;
  }
}
