<?php
/**
 * Implements sourcing thumbnails from http://www.shrinktheweb.com
 *
 * Dependent on PHP5, but could be easily back-ported.  All config
 * information is defined in constants.  No reason to ever create
 * an instance of this class, hence abstract.
 *
 * @author Entraspan, Based in part on STW sample code
 * @copyright Open Source/Creative Commons
 */

class AppSTW {
    const THUMBNAIL_URI = "/images";
    const THUMBNAIL_DIR = "/static";
    const CACHE_DAYS = 3; // used 7 for Alexa!

	var $thumbDir;
	var $thumbUri;
	
	function AppSTW()
	{
		$upload_dir = wp_upload_dir();
		$this->thumbDir = $upload_dir['basedir']."/webimages"; 
		if(!file_exists($this->thumbDir))
		{
			$old = umask(0); 
			mkdir($this->thumbDir);
			umask($old);
		}
		$this->thumbUri = $upload_dir['baseurl']."/webimages";
	}
	
    private function make_http_request($url){
        $lines = file($url);
        return implode("", $lines);
    }

    /**
     * Calls through the API and processes the results based on the
     * original sample code from STW.  This function is public for
     * example only.  It really should not be used since thumbnails
     * should be cached locally using getThumbnail.
     *
     * It is common for this routine to return a null value when the
     * thumbnail does not yet exist and is queued up for processing.
     *
     * @param string $url URL to get thumbnail for
     * @param array $args Array of parameters to use
     * @return string full remote URL to the thumbnail
     */
    public function queryRemoteThumbnail($url, $args = null, $debug = false) {
        $args = is_array($args) ? $args : array();
        $defaults["stwaccesskeyid"] = get_option('wpss_stw_access');
        $defaults["stwu"] = get_option('wpss_stw_secret');

        foreach ($defaults as $k=>$v)
            if (!isset($args[$k]))
                $args[$k] = $v;

		$args["stwurl"] = $url;

        $request_url = "http://images.shrinktheweb.com/xino.php?".http_build_query($args);

        $line = self::make_http_request($request_url);

        if ($debug) {
            echo '<pre style=font-size:10px>';
            unset($args["stwaccesskeyid"]);
            unset($args["stwu"]);
            print_r($args);
            echo '</pre>';
            echo '<div style=font-size:10px>';
            highlight_string($line);
            echo '</div>';
        }

        $regex = '/<[^:]*:Thumbnail\\s*(?:Exists=\"((?:true)|(?:false))\")?[^>]*>([^<]*)<\//';

        if (preg_match($regex, $line, $matches) == 1 && $matches[1] == "true")
            return $matches[2];

        return null;
    }

    /**
     * Refreshes the thumbnail if it is expired or creates it if it does
     * not exist.  There is no cleanup of the thumbnails for ones that don't
     * get used again, e.g. find /static/images/thumbnails -type f -mtime +7 -delete
     *
     * Every combination of url and call arguments results in a unique filename
     * through a MD5 hash.  The size argument can also be an array where you can
     * add any parameter you wish to the request, or override any default.
     *
     * It is up to the calling function to decide what to do with the results when
     * a null is returned.  I often store the src in a database with a timestamp so
     * that I do not bombard the server with repeated requests for a thumbnail that
     * doesn't yet exist, although STW is very fast at processing.
     *
     * @param string $url URL to get thumbnail for
     * @param array $args Array of parameters to use
     * @param boolean $force Force call to bypass cache, was used for debugging
     * @return string Local SRC URI for the thumbnail.
     */
    public function getThumbnail($url, $args = null, $force = false) {
        $args = $args ? $args : array("stwsize"=>"lg");
        $name = md5($url.serialize($args)).".jpg";
        $src = "/$name";
        $path = $this->thumbDir.$src;
        $cutoff = time() - 3600 * 24 * self::CACHE_DAYS;

        if ($force || !file_exists($path) || filemtime($path) <= $cutoff)
            if (($jpgurl = $this->queryRemoteThumbnail($url, $args)))
                if (($im = imagecreatefromjpeg($jpgurl)))
                    imagejpeg($im, $path, 100);

        if (file_exists($path))
            return $src;

        return null;
    }

    /**
     * Always retrieves the X-Large thumbnail from STW, then uses
     * local gd library to create arbitrary sized thumbnails.
     *
     * By passing the same arguments used for small/large should
     * generate cache hits so the only size every retrieved would
     * be xlg.
     *
     * @param string $url URL to get thumbnail for
     * @param string $width The desired image width
     * @param string $height The desired image height
     * @param string $args Used to make name same as sm/lg fetches.
     */
    public function getScaledThumbnail($url, $width, $height, $args = null, $force) {
        $args = $args ? $args : array("width"=>$width, "height"=>$height);
        $name = md5($url.serialize($args)).".jpg";
        $src = "/$name";
        $path = $this->thumbDir.$src;
        $cutoff = time() - 3600 * 24 * self::CACHE_DAYS;

        if ($force || !file_exists($path) || filemtime($path) <= $cutoff)
            if (($xlg = self::getXLargeThumbnail($url)))
                if (($im = imagecreatefromjpeg($this->thumbDir.$xlg))) {
                    list($xw, $xh) = getimagesize($this->thumbDir.$xlg);
                    $scaled = imagecreatetruecolor($width, $height);

                    if (imagecopyresampled($scaled, $im, 0, 0, 0, 0, $width, $height, $xw, $xh))
                        imagejpeg($scaled, $path, 100);
                }

        if (file_exists($path))
            return $src;

        return null;
    }

    /**
     * Convenience Function for 320x240
     *
     * @param string $url URL to get thumbnail for
     */
    public function getXLargeThumbnail($url) {
        return $this->getThumbnail($url, array("stwsize"=>"xlg"));
    }

    /**
     * Convenience Function for 200x150
     *
     * @param string $url URL to get thumbnail for
     * @param boolean $scaler Scale image from xlg
     */
    public function getLargeThumbnail($url, $scaler = true, $force = false) {
        if ($scaler)
            return $this->getScaledThumbnail($url, 200, 150, array("stwsize"=>"lg"), $force);

        return $this->getThumbnail($url);
    }

    /**
     * Convenience Function for 120x90
     *
     * @param string $url URL to get thumbnail for
     * @param boolean $scaler Scale image from xlg
     */
    public function getSmallThumbnail($url, $scaler = true, $force = false) {
        if ($scaler)
            return $this->getScaledThumbnail($url, 120, 90, array("stwsize"=>"sm"), $force);

        return $this->getThumbnail($url, array("stwsize"=>"sm"));
    }
}

?>