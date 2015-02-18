<?php
class Page
{
	private $webUrl = 'http://thunderbird.mozilla.cz/';
	private $webName = 'Používejte Thunderbird';

	private $title = 'Mozilla Thunderbird';
	private $description;
	private $keywords;

	private $incPath;

	public function __construct()
	{
		error_reporting(E_ALL);
		$this->incPath = dirname(__FILE__);
	}

	public function setTitle($title, $prepend = true) {
		$this->title = $title . ( $prepend ? ' - ' . $this->webName : '' );
		return $this;
	}

	public function getTitle() {
		return $this->title;
	}

	public function setDescription($description) {
		$this->description = $description;
		return $this;
	}

	public function getDescription() {
		return $this->description;
	}

	public function setKeywords($keywords) {
		$this->keywords = $keywords;
		return $this;
	}

	public function getKeywords() {
		return $this->keywords;
	}

	public function getWebUrl() {
		return $this->webUrl;
	}

	public function getWebName() {
		return $this->webName;
	}

	public function getMeta() {
		$meta = array();
		if(!empty($this->description) && $this->description != 'XXX')  {
			$meta['description'] = $this->description;
		}
		if(!empty($this->keywords) && $this->keywords != 'XXX')  {
			$meta['keywords'] = $this->keywords;
		}
		return $meta;
	}

	public function includeTemplate($name, $variables = null)
	{
		if(!empty($variables) && is_array($variables)) {
			extract($variables);
		}
		require $this->incPath . '/tpl/' . $name . '.php';
	}

	public function getDownload($product)
	{
		include_once $this->incPath . '/config.php';
		require $this->incPath . '/download.php';
		return new Download($product);
	}
}

$page = new Page();
