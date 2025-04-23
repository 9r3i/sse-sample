/* chat.js */
var SSE,
CHAT_AUDIO=new Audio('files/receive.mp3'),
CHAT_USER=null,
CHAT_CLOSED=false;
/* start initial chat */
chatInit();
/* update chat */
function chat(data){
  var el=gebi('chat-content');
  if(!el){console.log('Error: Failed to get element content.');return false;}
  return chatContent(data.data,true);
}
/* restart chat after closed */
function chatRestart(){
  SSE.close();
  /* start sse connection */
  SSE=new sse(SSE_SERVER,SSE_TOKEN,SSE_EXT_PATH,function(e){
    console.log('error',e);
  });
  CHAT_CLOSED=false;
  return chatInit();
}
/* clear data */
function chatSelect(){
  SSE.request('chat','select',[],function(r){
    return chatContent(r);
  },function(e){
    console.log('error',e);
  });
}
/* parse chat content
   r = object of data chats
   sound = bool of audio play
 */
function chatContent(r,sound){
  var el=gebi('chat-content');
  if(!el){return false;}
  var count=0;
  for(var i=0;i<r.length;i++){
    if(qs('div[data-id="'+r[i].id+'"]')){continue;}
    var iam=CHAT_USER==r[i].user?'#7b3':'#37b';
    var each=ce('div');
    each.style.margin='5px 0px';
    each.dataset.id=r[i].id;
    each.innerHTML='<span style="color:'+iam+';">'+r[i].user+'</span>: '+r[i].text;
    el.appendChild(each);
    count++;
  }if(count&&sound){CHAT_AUDIO.play();}
  return true;
}
/* close chat */
function chatClose(){
  SSE.close();
  var html=''
    +'<div id="chat-input" style="padding:5px;margin:5px 0px;">'
    +'<input type="submit" id="chat-restart" value="Restart" '
      +'style="width:calc(33% - 12px);padding:5px;border:1px solid #ccc;'
      +'font-family:consolas;monospace;font-size:13px;margin:0px;'
      +'background-color:#fff;" />'
    +'</div>';
  var index=gebi('index');
  if(index){index.innerHTML=html;}
  CHAT_CLOSED=true;
  var restart=gebi('chat-restart');
  restart.onclick=chatRestart;
}
/* clear data */
function chatClear(){
  SSE.request('chat','clear',[],function(r){
    var el=gebi('chat-content');
    if(el){el.innerHTML='';}
  },function(e){
    console.log('error',e);
  });
}
/* insert data
   text = string of chat message
 */
function chatInsert(text){
  if(!CHAT_USER||!text||text.trim()==''){return false;}
  SSE.request('chat','insert',[CHAT_USER,text.trim()],function(r){
    console.log('success',r);
  },function(e){
    console.log('error',e);
  });
}
/* initialize */
function chatInit(){
  CHAT_USER=getCookie('chat-user');
  if(!CHAT_USER){
    CHAT_USER='[GUEST]';
    setCookie('chat-user',CHAT_USER);
  }
  var index=gebi('index');
  if(!index){return false;}
  var html=''
    +'<div id="chat-content" '
      +'style="border:1px solid #ccc;padding:10px;margin:10px 5px;'
      +'font-family:consolas;monospace;font-size:13px;">'
    +'</div>'
    +'<div id="chat-input" style="padding:5px;margin:5px 0px;">'
    +'<input type="text" id="chat-text" '
      +'style="width:calc(66% - 12px);padding:5px;border:1px solid #ccc;'
      +'font-family:consolas;monospace;font-size:13px;" />'
    +'<input type="submit" id="chat-send" value="Send" '
      +'style="width:calc(33% - 12px);padding:5px;border:1px solid #ccc;'
      +'font-family:consolas;monospace;font-size:13px;margin:0px 0px 0px 10px;'
      +'background-color:#fff;" />'
    +'</div>'
    +'<div id="chat-input" style="padding:5px;margin:5px 0px;">'
    +'<input type="submit" id="chat-close" value="Close" '
      +'style="width:calc(33% - 12px);padding:5px;border:1px solid #ccc;'
      +'font-family:consolas;monospace;font-size:13px;margin:0px;'
      +'background-color:#fff;" />'
    +'<input type="submit" id="chat-clear" value="Clear" '
      +'style="width:calc(33% - 12px);padding:5px;border:1px solid #ccc;'
      +'font-family:consolas;monospace;font-size:13px;margin:0px 0px 0px 10px;'
      +'background-color:#fff;" />'
    +'<input type="submit" id="chat-user" value="User: '
      +CHAT_USER+'" '
      +'style="width:calc(33% - 12px);padding:5px;border:1px solid #ccc;'
      +'font-family:consolas;monospace;font-size:13px;margin:0px 0px 0px 10px;'
      +'background-color:#fff;" />'
    +'<input type="submit" id="chat-reload" value="Reload" '
      +'style="width:calc(33% - 12px);padding:5px;border:1px solid #ccc;'
      +'font-family:consolas;monospace;font-size:13px;margin:5px 0px 0px;'
      +'background-color:#fff;" />'
    +'<input type="submit" id="chat-restart" value="Restart" '
      +'style="width:calc(33% - 12px);padding:5px;border:1px solid #ccc;'
      +'font-family:consolas;monospace;font-size:13px;margin:5px 0px 0px 10px;'
      +'background-color:#fff;" />'
    +'</div>';
  index.innerHTML=html;
  chatSelect();
  var text=gebi('chat-text');
  text.focus();
  text.onkeyup=function(e){
    if(e.keyCode!==13){return false;}
    var val=this.value;
    this.value='';
    return chatInsert(val);
  };
  var clear=gebi('chat-clear');
  clear.onclick=chatClear;
  var close=gebi('chat-close');
  close.onclick=chatClose;
  var send=gebi('chat-send');
  send.onclick=function(e){
    var val=text.value;
    text.value='';
    return chatInsert(val);
  };
  var user=gebi('chat-user');
  user.onclick=function(e){
    let oname=CHAT_USER=='[GUEST]'?'':CHAT_USER,
    name=prompt('Insert username:',oname);
    if(!name){return false;}
    CHAT_USER=name;
    setCookie('chat-user',name);
    return chatRestart();
  };
  var reload=gebi('chat-reload');
  reload.onclick=function(){
    chatClose();
    window.location.reload();
  };
  var restart=gebi('chat-restart');
  restart.onclick=chatRestart;
}


