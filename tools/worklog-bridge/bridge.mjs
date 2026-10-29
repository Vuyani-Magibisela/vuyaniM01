import fs from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { spawnSync } from 'node:child_process';
import { randomUUID } from 'node:crypto';
import { parseNote, renderNote, operation, same, hash } from './markdown.mjs';

const read = async file => {try{return await fs.readFile(file,'utf8');}catch(e){if(e.code==='ENOENT')return null;throw e;}};
export async function atomicWrite(file, text, expected, backupDirectory) {
    const old=await read(file);
    if(old!==expected)throw new Error('Note changed during synchronization. Retrying without overwriting: '+path.basename(file));
    await fs.mkdir(path.dirname(file),{recursive:true});
    if(old!==null&&backupDirectory) {
        await fs.mkdir(backupDirectory,{recursive:true});
        await fs.writeFile(path.join(backupDirectory,Date.now()+'-'+randomUUID()+'.md'),old,{flag:'wx'});
    }
    const temp=file+'.worklog-'+randomUUID()+'.tmp';
    try{
        await fs.writeFile(temp,text,{flag:'wx'});
        if(await read(file)!==expected)throw new Error('Note changed before replacement. Retry required.');
        await fs.rename(temp,file);
    }finally{await fs.rm(temp,{force:true});}
}
export class Bridge {
    constructor(config, token, stateDirectory) {
        this.config=config;this.token=token;this.dir=stateDirectory;
        this.stateFile=path.join(stateDirectory,'state.json');
        this.state={base:{},local:{},files:{},pending:[],initialized:false};
        const url=new URL(config.url);
        if(url.protocol!=='https:'&&!(['localhost','127.0.0.1'].includes(url.hostname)&&url.protocol==='http:'))throw new Error('HTTPS is required except for localhost.');
        this.url=url.toString().replace(/\/$/,'');
        this.vault=path.resolve(config.vault);
    }
    file(relative) {
        const full=path.resolve(this.vault,relative);
        if(!full.startsWith(this.vault+path.sep))throw new Error('Note path is outside the configured vault.');
        return full;
    }
    async request(action, body) {
        const response=await fetch(this.url+'/todosync/'+action,{
            method:body?'POST':'GET',headers:{Authorization:'Bearer '+this.token,...(body?{'Content-Type':'application/json'}:{})},
            body:body?JSON.stringify(body):undefined,signal:AbortSignal.timeout(30000),redirect:'error'
        });
        const data=await response.json();
        if(!response.ok)throw new Error(data.error||'Server returned '+response.status);
        return data;
    }
    async save() {
        const existing=await read(this.stateFile);
        await atomicWrite(this.stateFile,JSON.stringify(this.state,null,2),existing);
    }
    async open() {
        await fs.mkdir(this.dir,{recursive:true});
        const stored=await read(this.stateFile);if(stored)this.state=JSON.parse(stored);
    }
    async initialize() {
        const snapshot=await this.request('pull');
        if(snapshot.conflicts.length)throw new Error('Resolve website conflicts before initializing.');
        const original=await read(this.file(this.config.currentNote));
        if(original===null)throw new Error('The current note is missing.');
        const parsed=parseNote(original);
        const week=snapshot.records.find(r=>r.kind==='week'&&!r.deleted&&(r.id===parsed.id||r.data.title===parsed.title));
        if(!week)throw new Error('Import the original note through workLOG before pairing this bridge.');
        const entries=snapshot.records.filter(r=>r.kind==='task'&&!r.deleted&&r.data.week_id===week.id).sort((a,b)=>a.data.position-b.data.position);
        const matched=new Set();
        parsed.tasks.forEach((row,i)=>{
            // Compare only what a note can express; time tracking is website-only.
            const withoutPosition = data => {const copy={...data};for(const key of ['position','week_id','time_log','timer_started_at'])delete copy[key];return copy;};
            const candidate=row.id?entries.find(r=>r.id===row.id):entries.find(r=>!matched.has(r.id)&&same(withoutPosition(row.data),withoutPosition(r.data)));
            if(!candidate||!same(withoutPosition(row.data),withoutPosition(candidate.data)))throw new Error('Initial note differs from website at row '+(i+1)+'. Nothing was overwritten.');
            matched.add(candidate.id);
        });
        for(const record of snapshot.records)this.state.base[record.id]=record;
        this.state.files[this.config.currentNote]={id:week.id};
        this.state.initialized=true;
        // Upgrade only after validating every original field.
        await atomicWrite(this.file(this.config.currentNote),renderNote(original,week,snapshot.records),original,path.join(this.dir,'backups'));
        await this.captureLocal(this.config.currentNote);
        await this.save();
    }
    async captureLocal(relative) {
        const text=await read(this.file(relative));if(text===null)return;
        const note=parseNote(text);const weekId=this.state.files[relative]?.id;
        if(weekId!=='inbox'){
            const data={title:note.title,starts_on:note.starts_on,ends_on:note.ends_on,related:note.related,review:note.review};
            this.state.local[weekId]={id:weekId,kind:'week',deleted:false,data};
        }
        for(const row of note.tasks)if(row.id)this.state.local[row.id]={id:row.id,kind:'task',deleted:false,data:{...row.data,week_id:weekId==='inbox'?null:weekId}};
    }
    async cycle() {
        if(!this.state.initialized)throw new Error('Run --initialize before starting synchronization.');
        // Persisted requests are replayed byte-for-byte after a network interruption.
        await this.drain();
        let snapshot=await this.request('pull');
        if(snapshot.conflicts.length){this.state.waiting=true;await this.save();return {conflicts:snapshot.conflicts.length};}
        if(this.state.waiting){
            this.state.base=Object.fromEntries(snapshot.records.map(r=>[r.id,r]));
            this.state.waiting=false;await this.save();
        }
        const reads=new Map(),observed={},ids=new Set();
        for(const [relative,info] of Object.entries(this.state.files)){
            const text=await read(this.file(relative));
            if(text===null)throw new Error('Managed note missing: '+relative+'. Restore it; deleting a file does not delete its tasks.');
            const parsed=parseNote(text);reads.set(relative,text);
            if(info.id!=='inbox'&&parsed.id!==info.id)throw new Error('Week metadata changed or missing: '+relative);
            if(info.id!=='inbox')observed[info.id]={id:info.id,kind:'week',deleted:false,data:{
                title:parsed.title,starts_on:parsed.starts_on,ends_on:parsed.ends_on,related:parsed.related,review:parsed.review
            }};
            for(const row of parsed.tasks){
                // Stable pending IDs survive retries even before they are written into the note.
                const id=row.id||this.state.newIds?.[relative+':'+row.data.position+':'+hash(row.data.description)]||randomUUID();
                if(!row.id){this.state.newIds??={};this.state.newIds[relative+':'+row.data.position+':'+hash(row.data.description)]=id;}
                if(ids.has(id))throw new Error('A task ID occurs in more than one note.');
                ids.add(id);
                observed[id]={id,kind:'task',deleted:false,data:{...row.data,week_id:info.id==='inbox'?null:info.id}};
            }
        }
        for(const [id,local] of Object.entries(this.state.local)){
            if(local.kind==='task'&&!local.deleted&&!observed[id])observed[id]={...local,deleted:true};
        }
        for(const [id,local] of Object.entries(observed)){
            const previous=this.state.local[id],base=this.state.base[id];
            if(previous&&same(local,previous))continue;
            if(!base&&snapshot.records.some(r=>r.id===id))throw new Error('Unknown existing task ID; reinitialize after reconciling the note.');
            let data=local.data;
            if(base&&previous){
                data={...base.data};
                for(const key of Object.keys(local.data))if(!same(local.data[key],previous.data[key]))data[key]=local.data[key];
            }
            const record={...local,data};
            this.state.pending.push({operation:operation(record,base||null),local});
        }
        await this.save();
        await this.drain();
        snapshot=await this.request('pull');
        if(snapshot.conflicts.length){this.state.waiting=true;await this.save();return {conflicts:snapshot.conflicts.length};}
        // Check every read before touching any file. Individual replacements recheck too.
        for(const [relative,text] of reads)if(await read(this.file(relative))!==text)throw new Error('A note changed while syncing. Retrying.');
        await this.materialize(snapshot,reads);
        this.state.base=Object.fromEntries(snapshot.records.map(r=>[r.id,r]));
        this.state.local={};
        for(const relative of Object.keys(this.state.files))await this.captureLocal(relative);
        this.state.newIds={};
        this.state.revision=snapshot.revision;
        await this.save();
        await this.request('ack',{revision:snapshot.revision});
        return {revision:snapshot.revision};
    }
    async drain() {
        while(this.state.pending.length){
            const item=this.state.pending[0],result=await this.request('push',item.operation);
            if(result.record)this.state.base[result.record.id]=result.record;
            this.state.local[item.local.id]=item.local;
            this.state.pending.shift();await this.save();
        }
    }
    async materialize(snapshot, reads) {
        const date=new Intl.DateTimeFormat('en-CA',{timeZone:'Africa/Johannesburg',year:'numeric',month:'2-digit',day:'2-digit'}).format(new Date());
        const weeks=snapshot.records.filter(r=>r.kind==='week'&&!r.deleted);
        const current=weeks.find(w=>w.data.starts_on<=date&&w.data.ends_on>=date);
        if(!current)throw new Error('Open workLOG to create the current week, then sync again.');
        const inbox={id:'inbox',kind:'week',data:{title:'Inbox',starts_on:date,ends_on:date,related:'## Related Notes\n\n- [[To Do]]',review:''}};
        const targets=new Map();
        for(const week of [...weeks,inbox]){
            const target=week.id==='inbox'?this.config.inboxNote:week.id===current.id?this.config.currentNote:
                this.config.archiveDirectory+'/'+week.data.starts_on+'.md';
            const source=Object.keys(this.state.files).find(file=>this.state.files[file].id===week.id);
            const sourceText=source?(reads.get(source)??await read(this.file(source))):null;
            const targetText=reads.has(target)?reads.get(target):await read(this.file(target));
            if(targetText!==null&&!this.state.files[target])throw new Error('Unmanaged note already exists at '+target+'. Move or import it before syncing.');
            targets.set(target,{week,text:renderNote(sourceText,week,snapshot.records),expected:targetText});
        }
        // Archive the old current note before replacing it with the new current week.
        const sorted=[...targets].sort(([a],[b])=>(a===this.config.currentNote)-(b===this.config.currentNote));
        const journal={records:snapshot.records,targets:sorted.map(([relative,item])=>({relative,...item})),cleanup:Object.keys(this.state.files).filter(relative=>!targets.has(relative)).map(relative=>({relative,expected:reads.get(relative)}))};
        await atomicWrite(path.join(this.dir,'write-journal.json'),JSON.stringify(journal),await read(path.join(this.dir,'write-journal.json')));
        for(const [relative,item] of sorted){
            if(item.text!==item.expected)await atomicWrite(this.file(relative),item.text,item.expected,path.join(this.dir,'backups'));
            this.state.files[relative]={id:item.week.id};
            await this.save();
        }
        // Old archive files can remain as unmanaged duplicates after their week becomes current.
        // Retain them as backups outside the vault rather than leaving duplicate task IDs.
        for(const relative of Object.keys(this.state.files)){
            if(targets.has(relative))continue;
            const old=await read(this.file(relative));
            if(old!==null){
                const expected=reads.get(relative);
                if(old!==expected)throw new Error('Old archive changed during rollover.');
                await fs.mkdir(path.join(this.dir,'backups'),{recursive:true});
                await fs.writeFile(path.join(this.dir,'backups',Date.now()+'-'+randomUUID()+'.md'),old,{flag:'wx'});
                await fs.unlink(this.file(relative));
            }
            delete this.state.files[relative];
        }
        await fs.rm(path.join(this.dir,'write-journal.json'),{force:true});
    }
    async recover() {
        const file=path.join(this.dir,'write-journal.json'),raw=await read(file);if(!raw)return;
        const journal=JSON.parse(raw);
        for(const item of journal.targets){
            const current=await read(this.file(item.relative));
            if(current!==item.text&&current!==item.expected)throw new Error('Interrupted sync overlaps a new note edit. Backups and write-journal.json preserve both versions; reconcile before restarting.');
        }
        for(const item of (journal.cleanup||[])){
            const current=await read(this.file(item.relative));
            if(current!==null&&current!==item.expected)throw new Error('Archived note changed during recovery. Reconcile the backup first.');
        }
        for(const item of journal.targets){
            const current=await read(this.file(item.relative));
            if(current!==item.text)await atomicWrite(this.file(item.relative),item.text,item.expected,path.join(this.dir,'backups'));
            this.state.files[item.relative]={id:item.week.id};
            await this.captureLocal(item.relative);
        }
        for(const item of (journal.cleanup||[])){
            const current=await read(this.file(item.relative));
            if(current!==null){
                await fs.mkdir(path.join(this.dir,'backups'),{recursive:true});
                await fs.writeFile(path.join(this.dir,'backups',Date.now()+'-'+randomUUID()+'.md'),current,{flag:'wx'});
                await fs.unlink(this.file(item.relative));
            }
            delete this.state.files[item.relative];
        }
        if(journal.records)this.state.base=Object.fromEntries(journal.records.map(r=>[r.id,r]));
        this.state.local={};
        for(const relative of Object.keys(this.state.files))await this.captureLocal(relative);
        await this.save();await fs.rm(file,{force:true});
    }
}

async function main() {
    const args=process.argv.slice(2),dir=args[args.indexOf('--state')+1];
    if(!args.includes('--state')||!dir)throw new Error('Usage: node bridge.mjs --state <private-directory> [--initialize|--once]');
    const config=JSON.parse(await fs.readFile(path.join(dir,'config.json'),'utf8'));
    let token;
    if(process.env.WORKLOG_TEST_TOKEN&&process.env.WORKLOG_TEST_MODE==='1')token=process.env.WORKLOG_TEST_TOKEN;
    else{
        const decrypt=spawnSync('powershell.exe',['-NoProfile','-NonInteractive','-Command',
            "$s=(Get-Content -Raw -LiteralPath $env:WORKLOG_TOKEN_FILE).Trim() | ConvertTo-SecureString; $p=[Runtime.InteropServices.Marshal]::SecureStringToBSTR($s); try {[Runtime.InteropServices.Marshal]::PtrToStringBSTR($p)} finally {[Runtime.InteropServices.Marshal]::ZeroFreeBSTR($p)}"
        ],{encoding:'utf8',windowsHide:true,env:{...process.env,WORKLOG_TOKEN_FILE:path.join(dir,'token.dpapi')}});
        if(decrypt.status!==0)throw new Error('Cannot unlock device credential for this Windows account.');
        token=decrypt.stdout.trim();
    }
    const lockFile=path.join(dir,'bridge.lock');
    // OS-held lock via exclusive file creation; stale locks require verified dead PID.
    try{const previous=JSON.parse(await fs.readFile(lockFile,'utf8'));try{process.kill(previous.pid,0);throw new Error('Bridge is already running.');}catch(e){if(e.code!=='ESRCH')throw e;await fs.unlink(lockFile);}}catch(e){if(e.code!=='ENOENT')throw e;}
    await fs.writeFile(lockFile,JSON.stringify({pid:process.pid}),{flag:'wx'});
    const bridge=new Bridge(config,token,dir);
    try{
        await bridge.open();await bridge.recover();
        if(args.includes('--initialize')){await bridge.initialize();console.log('Initial note verified and paired.');return;}
        let delay=10000;
        do{
            try{const result=await bridge.cycle();console.log(new Date().toISOString(),JSON.stringify(result));delay=10000;}
            catch(e){console.error(new Date().toISOString(),e.message);if(args.includes('--once'))throw e;delay=Math.min(delay*2,300000);}
            if(args.includes('--once'))break;
            await new Promise(resolve=>setTimeout(resolve,delay));
        }while(true);
    }finally{await fs.rm(lockFile,{force:true});}
}
if(process.argv[1]&&path.resolve(process.argv[1])===fileURLToPath(import.meta.url))main().catch(e=>{console.error(e.message);process.exitCode=1;});
