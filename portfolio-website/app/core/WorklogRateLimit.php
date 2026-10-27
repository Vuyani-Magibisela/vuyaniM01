<?php
namespace App\Core;

/** A small, shared-worker limiter suitable for the existing single-host deployment. */
class WorklogRateLimit {
    public static function check(string $key, int $limit=300): void {
        $directory=dirname(__DIR__,2).'/logs/worklog-rate';
        if (!is_dir($directory) && !mkdir($directory,0700,true) && !is_dir($directory)) {
            throw new \RuntimeException('Request limiter unavailable.',503);
        }
        $file=fopen($directory.'/'.hash('sha256',$key).'.json','c+');
        if (!$file || !flock($file,LOCK_EX)) throw new \RuntimeException('Request limiter unavailable.',503);
        try {
            $state=json_decode(stream_get_contents($file),true) ?: ['minute'=>0,'count'=>0];
            $minute=intdiv(time(),60);
            if ($state['minute']!==$minute) $state=['minute'=>$minute,'count'=>0];
            if ($state['count'] >= $limit) {
                header('Retry-After: 60');
                throw new \RuntimeException('Too many requests. Try again in a minute.',429);
            }
            $state['count']++;
            rewind($file);ftruncate($file,0);fwrite($file,json_encode($state));fflush($file);
        } finally {flock($file,LOCK_UN);fclose($file);}
    }
}
