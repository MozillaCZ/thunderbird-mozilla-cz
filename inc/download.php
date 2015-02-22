<?php
class Download
{
	private $mysqlConfig;
	private $platform;
	private $product;
	private $data = array();

	public function __construct($mysqlConfig, $product)
	{
		$this->mysqlConfig = $mysqlConfig;
		$this->product = $product;
		$this->loadData();
	}

	private function loadData()
	{
		$link = mysql_connect($this->mysqlConfig['host'], $this->mysqlConfig['user'], $this->mysqlConfig['pass']);
		if (!$link) {
			return false;
		}
		$db = mysql_select_db($this->mysqlConfig['db'], $link);
		if ($db) {
			$query = mysql_query("SELECT * FROM mozsk_produkty WHERE urlid='" . $this->product . "' ORDER BY datum DESC LIMIT 1");
			if ($query) {
				$this->data = mysql_fetch_assoc($query);
			}
		}
		mysql_close($link);
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
