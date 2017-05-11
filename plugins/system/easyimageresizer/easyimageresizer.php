<?php
/**
 * @Copyright
 * @package     EIR - Easy Image Resizer for Joomla! 3.x
 * @author      Viktor Vogel <admin@kubik-rubik.de>
 * @version     3.4.2 - 2016-05-17
 * @link        https://joomla-extensions.kubik-rubik.de/eir-easy-image-resizer
 *
 * @license     GNU/GPL
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_JEXEC') or die('Restricted access');

class PlgSystemEasyImageResizer extends JPlugin
{
	protected $quality_jpg;
	protected $compression_png;
	protected $scale_method;
	protected $multisize_path;
	protected $enlarge_images;
	protected $optimus_object;
	protected $optimus_quota;
	protected $optimus_mime_types;
	protected $allowed_mime_types = array('image/jpeg', 'image/png', 'image/gif');

	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	/**
	 * Optimizes generic image uploads and makes image names safe in the trigger onAfterInitialise()
	 */
	public function onAfterInitialise()
	{
		if($this->params->get('optimus', 1) AND $this->params->get('optimus_uploads', 1))
		{
			$this->uploadImagesOptimus();
		}

		if($this->params->get('safe_names', 1))
		{
			$this->makeNameSafe();
		}
	}

	/**
	 * Optimizes images in generic image upload processes with Optimus.io
	 */
	private function uploadImagesOptimus()
	{
		$input_files = new JInputFiles();
		$input_keys = array_keys($_FILES);

		if(!empty($input_keys))
		{
			foreach($input_keys as $input_key)
			{
				$file_data = $input_files->get($input_key, array(), 'raw');

				if(empty($file_data[0]))
				{
					$file_data = array($file_data);
				}

				foreach($file_data as $file)
				{
					if(!empty($file['tmp_name']) AND !empty($file['type']))
					{
						$size = false;

						if(!empty($file['size']))
						{
							$size = $file['size'];
						}

						$this->initialiseOptimus();
						$this->optimizeImageOptimus($file['tmp_name'], $file['type'], $size);
					}
				}
			}
		}
	}

	/**
	 * Initialises the service Optimus.io for lossless compression of images
	 */
	private function initialiseOptimus()
	{
		if(empty($this->optimus_object))
		{
			require_once JPATH_PLUGINS.'/system/easyimageresizer/optimus/optimus.php';
			$optimus_api_key = $this->params->get('optimus_api_key', '');
			$this->optimus_object = new Optimus($optimus_api_key);
			$this->optimus_quota = $this->optimus_object->getRequestQuota();
			$this->optimus_mime_types = array('image/jpeg', 'image/png');
		}
	}

	/**
	 * Optimizes the images with Optimus
	 *
	 * @param $image_path
	 * @param $mime_type
	 */
	private function optimizeImageOptimus($image_path, $mime_type, $file_size = false)
	{
		jimport('joomla.filesystem.file');

		if(JFile::exists($image_path))
		{
			if(empty($file_size))
			{
				$file_size = filesize($image_path);
			}

			if(is_int($file_size) AND $this->optimus_quota >= $file_size)
			{
				if(in_array($mime_type, $this->optimus_mime_types))
				{
					$result = $this->optimus_object->optimize($image_path);

					if(!empty($result))
					{
						file_put_contents($image_path, $result);
					}
				}
			}
		}
	}

	/**
	 * Creates safe image names for the Media Manager
	 *
	 * @throws Exception
	 */
	private function makeNameSafe()
	{
		$input = JFactory::getApplication()->input;

		if($input->get('option') == 'com_media' AND $input->get('task') == 'file.upload')
		{
			$input_files = new JInputFiles();
			$file_data = $input_files->get('Filedata', array(), 'raw');

			foreach($file_data as $key => $file)
			{
				if(!empty($file['name']))
				{
					// UTF8 to ASCII
					$file['name'] = JLanguageTransliterate::utf8_latin_to_ascii($file['name']);

					// Make image name safe with core function
					jimport('joomla.filesystem.file');
					$file['name'] = JFile::makeSafe($file['name']);

					// Replace whitespaces with underscores
					$file['name'] = preg_replace('@\s+@', '-', $file['name']);

					// Make a string lowercase
					$file['name'] = strtolower($file['name']);

					// Set the name back directly to the global FILES variable
					$_FILES['Filedata']['name'][$key] = $file['name'];
				}
			}
		}
	}

	/**
	 * Plugin uses the trigger onContentBeforeSave to manipulate the uploaded images
	 *
	 * @param string  $context
	 * @param object  $object
	 * @param boolean $state
	 */
	public function onContentBeforeSave($context, $object, $state)
	{
		if($context == 'com_media.file' AND (!empty($object) AND is_object($object)) AND $state == true)
		{
			$this->setQualityJpg();
			$this->setCompressionPng();
			$this->scale_method = (int)$this->params->get('scale_method', 2);
			$this->enlarge_images = (int)$this->params->get('enlarge_images', 0);
			$width = (int)$this->params->get('width', 0);
			$height = (int)$this->params->get('height', 0);

			// At least one value has to be set and not negative to execute the resizing process
			if((!empty($width) AND $width >= 0) OR (!empty($height) AND $height >= 0))
			{
				$this->resizeImage($object, $object->tmp_name, $width, $height, false);
			}
		}
	}

	/**
	 * Sets the quality of JPG images - 0 to 100
	 */
	private function setQualityJpg()
	{
		$this->quality_jpg = (int)$this->params->get('quality_jpg', 80);

		// Set default value if entered value is out of range
		if($this->quality_jpg < 0 OR $this->quality_jpg > 100)
		{
			$this->quality_jpg = 80;
		}
	}

	/**
	 * Sets the compression level of PNG images - 0 to 9
	 */
	private function setCompressionPng()
	{
		$this->compression_png = (int)$this->params->get('compression_png', 6);

		// Set default value if entered value is out of range
		if($this->compression_png < 0 OR $this->compression_png > 9)
		{
			$this->compression_png = 6;
		}
	}

	/**
	 * Resizes images using Joomla! core class JImage
	 *
	 * @param object $object
	 * @param string $object_path
	 * @param int    $width
	 * @param int    $height
	 * @param bool   $multiresize
	 *
	 * @return string|string
	 */
	private function resizeImage($object, $object_path, $width = 0, $height = 0, $multiresize = true)
	{
		if(in_array($object->type, $this->allowed_mime_types))
		{
			$image_object = new JImage($object_path);
			$image_object->resize($width, $height, false, $this->scale_method);

			if(empty($this->enlarge_images))
			{
				$image_properties = $image_object->getImageFileProperties($object_path);

				if($image_object->getWidth() >= $image_properties->width OR $image_object->getHeight() >= $image_properties->height)
				{
					return false;
				}
			}

			$image_save_path = ($multiresize ? $this->getThumbnailPath($object_path, $image_object->getWidth(), $image_object->getHeight()) : $object_path);
			$image_information = $this->getImageInformation($object->type);
			$image_object->toFile($image_save_path, $image_information['type'], array('quality' => $image_information['quality']));

			return $image_save_path;
		}

		return false;
	}

	/**
	 * Creates the full path, including the name of the thumbnail
	 *
	 * @param $image_path_original
	 * @param $width
	 * @param $height
	 *
	 * @return string
	 */
	private function getThumbnailPath($image_path_original, $width, $height)
	{
		$image_extension = '.'.JFile::getExt(basename($image_path_original));
		$image_name_original = basename($image_path_original, $image_extension);

		return $this->multisize_path.'/'.$image_name_original.'-'.$width.'x'.$height.$image_extension;
	}

	/**
	 * Gets needed information for the image manipulating process
	 *
	 * @param string $mime_type
	 *
	 * @return array
	 */
	private function getImageInformation($mime_type)
	{
		$image_information = array('type' => IMAGETYPE_JPEG, 'quality' => $this->quality_jpg);

		if($mime_type == 'image/gif')
		{
			$image_information = array('type' => IMAGETYPE_GIF, 'quality' => '');
		}
		elseif($mime_type == 'image/png')
		{
			$image_information = array('type' => IMAGETYPE_PNG, 'quality' => $this->compression_png);
		}

		return $image_information;
	}

	/**
	 * Plugin uses the trigger onContentAfterSave to manipulate the multisize images
	 *
	 * @param string  $context
	 * @param object  $object
	 * @param boolean $state
	 */
	public function onContentAfterSave($context, $object, $state)
	{
		if($context == 'com_media.file' AND (!empty($object) AND is_object($object)) AND $state == true)
		{
			// Optimus.io implementation
			$optimus = $this->params->get('optimus');

			if(!empty($optimus))
			{
				$this->initialiseOptimus();
				$this->optimizeImageOptimus($object->filepath, $object->type);
			}

			$multisizes = $this->params->get('multisizes');

			if(!empty($multisizes))
			{
				$multisizes_lines = array();
				$this->createThumbnailFolder($object->filepath);
				$multisizes = array_map('trim', explode("\n", $multisizes));

				foreach($multisizes as $multisizes_line)
				{
					$multisizes_lines[] = array_map('trim', explode('|', $multisizes_line));
				}

				foreach($multisizes_lines as $multisize_line)
				{
					// At least one value has to be set and not negative to execute the resizing process
					if((!empty($multisize_line[0]) AND $multisize_line[0] >= 0) OR (!empty($multisize_line[1]) AND $multisize_line[1] >= 0))
					{
						$image_path = $this->resizeImage($object, $object->filepath, $multisize_line[0], $multisize_line[1], true);

						if(!empty($optimus) AND !empty($image_path))
						{
							$this->optimizeImageOptimus($image_path, $object->type);
						}
					}
				}
			}
		}
	}

	/**
	 * Creates thumbnail folder if it does not exist yet
	 *
	 * @param $image_path_original
	 */
	private function createThumbnailFolder($image_path_original)
	{
		$this->multisize_path = dirname($image_path_original);
		$multisize_path = $this->params->get('multisize_path', '');

		if(!empty($multisize_path))
		{
			$this->multisize_path .= '/'.$multisize_path;
		}

		if(!JFolder::exists($this->multisize_path))
		{
			JFolder::create($this->multisize_path);
		}
	}
}
