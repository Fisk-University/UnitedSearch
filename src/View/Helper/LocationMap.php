<?php
namespace UnitedSearch\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Doctrine\DBAL\Connection;

class LocationMap extends AbstractHelper
{
    public function __construct(private Connection $conn) {}

    /**
     * Build/read relationship map for two property identifiers (terms like 'rfc:state'
     * or numeric IDs). Optional site scoping via site_id.
     *
     * @param int|null $siteId
     * @param string   $termOne   e.g. 'rfc:state' or '185'
     * @param string   $termTwo   e.g. 'rfc:county' or '186'
     * @param int      $ttlSeconds
     * @return array{valuesOne:string[], valuesTwoAll:string[], map:array<string,string[]>, generatedAt:string, ttl:int}
     */
    public function __invoke(?int $siteId, string $termOne, string $termTwo, int $ttlSeconds = 3600): array
    {
        $root = defined('OMEKA_PATH') ? OMEKA_PATH : getcwd();
        $dir  = $root . '/files/cache/unitedsearch';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);

        $suffix = ($siteId ? "-site{$siteId}" : '') . '-' . md5($termOne.'|'.$termTwo);
        $cache  = "{$dir}/dualprop-map{$suffix}.json";
        $lock   = "{$cache}.lock";

        // Serve fresh cache if available.
        if (is_file($cache) && (time() - filemtime($cache) < $ttlSeconds)) {
            return $this->normalize(json_decode((string)@file_get_contents($cache), true), $ttlSeconds);
        }

        // Attempt rebuild (non-blocking if another process holds the lock).
        $fh = @fopen($lock, 'c');
        if ($fh && flock($fh, LOCK_EX | LOCK_NB)) {
            try {
                $data = $this->build($siteId, $termOne, $termTwo, $ttlSeconds);
                $tmp  = $cache . '.tmp' . getmypid();
                file_put_contents($tmp, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                @chmod($tmp, 0664);
                @rename($tmp, $cache);
                return $data;
            } finally {
                @flock($fh, LOCK_UN); @fclose($fh); @unlink($lock);
            }
        }

        // Fallback to stale cache if it exists.
        if (is_file($cache)) {
            return $this->normalize(json_decode((string)@file_get_contents($cache), true), $ttlSeconds);
        }

        // First-ever fallback: empty but valid shape.
        return [
            'valuesOne'     => [],
            'valuesTwoAll'  => [],
            'map'           => [],
            'generatedAt'   => gmdate('c'),
            'ttl'           => $ttlSeconds,
        ];
    }

    /** Build the map from DB in one pass, resolving property IDs safely. */
    private function build(?int $siteId, string $termOne, string $termTwo, int $ttl): array
    {
        $pid1 = $this->resolvePropertyId($termOne);
        $pid2 = $this->resolvePropertyId($termTwo);
        if (!$pid1 || !$pid2) {
            return ['valuesOne'=>[], 'valuesTwoAll'=>[], 'map'=>[], 'generatedAt'=>gmdate('c'), 'ttl'=>$ttl];
        }

        $params  = ['p1'=>$pid1, 'p2'=>$pid2];
        $siteSql = '';
        if ($siteId) {
            $siteSql = 'JOIN site_resource sr ON sr.resource_id = v1.resource_id AND sr.site_id = :sid';
            $params['sid'] = $siteId;
        }

        // One query: all distinct (propertyOne value, propertyTwo value) pairs.
        $rows = $this->conn->fetchAllAssociative("
          SELECT DISTINCT TRIM(v1.value) AS val1, TRIM(v2.value) AS val2
          FROM value v1
          JOIN value v2 ON v1.resource_id = v2.resource_id
          $siteSql
          WHERE v1.property_id = :p1 AND v2.property_id = :p2
            AND v1.value <> '' AND v2.value <> ''
        ", $params);

        // Build sets, then sort.
        $map = []; $one = []; $two = [];
        foreach ($rows as $r) {
            $a = $r['val1']; $b = $r['val2'];
            if ($a === '' || $b === '') continue;
            $one[$a] = true; $two[$b] = true;
            $map[$a][$b] = true;
        }

        $valuesOne = array_keys($one); sort($valuesOne, SORT_NATURAL | SORT_FLAG_CASE);
        $valuesTwo = array_keys($two); sort($valuesTwo, SORT_NATURAL | SORT_FLAG_CASE);

        foreach ($map as $k => $set) {
            $list = array_keys($set);
            sort($list, SORT_NATURAL | SORT_FLAG_CASE);
            $map[$k] = $list;
        }

        return [
            'valuesOne'    => $valuesOne,   // first dropdown options
            'valuesTwoAll' => $valuesTwo,   // for OR-join fallback
            'map'          => $map,         // AND-join relationships
            'generatedAt'  => gmdate('c'),
            'ttl'          => $ttl,
        ];
    }

    /**
     * Resolve a property identifier to its numeric ID.
     * Accepts either a numeric string ('185') or a term 'prefix:local_name' (e.g., 'rfc:state').
     */
    private function resolvePropertyId(string $maybeTermOrId): ?int
    {
        if (ctype_digit($maybeTermOrId)) {
            return (int) $maybeTermOrId;
        }
        $parts = explode(':', $maybeTermOrId, 2);
        if (count($parts) !== 2) return null;
        [$prefix, $local] = $parts;

        $sql = "
            SELECT p.id
            FROM property p
            JOIN vocabulary v ON v.id = p.vocabulary_id
            WHERE v.prefix = :prefix AND p.local_name = :local
            LIMIT 1
        ";
        $id = $this->conn->fetchOne($sql, ['prefix' => $prefix, 'local' => $local]);
        return $id ? (int) $id : null;
    }

    /** Ensure we always return a complete, sane structure. */
    private function normalize(?array $d, int $ttl): array
    {
        $d = $d ?: [];
        $d['valuesOne']    = $d['valuesOne']    ?? [];
        $d['valuesTwoAll'] = $d['valuesTwoAll'] ?? [];
        $d['map']          = $d['map']          ?? [];
        $d['generatedAt']  = $d['generatedAt']  ?? gmdate('c');
        $d['ttl']          = $d['ttl']          ?? $ttl;
        return $d;
    }
}
