<?php
/**
 * @Copyright
 * @package     SIGE - Simple Image Gallery Extended for Joomla! 3.x
 * @author      Viktor Vogel <admin@kubik-rubik.de>
 * @version     3.2.3 - 2017-01-29
 * @link        https://joomla-extensions.kubik-rubik.de/sige-simple-image-gallery-extended
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

// Fast check whether image url variable was transmitted
if($_GET['img'] == '')
{
	exit('No parameters!');
}

class TempThumbnailCreation
{
	protected $image;
	protected $imagedata;
	protected $image_extension;
	protected $width = 300;
	protected $height = 300;
	protected $width_new = 300;
	protected $height_new = 300;
	protected $width_original = 300;
	protected $height_original = 300;
	protected $quality = 80;
	protected $ratio = 1;
	protected $crop = 0;
	protected $crop_factor = 50;
	protected $crop_percent = 100;
	protected $crop_width = 300;
	protected $crop_height = 300;
	protected $thumbdetail = 0;
	protected $allowed_extensions = array('jpg', 'png', 'gif');
	protected $x_coordinate = 0;
	protected $y_coordinate = 0;

	public function __construct()
	{
		if($this->loadImageData())
		{
			$this->createThumbnail();
		}
	}

	/**
	 * Prepares the data for the thumbnail creation
	 *
	 * @return bool
	 */
	private function loadImageData()
	{
		$_GET['img'] = str_replace('..', '', htmlspecialchars(urldecode($_GET['img'])));
		$this->image = '../../../..'.$_GET['img'];

		if(empty($this->image) OR !file_exists($this->image))
		{
			return false;
		}

		$this->validateRequestValue($this->width, 'width');
		$this->validateRequestValue($this->height, 'height');
		$this->validateRequestValue($this->quality, 'quality');
		$this->validateRequestValue($this->ratio, 'ratio');
		$this->validateRequestValue($this->crop, 'crop');
		$this->validateRequestValue($this->thumbdetail, 'thumbdetail');

		$this->image_extension = strtolower(substr($_GET['img'], -3));

		if(!in_array($this->image_extension, $this->allowed_extensions))
		{
			return false;
		}

		$this->imagedata = getimagesize($this->image);

		if(empty($this->imagedata[0]) OR empty($this->imagedata[1]))
		{
			return false;
		}

		$this->width_original = $this->imagedata[0];
		$this->height_original = $this->imagedata[1];

		$this->width_new = $this->width;
		$this->height_new = $this->height;

		$this->ratioCheck();
		$this->cropImage();

		return true;
	}

	/**
	 * Validates the request variables
	 *
	 * @param $variable
	 * @param $value
	 */
	private function validateRequestValue(&$variable, $value)
	{
		if(isset($_GET[$value]))
		{
			$variable = intval(htmlspecialchars($_GET[$value]));
		}
	}

	/**
	 * Checks the ratio of the image
	 */
	private function ratioCheck()
	{
		if($this->ratio)
		{
			$this->height_new = (int)($this->imagedata[1] * ($this->width_new / $this->imagedata[0]));

			if($this->height AND ($this->height_new > $this->height))
			{
				$this->height_new = $this->height;
				$this->width_new = (int)($this->imagedata[0] * ($this->height_new / $this->imagedata[1]));
			}
		}
	}

	/**
	 * Crops the image if crop option is activated
	 */
	private function cropImage()
	{
		if($this->crop AND ($this->crop_factor > 0 AND $this->crop_factor < 100))
		{
			$biggest_side = $this->height_original;

			if($this->width_original > $this->height_original)
			{
				$biggest_side = $this->width_original;
			}

			$this->crop_percent = (1 - ($this->crop_factor / 100));
			$this->crop_width = $this->width_original * $this->crop_percent;
			$this->crop_height = $this->height_original * $this->crop_percent;

			if(!$this->ratio AND ($this->width == $this->height))
			{
				$this->crop_width = $biggest_side * $this->crop_percent;
				$this->crop_height = $biggest_side * $this->crop_percent;
			}
			elseif(!$this->ratio AND ($this->width != $this->height))
			{
				$this->crop_width = ($this->width * ($this->height_original / $this->height)) * $this->crop_percent;
				$this->crop_height = $this->height_original * $this->crop_percent;

				if(($this->width_original / $this->width) < ($this->height_original / $this->height))
				{
					$this->crop_width = $this->width_original * $this->crop_percent;
					$this->crop_height = ($this->height * ($this->width_original / $this->width)) * $this->crop_percent;
				}
			}

			$this->x_coordinate = ($this->width_original - $this->crop_width) / 2;
			$this->y_coordinate = ($this->height_original - $this->crop_height) / 2;
		}
	}

	/**
	 * Created the thumbnail output
	 */
	private function createThumbnail()
	{
		if($this->image_extension == 'jpg')
		{
			header('Content-type: image/jpg');
			$src_img = imagecreatefromjpeg($this->image);
			$dst_img = imagecreatetruecolor($this->width_new, $this->height_new);

			$this->resizeImage($dst_img, $src_img);

			imagejpeg($dst_img, null, $this->quality);

			if(is_resource($src_img))
			{
				imagedestroy($src_img);
			}

			if(is_resource($dst_img))
			{
				imagedestroy($dst_img);
			}

			return;
		}

		if($this->image_extension == 'gif')
		{
			header('Content-type: image/gif');
			$src_img = imagecreatefromgif($this->image);
			$dst_img = imagecreatetruecolor($this->width_new, $this->height_new);
			imagepalettecopy($dst_img, $src_img);

			$this->resizeImage($dst_img, $src_img);

			imagegif($dst_img);

			if(is_resource($src_img))
			{
				imagedestroy($src_img);
			}

			if(is_resource($dst_img))
			{
				imagedestroy($dst_img);
			}

			return;
		}

		if($this->image_extension == 'png')
		{
			header('Content-type: image/png');
			$src_img = imagecreatefrompng($this->image);
			$dst_img = imagecreatetruecolor($this->width_new, $this->height_new);
			imagepalettecopy($dst_img, $src_img);

			$this->resizeImage($dst_img, $src_img);

			imagepng($dst_img, null, 6);

			if(is_resource($src_img))
			{
				imagedestroy($src_img);
			}

			if(is_resource($dst_img))
			{
				imagedestroy($dst_img);
			}

			return;
		}
	}

	/**
	 * Resizes the image depending on selected parameters
	 *
	 * @param $dst_img
	 * @param $src_img
	 */
	private function resizeImage(&$dst_img, $src_img)
	{
		if($this->crop AND ($this->crop_factor > 0 AND $this->crop_factor < 100))
		{
			imagecopyresampled($dst_img, $src_img, 0, 0, $this->x_coordinate, $this->y_coordinate, $this->width_new, $this->height_new, $this->crop_width, $this->crop_height);

			return;
		}

		if($this->thumbdetail == 1)
		{
			imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $this->width_new, $this->height_new, $this->width_new, $this->height_new);
		}
		elseif($this->thumbdetail == 2)
		{
			imagecopyresampled($dst_img, $src_img, 0, 0, $this->width_original - $this->width_new, 0, $this->width_new, $this->height_new, $this->width_new, $this->height_new);
		}
		elseif($this->thumbdetail == 3)
		{
			imagecopyresampled($dst_img, $src_img, 0, 0, 0, $this->height_original - $this->height_new, $this->width_new, $this->height_new, $this->width_new, $this->height_new);
		}
		elseif($this->thumbdetail == 4)
		{
			imagecopyresampled($dst_img, $src_img, 0, 0, $this->width_original - $this->width_new, $this->height_original - $this->height_new, $this->width_new, $this->height_new, $this->width_new, $this->height_new);
		}
		else
		{
			imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $this->width_new, $this->height_new, $this->width_original, $this->height_original);
		}

		return;
	}
}

new TempThumbnailCreation();