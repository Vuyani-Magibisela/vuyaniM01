<?php
namespace App\Core;

/** Strict legacy/new workLOG tables; arbitrary HTML is retained as text, never rendered. */
class WorklogMarkdown {
    public static function cells(string $line): array {
        $line = trim($line);
        if (str_starts_with($line, '|')) $line = substr($line, 1);
        if (str_ends_with($line, '|')) $line = substr($line, 0, -1);
        $out = []; $cell = ''; $depth = 0; $escape = false;
        for ($i=0; $i<strlen($line); $i++) {
            $char = $line[$i];
            if ($escape) { $cell .= '\\' . $char; $escape = false; continue; }
            if ($char === '\\') { $escape = true; continue; }
            if (substr($line,$i,2) === '[[') { $depth++; $cell .= '[['; $i++; continue; }
            if (substr($line,$i,2) === ']]' && $depth) { $depth--; $cell .= ']]'; $i++; continue; }
            if ($char === '|' && !$depth) { $out[] = trim($cell); $cell=''; } else $cell .= $char;
        }
        if ($escape) $cell .= '\\';
        $out[] = trim($cell);
        return $out;
    }

    private static function decode(string $value): string {
        return str_replace(['&#124;','\\|'], '|', preg_replace('/<br\s*\/?>/i', "\n", $value));
    }

    private static function date(string $value): ?string {
        if (!$value || $value === '—') return null;
        if (preg_match('~^(\d{2})/(\d{2})/(\d{4})$~', $value, $m)) return "$m[3]-$m[2]-$m[1]";
        return $value;
    }

    public static function parse(string $markdown): array {
        if (strlen($markdown) > 1000000) throw new \InvalidArgumentException('Note exceeds 1 MB.', 422);
        $lines = preg_split('/\r?\n/', $markdown);
        $header = null;
        foreach ($lines as $i=>$line) if (preg_match('/^\|\s*#\s*\|\s*Status\s*\|/i', $line)) {
            if ($header !== null) throw new \InvalidArgumentException('Import one weekly table at a time.', 422);
            $header = $i;
        }
        if ($header === null || !isset($lines[$header+1]) || !preg_match('/^\|[-:\s|]+\|$/', $lines[$header+1])) {
            throw new \InvalidArgumentException('A valid workLOG table is required.', 422);
        }
        preg_match('/^#\s+(.+)$/m', $markdown, $heading);
        preg_match('/<!-- worklog-week:([a-f0-9-]{36}) -->/', $markdown, $weekId);
        preg_match('/<!-- worklog-dates:(\d{4}-\d{2}-\d{2}),(\d{4}-\d{2}-\d{2}) -->/', $markdown, $dates);
        $tasks=[]; $seen=[]; $end=$header+2;
        for ($i=$header+2; $i<count($lines) && str_starts_with(ltrim($lines[$i]), '|'); $i++) {
            $cells=self::cells($lines[$i]); $end=$i+1;
            if (count($cells)<8 || count($cells)>12) throw new \InvalidArgumentException('Malformed task row '.($i+1).'.',422);
            preg_match('/<!-- worklog-id:([a-f0-9-]{36}) -->/', $cells[0], $id);
            if (!empty($id[1]) && isset($seen[$id[1]])) throw new \InvalidArgumentException('Duplicate task identifier.',422);
            if (!empty($id[1])) $seen[$id[1]]=true;
            $status=strtolower($cells[1]);
            if (!preg_match('/open|waiting|done/i',$status)) throw new \InvalidArgumentException('Unknown task status.',422);
            $tasks[]=['id'=>$id[1]??null,'data'=>[
                'position'=>count($tasks),'status'=>str_contains($status,'done')?'done':(str_contains($status,'waiting')?'waiting':'open'),
                'area'=>self::decode($cells[2]),'description'=>self::decode($cells[3]),'priority'=>strtolower($cells[4]),
                'schedule'=>$cells[5]==='—'?'':self::decode($cells[5]),
                'started_on'=>self::date($cells[6]),'finished_on'=>self::date($cells[7]),
                'planned_on'=>self::date($cells[8]??''),'due_on'=>self::date($cells[9]??''),
                'waiting_on'=>self::decode(($cells[10]??'')==='—'?'':($cells[10]??'')),
                'follow_up_on'=>self::date($cells[11]??'')
            ]];
        }
        $related='';
        if (preg_match('/^## Related Notes\s*\n([\s\S]*?)(?=^## |\s*<!-- worklog-review:start -->|\z)/m',$markdown,$match)) $related=trim($match[0]);
        $review='';
        if (preg_match('/<!-- worklog-review:start -->\r?\n([\s\S]*?)\r?\n<!-- worklog-review:end -->/', $markdown,$match)) $review=$match[1];
        return ['title'=>trim($heading[1]??'To Do'),'id'=>$weekId[1]??null,'starts_on'=>$dates[1]??null,
            'ends_on'=>$dates[2]??null,'tasks'=>$tasks,'related'=>$related,'review'=>$review];
    }

    public static function render(array $week, array $tasks): string {
        $data=$week['data'];
        $cell=static fn($text)=>str_replace(["\r\n","\n","\r",'|'],['<br>','<br>','<br>','&#124;'],(string)($text??''));
        $date=static fn($value)=>$value ? substr($value,8,2).'/'.substr($value,5,2).'/'.substr($value,0,4) : '—';
        $tasks=array_values(array_filter($tasks,fn($t)=>!$t['deleted']));
        usort($tasks,fn($a,$b)=>($a['data']['position']<=>$b['data']['position']) ?: strcmp($a['id'],$b['id']));
        $done=count(array_filter($tasks,fn($t)=>$t['data']['status']==='done'));
        $waiting=count(array_filter($tasks,fn($t)=>$t['data']['status']==='waiting'));
        $open=count($tasks)-$done-$waiting;
        $text='# '.$data['title']."\n\n<!-- worklog-week:".$week['id']." -->\n";
        $text.='<!-- worklog-dates:'.$data['starts_on'].','.$data['ends_on']." -->\n\n";
        $text.="> [!summary] Weekly progress\n> **$done complete · $waiting waiting · $open open**\n\n";
        $text.="| # | Status | Area | Task / next action | Priority | Schedule | Started | Finished | Planned | Deadline | Waiting on | Follow up |\n";
        $text.="|---:|:---:|---|---|:---:|---|:---:|:---:|:---:|:---:|---|:---:|\n";
        foreach ($tasks as $i=>$task) {
            $d=$task['data'];
            $row=[($i+1).' <!-- worklog-id:'.$task['id'].' -->',
                ['open'=>'⬜ Open','waiting'=>'⏳ Waiting','done'=>'✅ Done'][$d['status']],
                $cell($d['area']),$cell($d['description']),ucfirst($d['priority']),$cell($d['schedule'])?:'—',
                $date($d['started_on']),$date($d['finished_on']),$date($d['planned_on']),$date($d['due_on']),
                $cell($d['waiting_on'])?:'—',$date($d['follow_up_on'])];
            $text.='| '.implode(' | ',$row)." |\n";
        }
        return $text."\n".($data['related']??'')."\n\n<!-- worklog-review:start -->\n".($data['review']??'')."\n<!-- worklog-review:end -->\n";
    }
}
