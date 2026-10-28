import {test} from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
import os from 'node:os';
import path from 'node:path';
import {parseNote,renderNote,splitCells,same} from './markdown.mjs';
import {atomicWrite,Bridge} from './bridge.mjs';

const wid='11111111-1111-4111-8111-111111111111',tid='22222222-2222-4222-8222-222222222222';
const week={id:wid,kind:'week',version:1,deleted:false,data:{title:'Week 26–30 October 2026',starts_on:'2026-10-26',ends_on:'2026-11-01',related:'## Related Notes\n\n- [[Work|My work]]',review:'One useful reflection.'}};
const task={id:tid,kind:'task',version:1,deleted:false,data:{week_id:wid,position:0,status:'open',priority:'high',area:'Learning',
    description:'Keep **bold**, [[Note|alias]], a | pipe.\nNext: café ✅',schedule:'09:00–10:00',started_on:null,finished_on:null,planned_on:'2026-10-27',due_on:null,waiting_on:'',follow_up_on:null}};
test('table tokenizer preserves aliases and escaped pipes',()=>assert.deepEqual(splitCells('| [[Note|alias]] | one\\|two |'),['[[Note|alias]]','one\\|two']));
test('all task fields round-trip without stripping formatting',()=>{
    const text=renderNote('',week,[task]),parsed=parseNote(text);
    assert.deepEqual({...parsed.tasks[0].data,week_id:wid},task.data);
    assert.equal(parsed.tasks[0].id,tid);assert.equal(parsed.related,week.data.related);assert.equal(parsed.review,week.data.review);
    assert.equal(renderNote(text,week,[task]),text);
});
test('unmanaged notes survive replacement',()=>{
    const original=renderNote('',week,[task])+'\n## Personal context\n\nDo not remove this paragraph.\n';
    const next=renderNote(original,week,[{...task,data:{...task.data,description:'Updated'}}]);
    assert.match(next,/Do not remove this paragraph/);assert.equal(parseNote(next).tasks[0].data.description,'Updated');
});
test('malformed tables and duplicate IDs are rejected',()=>{
    assert.throws(()=>parseNote('# Missing table'));
    const text=renderNote('',week,[task,task]);assert.throws(()=>parseNote(text),/Duplicate/);
    assert.throws(()=>parseNote(renderNote('',week,[{...task,data:{...task.data,planned_on:'2026-02-31'}}])),/Invalid date/);
});
test('optimistic file replacement preserves concurrent edits and makes backups',async()=>{
    const dir=await fs.mkdtemp(path.join(os.tmpdir(),'worklog-test-'));
    try{
        const file=path.join(dir,'note.md');await fs.writeFile(file,'new editor content');
        await assert.rejects(atomicWrite(file,'website','old version',path.join(dir,'backups')),/changed/);
        assert.equal(await fs.readFile(file,'utf8'),'new editor content');
        await atomicWrite(file,'website','new editor content',path.join(dir,'backups'));
        assert.equal((await fs.readdir(path.join(dir,'backups'))).length,1);
    }finally{await fs.rm(dir,{recursive:true,force:true});}
});
test('bridge merges local changes against its server base and acknowledges only after writing',async()=>{
    const dir=await fs.mkdtemp(path.join(os.tmpdir(),'worklog-sync-')),vault=path.join(dir,'vault');
    const now=new Intl.DateTimeFormat('en-CA',{timeZone:'Africa/Johannesburg',year:'numeric',month:'2-digit',day:'2-digit'}).format(new Date());
    const w={...week,data:{...week.data,starts_on:now,ends_on:now}};
    let records=[w,structuredClone(task)],revision=2,acked=0;
    const bridge=new Bridge({url:'http://localhost',vault,currentNote:'Work/To Do.md',inboxNote:'Work/workLOG/Inbox.md',archiveDirectory:'Work/workLOG/Weeks'},'test',path.join(dir,'state'));
    bridge.request=async(action,body)=>{
        if(action==='pull')return {records:structuredClone(records),revision,conflicts:[]};
        if(action==='ack'){acked=body.revision;return {ok:true};}
        if(action==='push'){
            const index=records.findIndex(r=>r.id===body.id),old=records[index];
            const next={id:body.id,kind:body.kind,version:(old?.version||0)+1,deleted:body.deleted,data:body.data};
            if(index<0)records.push(next);else records[index]=next;revision++;return {record:structuredClone(next)};
        }
    };
    try{
        await fs.mkdir(path.join(vault,'Work'),{recursive:true});
        await fs.writeFile(path.join(vault,'Work/To Do.md'),renderNote('',w,[task]));
        await bridge.open();await bridge.initialize();await bridge.cycle();assert.equal(acked,revision);
        const file=path.join(vault,'Work/To Do.md');
        await fs.writeFile(file,(await fs.readFile(file,'utf8')).replace('Learning','Teaching'));
        await bridge.cycle();assert.equal(records.find(r=>r.id===tid).data.area,'Teaching');
        records[1].data.schedule='Afternoon';records[1].version++;revision++;
        await bridge.cycle();assert.match(await fs.readFile(file,'utf8'),/Afternoon/);
        await bridge.cycle();assert.equal(acked,revision);
    }finally{await fs.rm(dir,{recursive:true,force:true});}
});

test('initial pairing preserves additional website tasks',async()=>{
    const dir=await fs.mkdtemp(path.join(os.tmpdir(),'worklog-pair-')),vault=path.join(dir,'vault');
    const additional={...task,id:'33333333-3333-4333-8333-333333333333',data:{...task.data,position:2,description:'New website task'}};
    const bridge=new Bridge({url:'http://localhost',vault,currentNote:'Work/To Do.md',inboxNote:'Work/workLOG/Inbox.md',archiveDirectory:'Work/workLOG/Weeks'},'test',path.join(dir,'state'));
    bridge.request=async()=>({records:[week,task,additional],revision:3,conflicts:[]});
    try {
        await fs.mkdir(path.join(vault,'Work'),{recursive:true});
        await fs.writeFile(path.join(vault,'Work/To Do.md'),renderNote('',week,[task]));
        await bridge.open();await bridge.initialize();
        assert.equal(parseNote(await fs.readFile(path.join(vault,'Work/To Do.md'),'utf8')).tasks.length,2);
    }finally{await fs.rm(dir,{recursive:true,force:true});}
});
test('interrupted note write recovers baseline before next synchronization',async()=>{
    const dir=await fs.mkdtemp(path.join(os.tmpdir(),'worklog-recover-')),vault=path.join(dir,'vault');
    const bridge=new Bridge({url:'http://localhost',vault,currentNote:'Work/To Do.md',inboxNote:'Work/workLOG/Inbox.md',archiveDirectory:'Work/workLOG/Weeks'},'test',path.join(dir,'state'));
    try {
        await bridge.open();await fs.mkdir(path.join(vault,'Work'),{recursive:true});
        const expected=renderNote('',week,[task]),newTask={...task,version:2,data:{...task.data,area:'Recovered'}},text=renderNote(expected,week,[newTask]);
        await fs.writeFile(path.join(vault,'Work/To Do.md'),expected);
        await fs.writeFile(path.join(dir,'state/write-journal.json'),JSON.stringify({records:[week,newTask],targets:[{relative:'Work/To Do.md',week,text,expected}],cleanup:[]}));
        await bridge.recover();
        assert.equal(bridge.state.base[tid].version,2);
        assert.equal(bridge.state.local[tid].data.area,'Recovered');
        assert.equal(await fs.readFile(path.join(vault,'Work/To Do.md'),'utf8'),text);
    }finally{await fs.rm(dir,{recursive:true,force:true});}
});
test('recovery refuses to overwrite an external edit after a crash',async()=>{
    const dir=await fs.mkdtemp(path.join(os.tmpdir(),'worklog-conflict-')),vault=path.join(dir,'vault');
    const bridge=new Bridge({url:'http://localhost',vault,currentNote:'Work/To Do.md',inboxNote:'Work/workLOG/Inbox.md',archiveDirectory:'Work/workLOG/Weeks'},'test',path.join(dir,'state'));
    try {
        await bridge.open();await fs.mkdir(path.join(vault,'Work'),{recursive:true});
        await fs.writeFile(path.join(vault,'Work/To Do.md'),'External edit after crash');
        await fs.writeFile(path.join(dir,'state/write-journal.json'),JSON.stringify({targets:[{relative:'Work/To Do.md',week,text:'Remote proposal',expected:'Before crash'}]}));
        await assert.rejects(bridge.recover(),/overlaps a new note edit/);
        assert.equal(await fs.readFile(path.join(vault,'Work/To Do.md'),'utf8'),'External edit after crash');
    }finally{await fs.rm(dir,{recursive:true,force:true});}
});
