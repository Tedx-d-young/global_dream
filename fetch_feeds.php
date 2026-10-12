<?php
/*
 * CLI feed importer. Run via cron/task scheduler, e.g. every 6 hours.
 * Fetches RSS/Atom feeds from admin-configured URLs, applies URL/IP checks,
 * stores candidate entries in discovery_items, and NEVER auto-publishes them.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit("CLI only.\n"); }
require __DIR__ . '/../includes/bootstrap.php';

function safe_feed_url(string $url, string $allowHost): bool {
    if (!valid_http_url($url)) return false;
    $host = strtolower((string)parse_url($url, PHP_URL_HOST));
    if ($host === '' || $host !== strtolower($allowHost)) return false;
    if (filter_var($host, FILTER_VALIDATE_IP)) {
        return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }
    $ips = [];
    $records = @dns_get_record($host, DNS_A + DNS_AAAA);
    if (is_array($records)) foreach ($records as $r) { if (!empty($r['ip'])) $ips[]=$r['ip']; if (!empty($r['ipv6'])) $ips[]=$r['ipv6']; }
    if (!$ips) { $ip = gethostbyname($host); if ($ip !== $host) $ips[]=$ip; }
    if (!$ips) return false;
    foreach ($ips as $ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) return false;
    }
    return true;
}
function fetch_feed(string $url, int $timeout, int $maxBytes): string {
    if (!function_exists('curl_init')) throw new RuntimeException('PHP cURL extension is required.');
    $ch=curl_init($url);
    curl_setopt_array($ch,[
        CURLOPT_RETURNTRANSFER=>true, CURLOPT_FOLLOWLOCATION=>false, CURLOPT_CONNECTTIMEOUT=>5,
        CURLOPT_TIMEOUT=>$timeout, CURLOPT_MAXFILESIZE=>$maxBytes,
        CURLOPT_USERAGENT=>'GlobalDreamScholarshipBot/1.0 (+admin-managed RSS/Atom feed reader)',
        CURLOPT_HTTPHEADER=>['Accept: application/atom+xml, application/rss+xml, application/xml, text/xml;q=0.9'],
        CURLOPT_PROTOCOLS=>CURLPROTO_HTTP|CURLPROTO_HTTPS,
    ]);
    $body=curl_exec($ch);$status=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE);$error=curl_error($ch);$size=(int)curl_getinfo($ch,CURLINFO_SIZE_DOWNLOAD);curl_close($ch);
    if($body===false)throw new RuntimeException("Fetch error: $error");
    if($status<200||$status>=300)throw new RuntimeException("Feed returned HTTP $status.");
    if(strlen($body)>$maxBytes||$size>$maxBytes)throw new RuntimeException('Feed exceeds maximum permitted size.');
    return $body;
}
function clean_text(?string $s, int $limit=4000): string {
    $s=html_entity_decode(strip_tags($s??''),ENT_QUOTES|ENT_HTML5,'UTF-8');
    $s=preg_replace('/\s+/u',' ',trim($s)) ?? trim($s);
    return mb_substr($s,0,$limit,'UTF-8');
}
function parse_feed_entries(string $xml, int $maxItems): array {
    libxml_use_internal_errors(true);
    $doc=new DOMDocument();
    $ok=$doc->loadXML($xml, LIBXML_NONET | LIBXML_NOBLANKS | LIBXML_NOCDATA);
    if(!$ok)throw new RuntimeException('Feed XML is invalid.');
    $xp=new DOMXPath($doc);
    $nodes=$xp->query('//*[local-name()="item"]');
    if(!$nodes||$nodes->length===0)$nodes=$xp->query('//*[local-name()="entry"]');
    $items=[];
    if($nodes) foreach($nodes as $node) {
        if(count($items)>=$maxItems)break;
        $get=function(array $names) use ($xp,$node): string {
            foreach($names as $name){$n=$xp->query('./*[local-name()="'.$name.'"]',$node);if($n&&$n->length){$el=$n->item(0);if($name==='link'&&$el instanceof DOMElement){$href=$el->getAttribute('href');if($href!=='')return $href;}return $el->textContent;}}
            return '';
        };
        $title=clean_text($get(['title']),500);
        $link=trim($get(['link','id']));
        if($link===''||!valid_http_url($link)||$title==='')continue;
        $summary=clean_text($get(['description','summary','content','encoded']),3000);
        $guid=clean_text($get(['guid','id']),500);
        $items[]=['title'=>$title,'url'=>$link,'summary'=>$summary,'guid'=>$guid];
    }
    libxml_clear_errors();
    return $items;
}
$config=require __DIR__.'/../config/config.php';
$feeds=$pdo->query('SELECT * FROM discovery_sources WHERE active=1 ORDER BY id')->fetchAll();
$found=0;$errors=0;
foreach($feeds as $feed){
 try{
  if(!safe_feed_url($feed['feed_url'],$feed['host_allowlist']))throw new RuntimeException('Feed host failed safety checks. Review source configuration.');
  $xml=fetch_feed($feed['feed_url'],(int)$config['discovery']['request_timeout'],(int)$config['discovery']['max_feed_bytes']);
  $items=parse_feed_entries($xml,(int)$config['discovery']['max_items_per_feed']);
  foreach($items as $it){
   // Ensure item link is same host as the configured feed host to prevent unsafe links.
   $itemHost=strtolower((string)parse_url($it['url'],PHP_URL_HOST));
   if($itemHost!==strtolower($feed['host_allowlist']))continue;
   $hash=hash('sha256',$feed['id'].'|'.($it['guid']?:$it['url']));
   $st=$pdo->prepare("INSERT IGNORE INTO discovery_items(source_id,item_hash,title,item_url,summary,review_status,discovered_at) VALUES(?,?,?,?,?,'pending',NOW())");
   $st->execute([$feed['id'],$hash,$it['title'],$it['url'],$it['summary']]);
   if($st->rowCount()>0)$found++;
  }
  $pdo->prepare('UPDATE discovery_sources SET last_checked_at=NOW(),last_error=NULL,updated_at=NOW() WHERE id=?')->execute([$feed['id']]);
  echo "[OK] {$feed['name']}: checked ".count($items)." feed entries.\n";
 }catch(Throwable $e){
  $errors++;$msg=mb_substr($e->getMessage(),0,500);
  $pdo->prepare('UPDATE discovery_sources SET last_checked_at=NOW(),last_error=?,updated_at=NOW() WHERE id=?')->execute([$msg,$feed['id']]);
  echo "[ERROR] {$feed['name']}: $msg\n";
 }
}
echo "New queue items: $found; feeds with errors: $errors\n";
