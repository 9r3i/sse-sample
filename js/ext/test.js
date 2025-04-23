/* test.js */
var SSE;
function test(data){
  console.log('test',data.date,data.total,data.data);
}
function testInsert(data){
  SSE.request('test','insert',[data],function(r){
    console.log('success',r);
  },function(e){
    console.log('error',e);
  });
}


