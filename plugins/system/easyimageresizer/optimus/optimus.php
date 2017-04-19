<?php
/**
 * @Copyright
 * @package     EIR - Easy Image Resizer for Joomla! 3.x
 * @author      Viktor Vogel <admin@kubik-rubik.de>
 * @version     3.4.2 - 2016-05-17
 * @link        https://joomla-extensions.kubik-rubik.de/eir-easy-image-resizer
 *
 * Modified PHP Library for the Optimus API
 *
 * optimus.io - Lossless compression and optimization of your images
 *
 * @author      KeyCDN
 * @version     0.1
 */
defined('JPATH_PLATFORM') or die;
use Joomla\Registry\Registry;

/**
 * Class Optimus
 */
class Optimus
{
	/**
	 * @var string
	 */
	private $api_key;

	/**
	 * @var string
	 */
	private $end_point;

	/**
	 * @param string $api_key
	 */
	public function __construct($api_key)
	{
		$this->api_key = $api_key;
		$this->end_point = 'http://magic.optimus.io';

		if(!empty($api_key))
		{
			$this->end_point = 'https://magic.optimus.io';
		}
	}

	/**
	 * Calculates the maximum request quota to avoid unnecessary requests
	 *
	 * @return int
	 */
	public function getRequestQuota()
	{
		$quota = 100 * 1024;

		if(!empty($this->api_key))
		{
			$quota = 5000 * 1024;
		}

		return $quota;
	}

	/**
	 * Does the main request to optimus.io
	 *
	 * @param string $image_path
	 * @param string $option
	 *
	 * @return bool|string
	 */
	public function optimize($image_path, $option = 'optimize')
	{
		// First check whether cURL is activated
		if(JHttpFactory::getAvailableDriver(new Registry, 'curl') == false OR empty($image_path))
		{
			return false;
		}

		$end_point = $this->end_point.'/'.$this->api_key.'?'.$option;
		$version = new JVersion();
		$host = JUri::getInstance()->getHost();

		if(!preg_match('@http.?://@', $host))
		{
			$host = 'http://'.$host;
		}

		$user_agent = 'Joomla/'.$version->getShortVersion().'; '.$host;
		$headers = array('User-Agent: '.$user_agent, 'Accept: image/*');

		// Use native cURL implementation because not all needed options can be set through the Joomla! API
		$ch = curl_init();
		curl_setopt_array($ch, array(CURLOPT_URL => $end_point, CURLOPT_HTTPHEADER => $headers, CURLOPT_POSTFIELDS => file_get_contents($image_path), CURLOPT_BINARYTRANSFER => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_SSL_VERIFYPEER => true));

		$response = curl_exec($ch);
		$curlError = curl_error($ch);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$body = substr($response, $header_size);

		if(!empty($curlError) OR empty($body))
		{
			return false;
		}

		return $body;
	}
}
