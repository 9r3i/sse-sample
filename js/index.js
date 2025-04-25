/* index.js
 * ~ server-send-event client index.js
 * authored by 9r3i
 * https://github.com/9r3i
 * started at september 10th 2018
 * require: header.js v1.5.1 or higher
 */
var W,D,SSE=null,
SSE_SERVER='http://127.0.0.1:9302/projects/sse/server/',
//SSE_SERVER='http://192.168.43.42/sse/server/',
SSE_TOKEN='2jmj7l5rSw0yVbvlWAYkKYBwk',
SSE_EXT_PATH='js/ext/';

/* start sse connection */
SSE=new sse(SSE_SERVER,SSE_TOKEN,SSE_EXT_PATH,function(e){
  console.log('error',e);
  var index=gebi('index');
  if(index){index.innerText=e;}
});

/* window events */
//WINDOW_EVENTS.execAll();
WINDOW_EVENTS.onkeyup.push(function(e){
  if(e.keyCode==116){
    SSE.close();
    console.log(e.keyCode);
  }
  e.preventDefault&&e.preventDefault();
  e.stopPropagation&&e.stopPropagation();
  e.cancelBubble=true;
  e.returnValue=false;
  return false;
});


