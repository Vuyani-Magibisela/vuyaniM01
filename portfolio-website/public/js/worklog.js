(() => {
    'use strict';
    const root = document.querySelector('#worklog');
    if (!root) return;
    const $ = selector => root.querySelector(selector);
    const base = root.dataset.base;
    const state = { records: [], conflicts: [], devices: [], revision: 0, view: 'weeks', dirty: new Map(), timers: new Map(), busy: new Set() };
    const uuid = () => crypto.randomUUID();
    const today = () => new Intl.DateTimeFormat('en-CA', {timeZone:'Africa/Johannesburg',year:'numeric',month:'2-digit',day:'2-digit'}).format(new Date());
    const plusDays = (day, amount) => { const d = new Date(day + 'T12:00:00Z'); d.setUTCDate(d.getUTCDate()+amount); return d.toISOString().slice(0,10); };
    const monday = day => { const d=new Date(day+'T12:00:00Z'); return plusDays(day,-((d.getUTCDay()+6)%7)); };
    const weekTitle = start => 'Week of ' + new Date(start+'T12:00:00Z').toLocaleDateString('en-ZA',{day:'numeric',month:'long',year:'numeric'});
    const weeks = () => state.records.filter(r=>r.kind==='week'&&!r.deleted).sort((a,b)=>b.data.starts_on.localeCompare(a.data.starts_on));
    const tasks = () => state.records.filter(r=>r.kind==='task');
    const currentWeek = () => weeks().find(w=>w.data.starts_on<=today()&&w.data.ends_on>=today());
    const find = id => state.records.find(r=>r.id===id);
    const error = text => { $('#wl-error').textContent=text||''; $('#wl-error').hidden=!text; };
    const status = text => { $('#wl-save').textContent=text; };
    function el(tag, text, className) { const e=document.createElement(tag); if(text!==undefined)e.textContent=text; if(className)e.className=className; return e; }
    function button(text, fn, className) { const b=el('button',text,className); b.type='button'; b.addEventListener('click',()=>Promise.resolve().then(fn).catch(e=>error(e.message))); return b; }
    function input(label, type, value, change) {
        const wrap=el('label'), title=el('span',label), field=el(type==='textarea'?'textarea':'input');
        if(type!=='textarea')field.type=type; field.value=value??''; wrap.append(title,field);
        if(change) field.addEventListener('input',()=>change(field.value)); return {wrap,field};
    }
    function select(label, options, value, change) {
        const wrap=el('label'), field=el('select'); if(label)wrap.append(el('span',label));
        for(const [id,name] of options){ const o=el('option',name);o.value=id;field.append(o); }
        field.value=value??''; field.addEventListener('change',()=>change(field.value));wrap.append(field);return {wrap,field};
    }
    async function api(route, body, method) {
        const options={method:method||(body?'POST':'GET'),cache:'no-store',headers:{'Accept':'application/json'}};
        if(body){options.headers['Content-Type']='application/json';options.headers['X-CSRF-Token']=root.dataset.csrf;options.body=JSON.stringify(body);}
        const response=await fetch(base+'/todo/'+route,options);
        let data; try{data=await response.json();}catch{throw new Error('The server did not return a valid response. Your draft is still here.');}
        if(!response.ok){const e=new Error(data.error||'Request failed');e.status=response.status;throw e;}return data;
    }
    function putRecord(record) { if(!record)return;const i=state.records.findIndex(r=>r.id===record.id);if(i<0)state.records.push(record);else state.records[i]=record; }
    function mark(record) {
        state.dirty.set(record.id,record); status('Unsaved changes');
        clearTimeout(state.timers.get(record.id));
        state.timers.set(record.id,setTimeout(()=>save(record.id),650));
    }
    async function save(id) {
        if(state.busy.has(id)||!state.dirty.has(id))return;
        const record=state.dirty.get(id), sent=JSON.parse(JSON.stringify(record));
        state.busy.add(id);status('Saving…');
        try {
            const result=await api((record.kind==='week'?'weeks':'tasks')+'/'+id,{
                ...sent,operation_id:uuid()
            },'PATCH');
            if(JSON.stringify(record.data)===JSON.stringify(sent.data)&&record.deleted===sent.deleted){
                state.dirty.delete(id);Object.assign(record.data,result.record.data);record.version=result.record.version;record.deleted=result.record.deleted;record.updated_at=result.record.updated_at;putRecord(record);
            }else{
                record.version=result.record.version;putRecord(record);
                setTimeout(()=>save(id),0);
            }
            status(state.dirty.size?'Unsaved changes':'Saved to website · Obsidian pending');if(!state.dirty.size)error('');updateStats();
        } catch(e) {
            error(e.message);status('Not saved · your draft is retained');
            if(e.status===409) conflictDialog(record);
        } finally {state.busy.delete(id);}
    }
    async function flush() {
        for(const id of [...state.dirty.keys()]) {clearTimeout(state.timers.get(id));await save(id);}
        if(state.dirty.size||state.busy.size)throw new Error('Finish saving or resolve unsaved edits first.');
    }
    function dialog(title) {const box=$('#wl-dialog-content');box.replaceChildren(el('h3',title));if(!$('#wl-dialog').open)$('#wl-dialog').showModal();return box;}
    async function conflictDialog(draft) {
        const remote=(await api((draft.kind==='week'?'weeks/':'tasks/')+draft.id)).record;
        const box=dialog('This entry changed elsewhere');
        box.append(el('p','Your draft is retained. Compare it with the latest website version before choosing.'));
        box.append(el('pre','Website:\n'+JSON.stringify(remote.data,null,2)));
        box.append(el('pre','Your draft:\n'+JSON.stringify(draft.data,null,2)));
        const actions=el('div',undefined,'wl-actions');
        actions.append(button('Keep website version',()=>{state.dirty.delete(draft.id);putRecord(remote);$('#wl-dialog').close();error('');render();}),
            button('Save my draft over this version',async()=>{draft.version=remote.version;$('#wl-dialog').close();await save(draft.id);render();}));
        box.append(actions);
    }
    async function createWeek(start) {
        const existing=weeks().find(w=>w.data.starts_on===start);if(existing)return existing;
        const result=await api('weeks',{id:uuid(),kind:'week',operation_id:uuid(),version:0,data:{title:weekTitle(start),starts_on:start,ends_on:plusDays(start,6)}});
        putRecord(result.record);return result.record;
    }
    async function load(initial=false) {
        if(state.dirty.size||state.busy.size){if(!initial)throw new Error('Save or resolve your edits before refreshing.');return;}
        Object.assign(state,await api('state'));
        if(!currentWeek()){try{await createWeek(monday(today()));}catch(e){if(e.status!==409)throw e;Object.assign(state,await api('state'));}}
        render();error('');
        const active=state.devices.filter(d=>!d.revoked_at);
        status(!active.length?'Saved to website · bridge not paired':active.some(d=>Number(d.acknowledged_revision)>=state.revision)?'Website + Obsidian synchronized':'Saved to website · waiting for Obsidian');
    }
    function updateStats() {
        const list=tasks().filter(t=>!t.deleted&&t.data.week_id===currentWeek()?.id);
        const done=list.filter(t=>t.data.status==='done').length;
        const values=[[list.length,'this week'],[list.filter(t=>t.data.status==='open').length,'open'],[list.filter(t=>t.data.status==='waiting').length,'waiting'],[list.length?Math.round(done/list.length*100)+'%':'0%','complete']];
        $('#wl-stats').replaceChildren(...values.map(([n,label])=>{const d=el('div',undefined,'wl-stat');d.append(el('strong',String(n)),el('span',label));return d;}));
    }
    function matches(task) {
        const q=$('#wl-search').value.trim().toLowerCase(), filter=$('#wl-filter').value;
        return (filter==='all'||task.data.status===filter)&&(!q||[task.data.description,task.data.area,task.data.waiting_on].join(' ').toLowerCase().includes(q));
    }
    function taskCard(record,index) {
        const d=record.data, card=el('article',undefined,'wl-card');card.dataset.status=d.status;card.dataset.priority=d.priority;
        const top=el('div',undefined,'wl-card-top');top.append(el('span',String(index+1).padStart(2,'0'),'wl-number'));
        const set=(key,value)=>{d[key]=value;mark(record);card.dataset.status=d.status;card.dataset.priority=d.priority;};
        const statusSelect=select('',[['open','⬜ Open'],['waiting','⏳ Waiting'],['done','✓ Done']],d.status,value=>{
            set('status',value);d.finished_on=value==='done'?(d.finished_on||today()):null;
        });statusSelect.field.setAttribute('aria-label','Task status');
        const priority=select('',[['high','High priority'],['medium','Medium priority'],['low','Low priority']],d.priority,v=>set('priority',v));priority.field.setAttribute('aria-label','Task priority');
        top.append(statusSelect.field,priority.field);card.append(top);
        const description=input('Task / next action','textarea',d.description,v=>set('description',v));
        description.field.className='wl-description';description.field.maxLength=20000;card.append(description.wrap);
        const details=el('details'), summary=el('summary','Plan, schedule & details');details.append(summary);
        const fields=el('div',undefined,'wl-fields');
        fields.append(select('Week',[['','Inbox'],...weeks().map(w=>[w.id,w.data.title])],d.week_id,v=>set('week_id',v||null)).wrap);
        for(const [key,label,type] of [
            ['area','Area','text'],['schedule','Flexible schedule','text'],['planned_on','Plan to work on','date'],
            ['due_on','Deadline','date'],['waiting_on','Waiting on','text'],['follow_up_on','Follow up on','date'],
            ['started_on','Started','date'],['finished_on','Finished','date']
        ]){const f=input(label,type,d[key],v=>set(key,type==='date'?(v||null):v));if(type==='text')f.field.maxLength=500;fields.append(f.wrap);}
        details.append(fields);card.append(details);
        const foot=el('div',undefined,'wl-card-foot');
        foot.append(el('small',[d.area,d.planned_on?'Planned '+d.planned_on:'',d.due_on?'Due '+d.due_on:''].filter(Boolean).join(' · ')||'Keep the next action obvious.'));
        if(record.deleted)foot.append(button('Restore',async()=>{record.deleted=false;mark(record);await save(record.id);render();}));
        else foot.append(button('↑',async()=>move(record,-1)),button('↓',async()=>move(record,1)),
            button('Remove',async()=>{if(!confirm('Move this task to the archive bin?'))return;record.deleted=true;mark(record);await save(record.id);render();}));
        foot.querySelectorAll('button').forEach(b=>{if(b.textContent==='↑')b.setAttribute('aria-label','Move task up');if(b.textContent==='↓')b.setAttribute('aria-label','Move task down');});
        card.append(foot);return card;
    }
    async function move(record,direction) {
        await flush();
        const group=tasks().filter(t=>!t.deleted&&t.data.week_id===record.data.week_id).sort(order);
        const i=group.findIndex(t=>t.id===record.id), j=i+direction;if(j<0||j>=group.length)return;
        [group[i],group[j]]=[group[j],group[i]];
        for(let n=0;n<group.length;n++){if(group[n].data.position!==n){group[n].data.position=n;mark(group[n]);await save(group[n].id);}}
        render();
    }
    const order=(a,b)=>a.data.position-b.data.position||a.id.localeCompare(b.id);
    function list(records) {const container=el('div',undefined,'wl-list');const visible=records.filter(matches).sort(order);container.append(...visible.map(taskCard));if(!visible.length)container.append(el('p','A little breathing room. No tasks in this view.','wl-empty'));return container;}
    function render() {
        updateStats();
        $('#wl-today').textContent=new Date().toLocaleDateString('en-ZA',{timeZone:'Africa/Johannesburg',day:'2-digit',month:'short',year:'numeric'}).toUpperCase();
        root.querySelectorAll('[data-view]').forEach(b=>b.setAttribute('aria-pressed',String(b.dataset.view===state.view)));
        const capture=$('#wl-capture-week'), previous=capture.value;
        capture.replaceChildren(new Option('Inbox',''),...weeks().map(w=>new Option(w.data.title,w.id)));
        capture.value=weeks().some(w=>w.id===previous)?previous:'';
        state.openWeeks??=new Map();root.querySelectorAll('.wl-group').forEach(g=>state.openWeeks.set(g.dataset.week,g.open));
        const box=$('#wl-content');box.replaceChildren();$('#wl-capture').hidden=['sync','review','trash'].includes(state.view);
        if(state.view==='sync'){renderSync(box);return;}
        if(state.view==='review'){renderReview(box);return;}
        if(state.view==='weeks'){
            const ordered=weeks().sort((a,b)=>(b.id===currentWeek()?.id)-(a.id===currentWeek()?.id)||b.data.starts_on.localeCompare(a.data.starts_on));
            for(const w of ordered) {
                const group=el('details',undefined,'wl-group');group.dataset.week=w.id;group.open=state.openWeeks.has(w.id)?state.openWeeks.get(w.id):w.id===currentWeek()?.id;
                const entries=tasks().filter(t=>!t.deleted&&t.data.week_id===w.id);
                const summary=el('summary');summary.append(el('strong',w.data.title),el('small',entries.filter(t=>t.data.status==='done').length+' / '+entries.length+' complete'));
                const actions=el('div',undefined,'wl-week-actions');
                actions.append(button('Export Markdown',()=>{window.location.assign(base+'/todo/export/'+w.id);}),
                    button('Review week',()=>{state.reviewWeek=w.id;state.view='review';render();}));
                group.append(summary,actions,list(entries));box.append(group);
            }
        } else {
            const entries=tasks().filter(t=>{
                if(state.view==='trash')return t.deleted;
                if(t.deleted)return false;if(state.view==='inbox')return !t.data.week_id;
                return t.data.status!=='done'&&['planned_on','due_on','follow_up_on'].some(k=>t.data[k]&&t.data[k]<=today()&&(k!=='follow_up_on'||t.data.status==='waiting'));
            });
            box.append(list(entries));
        }
    }
    function renderReview(box) {
        const panel=el('section',undefined,'wl-panel');panel.append(el('h3','Close the loop. Choose what moves forward.'));
        const w=find(state.reviewWeek)||currentWeek();if(!w)return;
        panel.append(select('Review week',weeks().map(x=>[x.id,x.data.title]),w.id,v=>{state.reviewWeek=v;render();}).wrap);
        const review=input('What worked? What needs a different approach?','textarea',w.data.review,v=>{w.data.review=v;mark(w);});panel.append(review.wrap);
        const unfinished=tasks().filter(t=>!t.deleted&&t.data.week_id===w.id&&t.data.status!=='done').sort(order);
        const selected=new Set();
        for(const t of unfinished){const label=el('label',undefined,'wl-review-row'),check=el('input');check.type='checkbox';check.addEventListener('change',()=>check.checked?selected.add(t.id):selected.delete(t.id));label.append(check,el('span',t.data.description+' ('+t.data.status+')'));panel.append(label);}
        if(!unfinished.length)panel.append(el('p','All clear. This week has no unfinished tasks.'));
        panel.append(button('Carry selected tasks to next week',async()=>{
            await flush();const target=await createWeek(plusDays(w.data.starts_on,7));
            for(const id of selected){const task=find(id);task.data.week_id=target.id;mark(task);await save(id);}
            if(state.dirty.size)throw new Error('Some tasks could not be moved. Review the retained edits.');
            await load();status('Selected tasks carried forward · history retained');
        },'wl-primary'));box.append(panel);
    }
    function renderSync(box) {
        const panel=el('section',undefined,'wl-panel');panel.append(el('h3','Your notebook, connected.'));
        panel.append(el('p','The Windows bridge keeps your website and Obsidian notes together. When the computer is offline, website changes wait here. Never run the old dashboard writer alongside the bridge.'));
        panel.append(button('Pair Windows bridge',async()=>{
            const result=await api('devices',{name:'Windows bridge'});
            const content=dialog('Pair your Windows bridge');
            content.append(el('p','Copy this credential now. It will not be shown again. Paste it into the bridge setup prompt, not a command line.'));
            content.append(el('p',result.token,'wl-token'));
            content.append(el('pre','powershell -File tools/worklog-bridge/install.ps1\nWebsite URL: '+location.origin+base));
            await load();
        },'wl-primary'));
        for(const d of state.devices) {
            const p=el('div',undefined,'wl-panel');
            p.append(el('strong',d.name+(d.revoked_at?' · revoked':'')),el('p','Last contact: '+(d.last_seen?d.last_seen+' UTC':'not connected')+' · confirmed revision '+d.acknowledged_revision));
            if(!d.revoked_at)p.append(button('Revoke access',async()=>{await api('devices/'+d.id,{},'DELETE');await load();}));
            panel.append(p);
        }
        box.append(panel);
        for(const conflict of state.conflicts) {
            const p=el('section',undefined,'wl-panel'), detail=conflict.detail, current=find(detail.incoming.id);
            p.append(el('h3','Review a sync conflict'),el('p','Fields: '+detail.fields.join(', ')));
            p.append(el('pre','Website:\n'+JSON.stringify(current?.data,null,2)),
                el('pre','Note proposal'+(detail.incoming.deleted?' (delete task)':'')+':\n'+JSON.stringify(detail.incoming.data,null,2)));
            const resolve=async choice=>{await api('conflicts/'+conflict.id,{choice,version:current?.version||0});await load();};
            p.append(button('Keep website',()=>resolve('website')),button(detail.incoming.deleted?'Confirm deletion':'Use note changes',()=>resolve('note')));
            box.append(p);
        }
    }
    $('#wl-capture').addEventListener('submit',async event=>{
        event.preventDefault();const submit=event.submitter;submit.disabled=true;
        try {
            const description=$('#wl-quick').value.trim();if(!description)return;
            const week=$('#wl-capture-week').value||null;
            const result=await api('tasks',{id:uuid(),operation_id:uuid(),version:0,data:{description,week_id:week,position:tasks().filter(t=>t.data.week_id===week).length}});
            putRecord(result.record);$('#wl-quick').value='';if(!week)state.view='inbox';else state.view='weeks';render();status('Task captured · Obsidian pending');error('');
        }catch(e){error(e.message);}finally{submit.disabled=false;}
    });
    root.querySelectorAll('[data-view]').forEach(b=>b.addEventListener('click',()=>{state.view=b.dataset.view;render();}));
    $('#wl-search').addEventListener('input',render);$('#wl-filter').addEventListener('change',render);
    $('#wl-refresh').addEventListener('click',()=>load().catch(e=>error(e.message)));
    $('#wl-new-week').addEventListener('click',()=>{
        const box=dialog('Start a weekly page'),date=input('Week begins (Monday)','date',monday(today()));
        box.append(date.wrap,button('Create week',async()=>{await flush();await createWeek(monday(date.field.value));$('#wl-dialog').close();state.view='weeks';render();},'wl-primary'));
    });
    $('#wl-import').addEventListener('click',()=>{
        const box=dialog('Import an Obsidian week'),file=input('Markdown note','file',''),start=input('Week starts','date',monday(today()));
        file.field.accept='.md';box.append(file.wrap,start.wrap,el('p','Import reads a copy. It does not change your original note.'));
        box.append(button('Preview import',async()=>{
            if(!file.field.files[0])throw new Error('Choose a Markdown note.');
            const markdown=await file.field.files[0].text(),starts_on=monday(start.field.value),ends_on=plusDays(starts_on,6);
            const body={markdown,starts_on,ends_on};const result=await api('import',body);
            box.append(el('p',result.preview.week.title+' · '+result.preview.tasks+' tasks. Existing identical records will be skipped.'));
            box.append(button('Confirm import',async()=>{await flush();await api('import',{...body,commit:true,expected_revision:result.preview.revision});$('#wl-dialog').close();await load();},'wl-primary'));
        }));
    });
    window.addEventListener('beforeunload',e=>{if(state.dirty.size||state.busy.size){e.preventDefault();e.returnValue='';}});
    window.addEventListener('online',()=>{for(const id of state.dirty.keys())save(id);});
    setInterval(()=>{if(!document.hidden&&!state.dirty.size&&!state.busy.size&&!$('#wl-dialog').open&&!['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName))load(true).catch(()=>status('Connection interrupted · retrying'));},15000);
    load(true).catch(e=>{error(e.message);status('Could not open notebook');});
})();
