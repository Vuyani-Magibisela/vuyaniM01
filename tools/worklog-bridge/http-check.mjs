// Explicit local integration check; does not run as part of the offline unit suite.
import fs from 'node:fs/promises';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
import {execFileSync} from 'node:child_process';
import {randomUUID} from 'node:crypto';
import assert from 'node:assert/strict';
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'../..');
if(!process.argv.includes('--local'))throw new Error('Use --local to test the development server.');
const base='http://localhost:8090',ids=[];
let checks=0;
const check=(value,message)=>{assert.ok(value,message);checks++;};
async function session(id,role,expired=false){
    execFileSync('docker',['compose','exec','-T','-e','WORKLOG_TEST_FIXTURE=1','app','php','tools/worklog-browser-fixture.php',String(id),role,...(expired?['expired']:[])],{cwd:root,stdio:'pipe'});
    return JSON.parse(await fs.readFile(path.join(root,'portfolio-website/logs/worklog-test-session.json'),'utf8'));
}
async function request(route,session,body,method=body?'POST':'GET',headers={}){
    return fetch(base+route,{method,redirect:'manual',headers:{...(session?{Cookie:'PHPSESSID='+session.id}:{}),
        ...(body?{'Content-Type':'application/json','X-CSRF-Token':session?.csrf||''}:{}),...headers},body:body?JSON.stringify(body):undefined});
}
const owner=await session(1,'admin');
check((await request('/todo/state')).status===401,'anonymous state');
check((await request('/todo/state',await session(2,'admin'))).status===403,'other admin');
check((await request('/todo/state',await session(1,'user'))).status===403,'ordinary user');
check((await request('/todo/state',await session(1,'admin',true))).status===401,'expired session');
check((await request('/admin/todo')).status===302,'anonymous page redirects');
check((await request('/todo/state',owner)).status===200,'owner state');
check((await request('/todo/tasks',owner,{description:'x'},'POST',{'X-CSRF-Token':''})).status===403,'missing CSRF');
check((await request('/todo/tasks',owner,{description:'x'},'POST',{'X-CSRF-Token':'invalid'})).status===403,'invalid CSRF');
let record;
try{
    const id=randomUUID();ids.push(id);
    const operation={id,operation_id:randomUUID(),kind:'task',version:0,data:{description:'[worklog-test] API verification',area:'Test'}};
    const result=await request('/todo/tasks',owner,operation);check(result.status===200,'create');
    record=(await result.json()).record;
    const retry=await request('/todo/tasks',owner,operation);check((await retry.json()).record.version===1,'retry idempotency');
    const edited=await request('/todo/tasks/'+id,owner,{...record,data:{...record.data,area:'Updated'},operation_id:randomUUID()},'PATCH');
    const latest=(await edited.json()).record;check(latest.version===2,'version increment');
    check((await request('/todo/tasks/'+id,owner,{...record,operation_id:randomUUID()},'PATCH')).status===409,'stale edit');
    const pair=await request('/todo/devices',owner,{name:'[worklog-test] temporary device'});
    const device=await pair.json();
    const pull=await request('/todosync/pull',null,null,'GET',{Authorization:'Bearer '+device.token});
    check(pull.status===200,'device auth with Authorization header');
    check((await request('/todosync/pull',owner)).status===401,'browser session is not device auth');
    await request('/todo/devices/'+device.id,owner,{},'DELETE');
    check((await request('/todosync/pull',null,null,'GET',{Authorization:'Bearer '+device.token})).status===401,'revoked device');
    const removed=await request('/todo/tasks/'+id,owner,{...latest,operation_id:randomUUID()},'DELETE');
    check((await removed.json()).record.deleted,'soft delete');
    const snapshot=await (await request('/todo/state',owner)).json();
    check(snapshot.records.some(r=>r.id===id&&r.deleted),'deletion retained in snapshot');
}finally{
    await fs.writeFile(path.join(root,'portfolio-website/logs/worklog-test-ids.json'),JSON.stringify(ids));
}
console.log(checks+' HTTP integration assertions passed.');
