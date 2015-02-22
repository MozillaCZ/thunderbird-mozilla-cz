<?php
class Download
{
	private $platform;
	private $product;
	private $products = array(
		'firefox' => array(
			'name' => 'Mozilla Firefox',
			'json_url' => 'https://www.mozilla.org/includes/product-details/json/firefox_versions.json',
			'key' => 'LATEST_FIREFOX_VERSION',
			'download_url' => 'https://download.mozilla.org/?product=firefox-%1$s&os=%2$s&lang=cs',
			'changelog_url' => 'https://www.mozilla.org/cs/firefox/%s/releasenotes/'
		),
		'thunderbird' => array(
			'name' => 'Mozilla Thunderbird',
			'json_url' => 'https://www.mozilla.org/includes/product-details/json/thunderbird_versions.json',
			'key' => 'LATEST_THUNDERBIRD_VERSION',
			'download_url' => 'https://download.mozilla.org/?product=thunderbird-%1$s&os=%2$s&lang=cs',
			'changelog_url' => 'https://www.mozilla.org/cs/thunderbird/%s/releasenotes/'
		)
	);
	private $data = array();

	public function __construct($product)
	{
		$this->product = $product;
		$this->loadData();
	}

	private function substring_in_array($substring, $array) {
		foreach ($array as $value) {
			if (strpos($value, $substring) !== false) {
				return true;
			}
		}
		return false;
	}

	private function loadData()
	{
		$in_cache = CACHE_DIR.basename($this->products[$this->product]['json_url']);
		if(is_file($in_cache) && time()-filemtime($in_cache)<CACHE_EXPIRE) {
			$json = file_get_contents($in_cache);
		} else {
			$json = file_get_contents($this->products[$this->product]['json_url']);
			if(!$this->substring_in_array(' 200 OK', $http_response_header)) {
				if(is_file($in_cache)) {
					$json = file_get_contents($in_cache);
				} else {
					$json = null;
				}
			} else {
				if(!file_exists(CACHE_DIR)) {
					mkdir(CACHE_DIR);
				}
				file_put_contents($in_cache, $json);
			}
		}
		$this->data['nazov'] = $this->products[$this->product]['name'];
		$json_array = json_decode($json, true);
		$this->data['verzia'] = $json_array[$this->products[$this->product]['key']];
		$this->data['download_win'] = sprintf($this->products[$this->product]['download_url'], $this->data['verzia'], 'win');
		$this->data['download_lin'] = sprintf($this->products[$this->product]['download_url'], $this->data['verzia'], 'linux');
		$this->data['download_mac'] = sprintf($this->products[$this->product]['download_url'], $this->data['verzia'], 'osx');
		$this->data['changelog'] = sprintf($this->products[$this->product]['changelog_url'], $this->data['verzia']);
	}

	public function getPlatform()
	{
		if(isset($this->platform)) {
			return $this->platform;
		}

		$uaString = isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : "";
		if(stristr($uaString, "Linux") || stristr($uaString, "X11") || stristr($uaString, "Lindows")) {
			return $this->platform = "lin";
		}

		if(stristr($uaString, "Mac")) {
			return $this->platform = "mac";
		}

		return $this->platform = "win";
	}

	public function getPlatformName()
	{
		switch($this->getPlatform()) {
			case 'lin':
				return 'Linux';

			case 'mac':
				return 'Mac OS';

			default:
				return 'Windows';
		}
	}

	public function getDownloadLink($platform = null)
	{
		if($platform == null) {
			$platform = $this->getPlatform();
		}
		$key = 'download_' . $platform;
		if (empty($this->data[$key])) {
			return 'http://www.mozilla.org/cs/' . $this->product;
		}
		return str_replace("&", "&amp;", $this->data[$key]);
	}

	public function getChangelogLink()
	{
		if (empty($this->data['changelog'])) {
			return 'http://www.mozilla.org/cs/' . $this->product;
		}
		return $this->data['changelog'];
	}

	public function getVersion()
	{
		if (empty($this->data['verzia'])) {
			return '';
		}
		return $this->data['verzia'];
	}

	public function getTrackingLabel($platform = null)
	{
		if($platform == null) {
			$platform = $this->getPlatform();
		}
		return $this->product . ' ' . $platform . ' ' . $this->getVersion();
	}
}
