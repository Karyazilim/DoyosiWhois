<?php
class Home extends Prefab {
    protected $f3, $db;
    private $supported, $all;
    function __construct(){
    global $db;
    $this->fw = \Base::instance();
    $this->db = $db;
    $all_list = $this->db->read("whois.servers.json");

    $this->supported = array_keys($all_list);
    $this->fw->set("supported", $this->supported);
    $this->all = $all_list;
    }

    function Display() {
    echo \Template::instance()->render('default.html');
    }

    function Whois() {
    if($this->fw->exists("POST.domain") && $this->isValidDomain($this->fw->get("POST.domain"))){
    $domain = $this->clear($this->fw->get("POST.domain"));
    $domain = $this->clearDomain($domain);
    $domain_all = explode(".", $domain);
    if(count($domain_all)==2) $ext = $domain_all[1];
    if(count($domain_all)==3) $ext = $domain_all[1].".".$domain_all[2];
    if(count($domain_all)==4) $ext = false;

    $extr = [$domain_all, $ext, $domain, $this->all];
    if($ext) {
    $server = isset($this->all["$ext"][0]) ? $this->all["$ext"][0] : false;
    if($server) {
	if($this->fw->exists("whois.$domain")) {
	$get_data = $this->fw->get("whois.$domain");		
	} else {
	$web = \Web::instance();
    $get_data = $web->whois($domain, $server);
	$this->fw->set("whois.$domain" , $get_data, 86400);
	}

    $response = ["status"=>"success", "data"=>nl2br($get_data)];
    } else {
    $response = ["status"=>"error", "data"=>"server not found", $extr];
    }
    } else {
    $response = ["status"=>"error", "data"=>"subdomains not allowed", $extr];
    }
    } else {
    $response = ["status"=>"error", "data"=>"not valid domain", $extr];
    }
    $this->fw->set("response", json_encode($response)); //application/javascript
    echo \Template::instance()->render('whois.json', 'application/json');
    }

    function BulkWhois() {
    if($this->fw->exists("POST.domain") && strlen($this->fw->get("POST.domain")) > 10){
    $domains = $this->fw->get("POST.domain");
    $domains = $this->fw->scrub($domains);
    $domains = explode("\n", $domains);

    if(count($domains)>0) {
    foreach($domains as $domax) {
    $a = $this->clear($domax);
    $a = $this->clearDomain($a);
    if($this->isValidDomain($a)) $ddd[] = $a;
    }

    $ddd = @array_unique($ddd);
    if (count($ddd) == 0 ) $response = ["status"=>"error", "data"=>"No valid domains"];
	else if($this->fw->LIMITS >= count($ddd)) $response = ["status"=>"success", "data"=>$ddd];
    else $response = ["status"=>"error", "data"=>"Maximum ".$this->fw->LIMITS." domains allowed"];
    } else {
    $response = ["status"=>"error", "data"=>"Please write domains", $ddd];
    }
    } else {
    $response = ["status"=>"error", "data"=>"Please write domains 2"];
    }
    $this->fw->set("response", json_encode($response)); //application/javascript
    echo \Template::instance()->render('whois.json', 'application/json');
    }

    function clear($string){
    return preg_replace("/[^A-Za-z0-9-_.]/", '', strip_tags($string));
    }

	function clearDomain($domain) {
	$domain = str_replace(array("http://","www."),"", $domain);
	$domain_2 = explode("/", $domain);
	$domain = $domain_2[0];
	return $domain;
	}
	function isValidDomain($value) {
	$value = trim($value);
	$validhost = true;
	if (strpos($value, 'http://') === false && strpos($value, 'https://') === false) { $value = 'http://'.$value; }
	if (filter_var($value, FILTER_VALIDATE_URL) === false) { $validhost = false; } else { $host = parse_url($value, PHP_URL_HOST);
	$dotcount = substr_count($host, '.');
	if ($dotcount > 0) {
	if ($dotcount == 1) {
	if (strpos($host, 'www.') === 0) { $validhost = false; }
	} else { if (strpos($host, '..') !== false) { $validhost = false; } }
	} else { $validhost = false; } 	}
	return $validhost;
	}

}
?>