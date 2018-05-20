<? 

class DNS
{
	public $domain;
	private $token;
	private $ttl;
	private $ip;
	public function __construct($domain, $token, $ip, $ttl = '14400')
	{
		$this->domain = $domain;
		$this->token = $token;
		$this->ttl = $ttl;
		$this->ip = $ip;
	}

	private function curl_request ($method, $type, $fields)
	{
        $curl = curl_init();
 
        if($curl)
        {
                curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
                curl_setopt($curl, CURLOPT_URL, 'https://pddimp.yandex.ru/api2/admin/dns/'.$method.($type == 'get' ? '?'.http_build_query($fields) : ''));
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_TIMEOUT, 5);
                curl_setopt($curl, CURLOPT_USERAGENT, 'DocForce.ru RegService');
                if ($type == 'post')
                {
                        curl_setopt($curl, CURLOPT_POST, true);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
                }
               
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('PddToken: '.$this->token));
                $out = curl_exec($curl);
                curl_close($curl);
                return (empty ($out)) ? false : $out;
        } else
                return false;
	}
 
	public function get_list($type = '') {
		$r = json_decode($this->curl_request ('list', 'get', ['domain' => $this->domain]));
		$m = [];
		foreach ($r->records as $k) {
			if($type == '') { $m[$k->record_id] = $k->subdomain; } else {
				if($k->type==$type) { $m[$k->record_id] = $k->subdomain; }
			}
		}
		return $m;
	}

	public function add($subdomain) {
		if (in_array($subdomain, $this->get_list())) { return false; } 
		else {
			
			return json_decode($this->curl_request('add', 'post', ['domain' => $this->domain, 'subdomain' => $subdomain, 'type' => 'A', 'content' => $this->ip, 'ttl' => $this->ttl]))->record->record_id;
		}
	}

	public function delete($id) {
		if (!array_key_exists($id, $this->get_list())) { return false; } 
		else {
			$this->curl_request('del', 'post', ['domain' =>$this->domain, 'record_id' => $id]);
			return true;
		}
	}

	public function edit($id, $subdomain) {
		if (!array_key_exists($id, $this->get_list())||in_array($subdomain, $this->get_list())) { return false; } 
		else {
			
			return $this->curl_request('edit', 'post', ['domain' => $this->domain, 'record_id' => $id, 'subdomain' => $subdomain, 'type' => 'A', 'content' => $this->ip, 'ttl' => $this->ttl]);
		}
	}
}

$dns = new DNS('domain.com', 'YANDEX_OAUTH_API_KEY', 'IP_ADDRESS');
$dns->add('demo1');
?>