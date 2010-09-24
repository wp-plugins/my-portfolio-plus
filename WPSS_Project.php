<?
class WPSS_Project
{
	private $clientName;
	private $url;
	private $date;
	private $thumbGen;
	private $pluginUrl;
	
	function WPSS_Project($clientName, $url, $date)
	{
		$this->clientName = $clientName;
		$this->url = $url;
		$this->date = $date;
		$this->thumbGen = new AppSTW();
		$this->pluginUrl = "/wp-content/plugins/my-portfolio-plus";
	}
	
	public static function getProject($projectID)
	{
		$custom = get_post_custom($projectID);
		$project = new WPSS_Project($custom["sugar-clientname"][0], $custom["sugar-url"][0], $custom["sugar-date"][0]);
		return $project;
	}
	
	/*Accessors*/
	public function getClient()
	{
		return $this->clientName;
	}
	
	public function getURL()
	{
		return $this->url;
	}
	
	public function getDate()
	{
		return date("jS F Y", strtotime($this->date));
	}
	
	public function getImage()
	{	
		$imageSrc = $this->thumbGen->getXLargeThumbnail($this->url);
		if($imageSrc != null)
			return $this->thumbGen->thumbUri.$imageSrc;
		else
			return $this->pluginUrl."/img/noimage.png";
	}
	
	
}