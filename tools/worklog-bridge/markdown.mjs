import { createHash, randomUUID } from 'node:crypto';

export const stableId = value => {
    const h=createHash('sha256').update(value).digest('hex');
    return [h.slice(0,8),h.slice(8,12),h.slice(12,16),h.slice(16,20),h.slice(20,32)].join('-');
};
export const hash = text => createHash('sha256').update(text).digest('hex');
export function splitCells(line) {
    const source=line.trim().replace(/^\|/,'').replace(/\|$/,'');
    let cell='',depth=0,escape=false;const cells=[];
    for(let i=0;i<source.length;i++){
        const c=source[i];
        if(escape){cell+='\\'+c;escape=false;continue;}
        if(c==='\\'){escape=true;continue;}
        if(source.slice(i,i+2)==='[['){depth++;cell+='[[';i++;continue;}
        if(source.slice(i,i+2)===']]'&&depth){depth--;cell+=']]';i++;continue;}
        if(c==='|'&&!depth){cells.push(cell.trim());cell='';}else cell+=c;
    }
    if(escape)cell+='\\';cells.push(cell.trim());return cells;
}
const decode = value => String(value||'').replace(/<br\s*\/?>/gi,'\n').replace(/&#124;|\\\|/g,'|');
const date = value => {
    if(!value||value==='—')return null;
    const match=value.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    const result=match?[match[3],match[2],match[1]].join('-'):value;
    if(!/^\d{4}-\d{2}-\d{2}$/.test(result)||Number.isNaN(Date.parse(result))||new Date(result).toISOString().slice(0,10)!==result)throw new Error('Invalid date: '+value);
    return result;
};
export function parseNote(text) {
    const lines=text.split(/\r?\n/);
    const headers=lines.flatMap((line,i)=>/^\|\s*#\s*\|\s*Status\s*\|/i.test(line)?[i]:[]);
    if(headers.length!==1)throw new Error('Expected exactly one workLOG table.');
    const header=headers[0];
    if(!/^\|[-:\s|]+\|$/.test(lines[header+1]||''))throw new Error('Malformed table separator.');
    const tasks=[],seen=new Set();let end=header+2;
    while(end<lines.length&&/^\s*\|/.test(lines[end])){
        const cells=splitCells(lines[end++]);
        if(cells.length<8||cells.length>12)throw new Error('Malformed row at line '+end);
        const id=cells[0].match(/<!-- worklog-id:([a-f0-9-]{36}) -->/)?.[1]||null;
        if(id&&seen.has(id))throw new Error('Duplicate task ID. No changes were sent.');
        if(id)seen.add(id);
        if(!/open|waiting|done/i.test(cells[1]))throw new Error('Unknown task status.');
        if(!['high','medium','low'].includes(cells[4].toLowerCase()))throw new Error('Unknown task priority.');
        const data={position:tasks.length,status:/done/i.test(cells[1])?'done':/waiting/i.test(cells[1])?'waiting':'open',
            area:decode(cells[2]),description:decode(cells[3]),priority:cells[4].toLowerCase(),schedule:cells[5]==='—'?'':decode(cells[5]),
            started_on:date(cells[6]),finished_on:date(cells[7]),planned_on:date(cells[8]),due_on:date(cells[9]),
            waiting_on:cells[10]==='—'?'':decode(cells[10]),follow_up_on:date(cells[11])};
        if(!data.description.trim())throw new Error('A task description is empty.');
        tasks.push({id,data});
    }
    const dates=text.match(/<!-- worklog-dates:(\d{4}-\d{2}-\d{2}),(\d{4}-\d{2}-\d{2}) -->/);
    return {id:text.match(/<!-- worklog-week:([a-f0-9-]{36}) -->/)?.[1]||null,
        title:text.match(/^#\s+(.+)$/m)?.[1]?.trim()||'To Do',starts_on:dates?.[1]||null,ends_on:dates?.[2]||null,
        related:text.match(/^## Related Notes\s*\n[\s\S]*?(?=^## |\s*<!-- worklog-review:start -->|$(?![\s\S]))/m)?.[0]?.trim()||'',
        review:text.match(/<!-- worklog-review:start -->\r?\n([\s\S]*?)\r?\n<!-- worklog-review:end -->/)?.[1]||'',
        tasks,header,end,lines};
}
const cell=value=>String(value??'').replace(/\r\n|\r|\n/g,'<br>').replace(/\|/g,'&#124;');
const formatDate=value=>value?[value.slice(8,10),value.slice(5,7),value.slice(0,4)].join('/'):'—';
function table(tasks) {
    const rows=['| # | Status | Area | Task / next action | Priority | Schedule | Started | Finished | Planned | Deadline | Waiting on | Follow up |',
        '|---:|:---:|---|---|:---:|---|:---:|:---:|:---:|:---:|---|:---:|'];
    tasks.forEach((task,i)=>{
        const d=task.data;
        rows.push('| '+[(i+1)+' <!-- worklog-id:'+task.id+' -->',{open:'⬜ Open',waiting:'⏳ Waiting',done:'✅ Done'}[d.status],
            cell(d.area),cell(d.description),d.priority[0].toUpperCase()+d.priority.slice(1),cell(d.schedule)||'—',
            formatDate(d.started_on),formatDate(d.finished_on),formatDate(d.planned_on),formatDate(d.due_on),cell(d.waiting_on)||'—',formatDate(d.follow_up_on)].join(' | ')+' |');
    });return rows.join('\n');
}
export function renderNote(existing, week, records) {
    const d=week.data;
    const tasks=records.filter(t=>t.kind==='task'&&!t.deleted&&t.data.week_id===(week.id==='inbox'?null:week.id))
        .sort((a,b)=>a.data.position-b.data.position||a.id.localeCompare(b.id));
    const done=tasks.filter(t=>t.data.status==='done').length,waiting=tasks.filter(t=>t.data.status==='waiting').length;
    const summary='> [!summary] Weekly progress\n> **'+done+' complete · '+waiting+' waiting · '+(tasks.length-done-waiting)+' open**';
    const marker='<!-- worklog-week:'+week.id+' -->\n<!-- worklog-dates:'+d.starts_on+','+d.ends_on+' -->';
    const review='<!-- worklog-review:start -->\n'+(d.review||'')+'\n<!-- worklog-review:end -->';
    if(!existing)return '# '+d.title+'\n\n'+marker+'\n\n'+summary+'\n\n'+table(tasks)+'\n\n'+(d.related||'')+'\n\n'+review+'\n';
    const parsed=parseNote(existing),lines=parsed.lines;
    lines.splice(parsed.header,parsed.end-parsed.header,table(tasks));
    let text=lines.join('\n').replace(/^#\s+.*$/m,'# '+d.title);
    text=text.replace(/<!-- worklog-week:[^\n]* -->\n?/g,'').replace(/<!-- worklog-dates:[^\n]* -->\n?/g,'');
    text=text.replace(/^#.*$/m,match=>match+'\n\n'+marker);
    if(/^> \[!summary\].*\n(?:>.*\n?)*/m.test(text))text=text.replace(/^> \[!summary\].*\n(?:>.*\n?)*/m,summary+'\n');
    else text=text.replace(/^\|\s*#/m,summary+'\n\n| #');
    const relatedPattern=/^## Related Notes\s*\n[\s\S]*?(?=^## |\s*<!-- worklog-review:start -->|$(?![\s\S]))/m;
    if(relatedPattern.test(text))text=text.replace(relatedPattern,(d.related||'')+'\n\n');
    else if(d.related)text+='\n'+d.related+'\n';
    if(/<!-- worklog-review:start -->[\s\S]*?<!-- worklog-review:end -->/.test(text))text=text.replace(/<!-- worklog-review:start -->[\s\S]*?<!-- worklog-review:end -->/,review);
    else text+='\n'+review+'\n';
    return text.replace(/(<!-- worklog-dates:[^\n]* -->)\n+/,'$1\n\n').replace(/\n+<!-- worklog-review:start -->/,'\n\n<!-- worklog-review:start -->').trimEnd()+'\n';
}
export function operation(record, base=null) {
    return {operation_id:randomUUID(),id:record.id,kind:record.kind,version:base?.version||0,data:record.data,deleted:!!record.deleted,...(base?{base}: {})};
}
export function same(a,b) {
    if(a===b)return true;if(!a||!b||typeof a!=='object'||typeof b!=='object')return false;
    const keys=Object.keys(a);return keys.length===Object.keys(b).length&&keys.every(k=>same(a[k],b[k]));
}
