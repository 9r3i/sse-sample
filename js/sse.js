/* sse.js, ~ server-send-event client, authored by 9r3i, https://github.com/9r3i, started at september 10th 2018 */

/* connect to sse server
 , url   = string of sse server
 , token = string of sse token
 , path  = string of client extension path directory
 , err   = function of error callback */
function sse(url,token,path,err){
  if(typeof url!=='string'
    ||typeof path!=='string'
    ||typeof token!=='string'
    ||!token.match(/^[a-z0-9]+$/ig)){
    return false;
  }var raw=false;
  path=path.replace(/\\/ig,'/');
  path+=!path.match(/\/$/ig)?'/':'';
  this.path=path;
  this.url=url;
  this.uri=url+'?sse='+token;
  var opt={withCredentials:true};
  try{raw=new EventSource(this.uri,opt);}catch(e){return false;}
  if(!raw||typeof raw.close!=='function'){return false;}
  this.token=token;
  this.raw=raw;
  raw.onopen=function(e){
    var data=false;
    try{data=JSON.parse(e.data);}catch(e){}
    if(!Array.isArray(data)){return false;}
    for(var i=0;i<data.length;i++){
      var ext=data[i];
      load_script(path+ext+'.js');
      raw.addEventListener(ext,function(r){
        if(!window[ext]
          ||typeof window[ext]!=='function'){
          return false;
        }var dt=false;
        try{dt=JSON.parse(r.data);}catch(e){}
        window[ext].apply(ext,[dt]);
      },false);
    }return true;
  };
  raw.onerror=function(e){
    return typeof err==='function'
      ?err('Error: Failed to connect.'):false;
  };
  this.__proto__.close=function(){
    this.raw.close();
  };
  this.__proto__.request=function(ext,method,args,cb,er){
    cb=typeof cb==='function'?cb:function(){};
    er=typeof er==='function'?er:function(){};
    if(typeof window.post!=='function'){
      return er('Error: Require header.js to request.');
    }var data={ext:ext,method:method,args:args,token:this.token};
    window.post(this.url,function(r){
      if(r.toString().match(/^error/ig)){return er(r);}
      return cb(r);
    },{sse:btoa(JSON.stringify(data))},false,null,null,null,function(e){
      return er(e);
    });
  };
};


