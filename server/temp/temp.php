<?php
/* temp class */
class temp{

  
  /* load and download */
  private function load($f=null,$spd=null,$dl=false){
    if(!is_string($f)||!is_file($f)){return $this->error('Not Found',404);}
    $q=sprintf('"%s"',addcslashes(basename($f),'"\\'));
    $s=@$this->filesize($f);
    if($dl){header('Content-Description: File Transfer');}
    header('Content-Type: '.($dl?'application/octet-stream':$this->mime($f)));
    header('Content-Disposition: '.($dl?'attachment':'inline').'; filename='.$q); /* (inline|attachment) */
    header('Last-Modified: '.@gmdate('D, d M Y H:i:s',@filemtime($f)).' GMT');
    header('Content-Transfer-Encoding: binary'); /* (binary|gzip) */
    header('Connection: Keep-Alive'); /* (Keep-Alive|Close) */
    header('Cache-Control: must-revalidate, max-age=0, post-check=0, pre-check=0');
    /* (public|store|no-store|no-cache), must-revalidate, max-age=0, post-check=0, pre-check=0 */
    header('Expires: '.@gmdate('D, d M Y H:i:s',time()-(3*24*60*60)).' GMT');
    header('Pragma: no-cache'); /* (public|store|no-store|no-cache) */
    header('Accept-Ranges: bytes');
    $o=0;$t=$s;
    if(isset($_SERVER['HTTP_RANGE'])&&preg_match('/bytes=(\d+)-(\d+)?/',$_SERVER['HTTP_RANGE'],$a)){
      if($s>PHP_INT_MAX){
        /* using floatval: to prevent PHP_INT_MAX filesize on fseek */
        $o=floatval($a[1]);
        $t=isset($a[2])?floatval($a[2]):$s;
      }else{
        $o=intval($a[1]);
        $t=isset($a[2])?intval($a[2]):$s;
      }
    }
    header('Content-Range: bytes '.$o.'-'.$t.'/'.$s);
    header('HTTP/1.1 '.($o>0||$t<$s?'206 Partial Content':'200 OK'));
    header('Content-Length: '.($t-$o));
    @$this->readchunk($f,true,$o,$t,$spd);
    exit;
  }
  /* read file as chunked */
  private function readchunk($f=null,$r=true,$x=null,$y=null,$p=null,$u=true){
    if(!is_string($f)||!is_file($f)){return false;}
    $b='';$c=0;$o=fopen($f,'rb');$w=1024*(is_int($p)?$p:4);
    if($o===false){return false;}
    if(isset($x)){fseek($o,$x);}
    while(!feof($o)){
      $b=fread($o,$w);
      if($u){usleep(1000);}
      print($b);flush();
      if($r){$c+=strlen($b);}
      if(isset($y)&&ftell($o)>=$y){break;}
    }$s=fclose($o);
    if($r&&$s){return $c;}
    return $s;
  }
  /* real file size - work well for helping 32 bit os */
  private function filesize($f=null){
    if(is_dir($f)){return @\filesize($f);}
    if(!is_file($f)){return false;}
    $t=@\filesize($f);
    if(PHP_INT_SIZE===8){
      return $t;
    }elseif($t>0
      &&($i=@\fopen($f,'rb'))
      &&is_resource($i)
      &&fseek($i,0,SEEK_END)===0
      &&ftell($i)==$t
      &&fclose($i)){
      return $t;
    }elseif(strtoupper(substr(PHP_OS,0,3))==="WIN"){
      @exec('for %I in ("'.$f.'") do @echo %~zI',$o);
      return @$o[0];
    }elseif(strtoupper(substr(PHP_OS,0,5))==="LINUX"
      ||strtoupper(substr(PHP_OS,0,6))==="DARWIN"){
      @exec('stat -c%s '.$f,$o);
      return @$o[0];
    }else{
      $g=pow(1024,3)*2;
      return $t<0?$g+($g+$t):$t;
    }
  }
  /* get mime type from extension of file name */
  private function mime($f=null){
    $r='application/octet-stream';
    if(!is_string($f)){return $r;}
    if(is_dir($f)){return 'directory';}
    $t=array(
      'txt'=>'text/plain',
      'log'=>'text/plain',
      'ini'=>'text/plain',
      'html'=>'text/html',
      'css'=>'text/css',
      'php'=>'application/x-httpd-php',
      'js'=>'application/javascript',
      'json'=>'application/json',
      'xml'=>'application/xml',
      'mp4'=>'video/mp4',
      'mp3'=>'audio/mpeg',
      'wav'=>'audio/wav',
      'ogg'=>'audio/ogg',
      'png'=>'image/png',
      'jpe'=>'image/jpeg',
      'jpeg'=>'image/jpeg',
      'jpg'=>'image/jpeg',
      'gif'=>'image/gif',
      'zip'=>'application/zip',
      'rar'=>'application/x-rar-compressed',
      'pdf'=>'application/pdf',
    );
    $a=explode('.',strtolower(basename($f)));
    $e=array_pop($a);
    return array_key_exists($e,$t)?$t[$e]:$r;
  }
}
