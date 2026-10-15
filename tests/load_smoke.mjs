const base=process.env.AMSA_TEST_BASE_URL||'http://127.0.0.1:8099';
const total=60;
const started=performance.now();
const timings=[];
const responses=await Promise.all(Array.from({length:total},async(_,index)=>{
  const path=index%3===0?'/':index%3===1?'/user/login.php':'/api/index.php?action=csrf';
  const begin=performance.now();
  try{
    const response=await fetch(base+path,{cache:'no-store'});
    await response.arrayBuffer();
    timings.push(performance.now()-begin);
    return response.status;
  }catch{
    timings.push(performance.now()-begin);
    return 0;
  }
}));
timings.sort((a,b)=>a-b);
const failures=responses.filter(status=>status!==200);
console.log(`requests=${total} passed=${total-failures.length} failed=${failures.length} elapsed_ms=${Math.round(performance.now()-started)} p95_ms=${Math.round(timings[Math.floor(timings.length*.95)-1])}`);
if(failures.length)process.exitCode=1;
