<?php
/* sseClient
 * ~ sse client class
 * authored by 9r3i
 * https://github.com/9r3i
 * started at september 18th 2018
 */
class sseClient{
  const version='1.0.0';
  protected $host=null;
  protected $token=null;
  protected $path=null;
  protected $client=null;
  protected $exts=null;
  private $started=false;
  public function __construct($host=null,$token=null,$path=null,$exts=[]){
    if(is_string($host)&&is_string($token)&&is_string($path)
      &&preg_match('/^[a-z0-9]+$/i',$token)){
      $open=@fopen($host.'?sse='.$token,'rb');
      if(is_resource($open)){
        $path=str_replace('\\','//',$path);
        $path.=substr($path,-1)!='/'?'/':'';
        if(!is_dir($path)){@mkdir($path,0755,true);}
        $this->host=$host;
        $this->token=$token;
        $this->path=$path;
        $this->client=$open;
        $this->exts=[];
        $exts=is_array($exts)?$exts:[];
        foreach($exts as $ext){
          $path=$this->path.$ext.'.php';
          if(is_file($path)){
            require_once($path);
            if(class_exists($ext,false)&&method_exists($ext,'data')){
              $object=new $ext(new sseRequest($this->host,$this->token));
              if(is_object($object)){
                $this->exts[$ext]=$object;
              }
            }
          }
        }return $this->start();
      }
    }return false;
  }
  public function close(){
    if(!is_resource($this->client)){return false;}
    return fclose($this->client);
  }
  private function start(){
    if($this->started||!is_resource($this->client)){return false;}
    $this->started=true;
    foreach($this->event() as $event){
      if(!isset($event->event)){print_r($event);continue;}
      if(strtolower($event->event)=='open'){
        $exts=[];
        foreach($event->data as $ext){
          if(isset($this->exts[$ext])){
            $exts[$ext]=$this->exts[$ext];
          }
        }$this->exts=$exts;
      }elseif(array_key_exists($event->event,$this->exts)){
        @\call_user_func_array([$this->exts[$event->event],'data'],[$event->data]);
      }
    }return true;
  }
  private function event(){
    if(!is_resource($this->client)){yield false;}
    $event=null;
    while(true){
      if(!is_resource($this->client)){break;}
      if(feof($this->client)){sleep(1);}
      if(!is_resource($this->client)){break;}
      $get=trim(fgets($this->client));
      if($event&&preg_match('/^data:/',$get)){
        $data=$event->data($get);
        $event=null;
        if(!$data){continue;}
        yield $data;
      }elseif(preg_match('/^event:/',$get)){
        $event=new sseEvent($get);
      }
    }
  }
}
