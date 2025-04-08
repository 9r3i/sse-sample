<?php
/* chat class
 * ~ extension for sse
 * authored by 9r3i
 * https://github.com/9r3i
 * started at september 10th 2018
 */
class chat{
  private $total=0;
  private $data=null;
  private $root=null;
  public function condition(){
    $db=$this->root();
    $get=$db->query('select count(id) as row from chat');
    $total=(int)$get[0]['row'];
    if($total!==$this->total){
      $this->total=$total;
      $this->data=$db->query('select * from chat');
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
  public function select(){
    $db=$this->root();
    $sel=$db->query('select * from chat');
    return json_encode($sel);
  }
  public function clear(){
    $db=$this->root();
    return $db->query('truncate table chat')?'OK':'Error: '.$db->error;
  }
  public function insert($u=null,$s=null){
    $u=is_string($u)?$u:'[blank]';
    $s=is_string($s)?$s:'[blank]';
    $db=$this->root();
    $ins=$db->query('insert into chat '.http_build_query([
      'user'=>'string:'.$u,
      'text'=>'string:'.$s
    ]));
    return !$db->error?$ins:'Error: '.$db->error;
  }
  private function root(){
    if($this->root){return $this->root;}
    $db=new sdb('localhost','master','luthfie','chat');
    if(!$db->error){$this->root=$db;return $db;}
    $db=new sdb('localhost','root','','root');
    $db->query('create database db=chat&user=master&pass=luthfie');
    $db=new sdb('localhost','master','luthfie','chat');
    $db->query('create table chat id=aid()&user=string()&text=string()');
    return $db;
  }
}
