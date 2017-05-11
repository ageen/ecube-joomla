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
defined('_JEXEC') or die('Restricted access');

class PlgContentSige extends JPlugin
{
	protected $session;
	protected $absolute_path;
	protected $live_site;
	protected $images_dir;
	protected $thumbnail_max_height;
	protected $thumbnail_max_width;
	protected $turbo_html_read_in;
	protected $turbo_css_read_in;
	protected $sigcount;
	protected $sigcountarticles;
	protected $article_title = '';
	protected $root_folder = '/images/';
	protected $syntax_parameters = array();
	protected $plugin_parameters = array();
	protected $allowed_extensions = array('jpg', 'png', 'gif');

	public function __construct(&$subject, $config)
	{
		if(JFactory::getApplication()->isAdmin())
		{
			return;
		}

		parent::__construct($subject, $config);
		$this->loadLanguage('plg_content_sige', JPATH_ADMINISTRATOR);

		$version = new JVersion();
		$joomla_main_version = substr($version->RELEASE, 0, strpos($version->RELEASE, '.'));

		if($joomla_main_version != '3')
		{
			throw new Exception(JText::_('PLG_SIGE_NEEDJ3'), 404);
		}

		$this->session = JFactory::getSession();
		$this->session->clear('sigcount', 'sige');
		$this->session->clear('sigcountarticles', 'sige');

		$this->absolute_path = JPATH_SITE;
		$this->live_site = JURI::base();

		if(substr($this->live_site, -1) == '/')
		{
			$this->live_site = substr($this->live_site, 0, -1);
		}
	}

	/**
	 * Entry point of the plugin in core content trigger onContentPrepare
	 *
	 * @param $context
	 * @param $article
	 * @param $params
	 * @param $limitstart
	 */
	public function onContentPrepare($context, &$article, &$params, $limitstart)
	{
		if(stripos($article->text, '{gallery}') === false)
		{
			return;
		}

		$this->sigcountarticles = $this->session->get('sigcountarticles', -1, 'sige');

		if(preg_match_all('@{gallery}(.*){/gallery}@Us', $article->text, $matches, PREG_PATTERN_ORDER) > 0)
		{
			$this->sigcountarticles++;
			$this->session->set('sigcountarticles', $this->sigcountarticles, 'sige');
			$this->sigcount = $this->session->get('sigcount', -1, 'sige');
			$this->plugin_parameters['lang'] = JFactory::getLanguage()->getTag();

			foreach($matches[1] as $match)
			{
				$this->sigcount++;
				$this->session->set('sigcount', $this->sigcount, 'sige');
				$sige_syntax_array = explode(',', $match);
				$this->images_dir = $sige_syntax_array[0];

				$this->getSyntaxParameters($sige_syntax_array);
				$this->setParams($article);
				$this->setTurboParameters();
				$html = $this->createHtmlOutput();

				$article->text = preg_replace('@(<p>)?{gallery}'.$match.'{/gallery}(</p>)?@s', $html, $article->text);
			}

			$this->loadHeadData();
		}
	}

	/**
	 * Extracts all parameters from the entered syntax
	 *
	 * @param $sige_array
	 */
	private function getSyntaxParameters($sige_array)
	{
		// Reset syntax parameters if syntax is used more than once per page
		$this->syntax_parameters = array();

		if(count($sige_array) >= 2)
		{
			for($i = 1; $i < count($sige_array); $i++)
			{
				$parameter_temp = explode('=', $sige_array[$i], 2);

				if(count($parameter_temp) == 2)
				{
					$this->syntax_parameters[strtolower(trim($parameter_temp[0]))] = trim($parameter_temp[1]);
				}
			}
		}
	}

	/**
	 * Sets all parameters for the correct execution
	 *
	 * @param $article
	 */
	private function setParams($article)
	{
		$params = array('width', 'height', 'ratio', 'gap_v', 'gap_h', 'quality', 'quality_png', 'displaynavtip', 'navtip', 'limit', 'displaymessage', 'message', 'thumbs', 'thumbs_new', 'view', 'limit_quantity', 'noslim', 'caption', 'iptc', 'iptcutf8', 'print', 'salign', 'connect', 'download', 'list', 'crop', 'crop_factor', 'sort', 'single', 'thumbdetail', 'watermark', 'encrypt', 'image_info', 'image_link', 'image_link_new', 'single_gallery', 'column_quantity', 'css_image', 'css_image_half', 'copyright', 'word', 'watermarkposition', 'watermarkimage', 'watermark_new', 'root', 'js', 'calcmaxthumbsize', 'fileinfo', 'turbo', 'resize_images', 'width_image', 'height_image', 'ratio_image', 'images_new', 'scaption', 'nodebug');

		foreach($params as $value)
		{
			$this->plugin_parameters[$value] = $this->getParams($value);
		}

		$count = $this->getParams('count', true);

		if(!empty($count))
		{
			$this->sigcount = $count;
		}

		if($this->plugin_parameters['root'])
		{
			$this->root_folder = '/';
		}

		if(!empty($this->plugin_parameters['displaymessage']))
		{
			if(JFactory::getApplication()->input->getWord('view') != 'featured' AND isset($article->title))
			{
				$this->article_title = preg_replace("@\"@", "'", $article->title);
			}
		}

		$this->thumbnail_max_height = $this->plugin_parameters['height'];
		$this->thumbnail_max_width = $this->plugin_parameters['width'];
	}

	/**
	 * Gets a specific parameter - syntax or plugins settings
	 *
	 * @param      $param
	 * @param bool $syntax_only
	 *
	 * @return mixed|string
	 */
	private function getParams($param, $syntax_only = false)
	{
		if(array_key_exists($param, $this->syntax_parameters) AND $this->syntax_parameters[$param] !== '')
		{
			return $this->syntax_parameters[$param];
		}

		if(empty($syntax_only))
		{
			return $this->params->get($param);
		}

		return '';
	}

	/**
	 * Sets the turbo mode parameters
	 */
	private function setTurboParameters()
	{
		$this->turbo_html_read_in = false;
		$this->turbo_css_read_in = false;

		if($this->plugin_parameters['turbo'])
		{
			if($this->plugin_parameters['turbo'] == 'new')
			{
				$this->turbo_html_read_in = true;
				$this->turbo_css_read_in = true;

				return;
			}

			if(!file_exists($this->absolute_path.$this->root_folder.$this->images_dir.'/sige_turbo_html-'.$this->plugin_parameters['lang'].'.txt'))
			{
				$this->turbo_html_read_in = true;
			}

			if(!file_exists($this->absolute_path.$this->root_folder.$this->images_dir.'/sige_turbo_css-'.$this->plugin_parameters['lang'].'.txt'))
			{
				$this->turbo_css_read_in = true;
			}
		}
	}

	/**
	 * Creates the HTML output of the gallery
	 *
	 * @return string
	 */
	private function createHtmlOutput()
	{
		if(!$this->plugin_parameters['turbo'] OR ($this->plugin_parameters['turbo'] AND $this->turbo_html_read_in))
		{
			$images = array();
			$images_loaded = 0;
			$this->loadImagesFromDir($images, $images_loaded);

			// Set default message
            $html = '';

            if(empty($this->plugin_parameters['nodebug']))
            {
			    $html .= '<p><strong>'.JText::_('NOIMAGES').'</strong><br /><br />'.JText::_('NOIMAGESDEBUG').' '.$this->live_site.$this->root_folder.$this->images_dir.'</p>';
            }

			if(!empty($images_loaded))
			{
				if(!file_exists($this->absolute_path.$this->root_folder.$this->images_dir.'/index.html'))
				{
					file_put_contents($this->absolute_path.$this->root_folder.$this->images_dir.'/index.html', '');
				}

				$this->sortImagesArray($images);

				$images_loaded_rest = 0;
				$single_yes = false;

				if($this->plugin_parameters['single'])
				{
					$this->singleImageHandling($images, $images_loaded, $images_loaded_rest, $single_yes);
				}

				$file_info = $this->getFileInfo($images, $images_loaded, $single_yes);

				if($this->plugin_parameters['calcmaxthumbsize'])
				{
					$this->calculateMaxThumbnailSize($images);
				}

				$sige_css = $this->createMainCssInstruction();
				$this->loadHeadData($sige_css);

				if($this->plugin_parameters['resize_images'])
				{
					$this->resizeImages($images);
				}

				if($this->plugin_parameters['watermark'])
				{
					$this->createWatermark($images, $single_yes);
				}

				$this->limitImageList($images_loaded, $images_loaded_rest);

				if($this->plugin_parameters['thumbs'])
				{
					$this->createThumbnails($images, $images_loaded);
				}

				if($this->plugin_parameters['word'])
				{
					$images_loaded_rest = $images_loaded;
					$this->plugin_parameters['limit_quantity'] = 1;
					$images_loaded = 1;
				}

				$html = '<!-- Simple Image Gallery Extended - Plugin Joomla! 3.x - Kubik-Rubik Joomla! Extensions -->';

				if(empty($this->plugin_parameters['word']))
				{
					if($this->plugin_parameters['single'] AND !empty($single_yes))
					{
						$html .= '<ul class="sige_single">';
					}
					elseif($this->plugin_parameters['list'])
					{
						$html .= '<ul>';
					}
					else
					{
						$html .= '<ul class="sige">';
					}
				}

				for($a = 0; $a < $images_loaded; $a++)
				{
					$this->htmlImage($images[$a]['filename'], $html, 0, $file_info, $a);
				}

				if(empty($this->plugin_parameters['word']))
				{
					if($this->plugin_parameters['list'])
					{
						$html .= '</ul>';
					}
					else
					{
						$html .= '</ul><span class="sige_clr"></span>';
					}
				}

				if(!empty($images_loaded_rest) AND !$this->plugin_parameters['image_link'])
				{
					for($a = $this->plugin_parameters['limit_quantity']; $a < $images_loaded_rest; $a++)
					{
						$this->htmlImage($images[$a]['filename'], $html, 1, $file_info, $a);
					}
				}

				if($this->plugin_parameters['copyright'])
				{
					if((!$this->plugin_parameters['single'] OR ($this->plugin_parameters['single'] AND !$single_yes)) AND !$this->plugin_parameters['list'] AND !$this->plugin_parameters['word'])
					{
						$html .= '<p class="sige_small"><a href="http://joomla-extensions.kubik-rubik.de" title="SIGE - Simple Image Gallery Extended - Kubik-Rubik Joomla! Extensions" target="_blank">Simple Image Gallery Extended</a></p>';
					}
				}
			}

			if($this->turbo_html_read_in)
			{
				file_put_contents($this->absolute_path.$this->root_folder.$this->images_dir.'/sige_turbo_html-'.$this->plugin_parameters['lang'].'.txt', $html);
			}

			return $html;
		}

		$this->loadHeadData(1);
		$html = file_get_contents($this->absolute_path.$this->root_folder.$this->images_dir.'/sige_turbo_html-'.$this->plugin_parameters['lang'].'.txt');

		return $html;
	}

	/**
	 * Loads all images with the allowed extensions from the specified directory
	 *
	 * @param $images
	 * @param $images_loaded
	 */
	private function loadImagesFromDir(&$images, &$images_loaded)
	{
		$directory = $this->absolute_path.$this->root_folder.$this->images_dir;

		if(is_dir($directory))
		{
			if($handle = opendir($directory))
			{
				while(($file = readdir($handle)) !== false)
				{
					if(in_array(substr(strtolower($file), -3), $this->allowed_extensions))
					{
						$images[] = array('filename' => $file);
						$images_loaded++;
					}
				}

				closedir($handle);
			}
		}
	}

	/**
	 * Sorts the images array
	 *
	 * @param $images
	 */
	private function sortImagesArray(&$images)
	{
		if($this->plugin_parameters['sort'] == 1)
		{
			shuffle($images);
		}
		elseif($this->plugin_parameters['sort'] == 2)
		{
			sort($images);
		}
		elseif($this->plugin_parameters['sort'] == 3)
		{
			rsort($images);
		}
		elseif($this->plugin_parameters['sort'] == 4 OR $this->plugin_parameters['sort'] == 5)
		{
			for($a = 0; $a < count($images); $a++)
			{
				$images[$a]['timestamp'] = filemtime($this->absolute_path.$this->root_folder.$this->images_dir.'/'.$images[$a]['filename']);
			}

			if($this->plugin_parameters['sort'] == 4)
			{
				usort($images, array($this, 'timeasc'));
			}
			elseif($this->plugin_parameters['sort'] == 5)
			{
				usort($images, array($this, 'timedesc'));
			}
		}
	}

	/**
	 * Handles the single image output properly
	 *
	 * @param $images
	 * @param $images_loaded
	 * @param $images_loaded_rest
	 * @param $single_yes
	 */
	private function singleImageHandling(&$images, &$images_loaded, &$images_loaded_rest, &$single_yes)
	{
		if($images[0]['filename'] == $this->plugin_parameters['single'])
		{
			if($this->plugin_parameters['single_gallery'])
			{
				$images_loaded_rest = $images_loaded;
				$this->plugin_parameters['limit_quantity'] = 1;
			}

			$images_loaded = 1;
			$single_yes = true;

			return;
		}

		for($a = 1; $a < $images_loaded; $a++)
		{
			if($images[$a]['filename'] == $this->plugin_parameters['single'])
			{
				if($this->plugin_parameters['single_gallery'])
				{
					$images_loaded_rest = $images_loaded;
					$this->plugin_parameters['limit_quantity'] = 1;
				}

				$image_single = $images[$a];
				unset($images[$a]);
				array_unshift($images, $image_single);

				$images_loaded = 1;
				$single_yes = true;

				break;
			}
		}
	}

	/**
	 * Loads all information from the info text file
	 *
	 * @param $images
	 * @param $images_loaded
	 * @param $single_yes
	 *
	 * @return array
	 */
	private function getFileInfo(&$images, &$images_loaded, $single_yes)
	{
		$file_info = array();

		if($this->plugin_parameters['fileinfo'])
		{
			$captions_lang = $this->absolute_path.$this->root_folder.$this->images_dir.'/captions-'.$this->plugin_parameters['lang'].'.txt';
			$captions_txtfile = $this->absolute_path.$this->root_folder.$this->images_dir.'/captions.txt';

			if(file_exists($captions_lang))
			{
				$captions_file = array_map('trim', file($captions_lang));

				foreach($captions_file as $value)
				{
					if(!empty($value))
					{
						$captions_line = explode('|', $value);
						$file_info[] = $captions_line;
					}
				}
			}
			elseif(file_exists($captions_txtfile) AND !file_exists($captions_lang))
			{
				$captions_file = array_map('trim', file($captions_txtfile));

				foreach($captions_file as $value)
				{
					if(!empty($value))
					{
						$captions_line = explode('|', $value);
						$file_info[] = $captions_line;
					}
				}
			}

			// Use the sorting from the captions.text to sort the images
			if(!empty($file_info) AND $this->plugin_parameters['sort'] == 6 AND empty($single_yes))
			{
				$images_file_info = array();

				foreach($file_info as $file_info_image)
				{
					foreach($images as $key => $image)
					{
						if($file_info_image[0] == $image['filename'])
						{
							$images_file_info[]['filename'] = $file_info_image[0];
							unset($images[$key]);
							break;
						}
					}
				}

				if(!empty($images_file_info))
				{
					$images = $images_file_info;
					$images_loaded = count($images);
				}
			}
		}

		return $file_info;
	}

	/**
	 * Calculates the maximum thumbnails size (resolution) of all loaded images
	 *
	 * @param $images
	 */
	private function calculateMaxThumbnailSize($images)
	{
		$max_height = array();
		$max_width = array();

		foreach($images as $image)
		{
			list($max_height[], $max_width[]) = $this->calculateSize($image['filename'], 1);
		}

		rsort($max_height);
		rsort($max_width);

		$this->thumbnail_max_height = $max_height[0];
		$this->thumbnail_max_width = $max_width[0];
	}

	/**
	 * Gets the correct resolution dependent of selected parameters
	 *
	 * @param $image
	 * @param $thumbnail
	 *
	 * @return array
	 */
	private function calculateSize($image, $thumbnail)
	{
		if($this->plugin_parameters['resize_images'] AND empty($thumbnail))
		{
			list($height_new, $width_new) = $this->calculateSizeProcess($image, $this->plugin_parameters['height_image'], $this->plugin_parameters['width_image'], $this->plugin_parameters['ratio_image']);

			return array($height_new, $width_new);
		}

		list($height_new, $width_new) = $this->calculateSizeProcess($image, $this->plugin_parameters['height'], $this->plugin_parameters['width'], $this->plugin_parameters['ratio']);

		return array($height_new, $width_new);
	}

	/**
	 * Calculates the proper resolution of a specific image and returns the height and width in an array with integer type casting
	 *
	 * @param $image
	 * @param $height
	 * @param $width
	 * @param $ratio
	 *
	 * @return array
	 */
	private function calculateSizeProcess($image, $height, $width, $ratio)
	{
		$height_new = $height;
		$width_new = $width;

		if(!empty($ratio))
		{
			$imagedata = getimagesize($this->absolute_path.$this->root_folder.$this->images_dir.'/'.$image);
			$height_new = $imagedata[1] * ($width / $imagedata[0]);

			if($height_new > $height)
			{
				$height_new = $height;
				$width_new = $imagedata[0] * ($height / $imagedata[1]);
			}
		}

		return array((int)$height_new, (int)$width_new);
	}

	/**
	 * Creates the main CSS instructions for the gallery output
	 *
	 * @return string
	 */
	private function createMainCssInstruction()
	{
		$sige_css = '';

		if($this->plugin_parameters['css_image'])
		{
			$css_image_width = 600;

			if($this->plugin_parameters['css_image_half'])
			{
				$css_image_width = $css_image_width / 2;
			}

			$sige_css .= '.sige_cont_'.$this->sigcount.' .sige_css_image:hover span{width: '.$css_image_width.'px;}'."\n";
		}

		$caption_height = 0;

		if($this->plugin_parameters['caption'])
		{
			$caption_height = 20;
		}

		if($this->plugin_parameters['salign'])
		{
			if($this->plugin_parameters['salign'] == 'left')
			{
				$sige_css .= '.sige_cont_'.$this->sigcount.' {width:'.($this->thumbnail_max_width + $this->plugin_parameters['gap_h']).'px;height:'.($this->thumbnail_max_height + $this->plugin_parameters['gap_v'] + $caption_height).'px;float:left;display:inline-block;}'."\n";
			}
			elseif($this->plugin_parameters['salign'] == 'right')
			{
				$sige_css .= '.sige_cont_'.$this->sigcount.' {width:'.($this->thumbnail_max_width + $this->plugin_parameters['gap_h']).'px;height:'.($this->thumbnail_max_height + $this->plugin_parameters['gap_v'] + $caption_height).'px;float:right;display:inline-block;}'."\n";
			}
			elseif($this->plugin_parameters['salign'] == 'center')
			{
				$sige_css .= '.sige_cont_'.$this->sigcount.' {width:'.($this->thumbnail_max_width + $this->plugin_parameters['gap_h']).'px;height:'.($this->thumbnail_max_height + $this->plugin_parameters['gap_v'] + $caption_height).'px;display:inline-block;}'."\n";
			}

			return $sige_css;
		}

		$sige_css .= '.sige_cont_'.$this->sigcount.' {width:'.($this->thumbnail_max_width + $this->plugin_parameters['gap_h']).'px;height:'.($this->thumbnail_max_height + $this->plugin_parameters['gap_v'] + $caption_height).'px;float:left;display:inline-block;}'."\n";

		return $sige_css;
	}

	/**
	 * Loads the CSS and JS instructions to the head section of the HTML page
	 *
	 * @param int $sige_css
	 */
	private function loadHeadData($sige_css = 0)
	{
		$document = JFactory::getDocument();

		if($document instanceof JDocumentHTML)
		{
			$head = $this->getHeadData($sige_css);

			// Combine dynamic CSS instructions - Check whether a custom style tag was already set and combine them to
			// avoid problems in some browsers due to too many CSS instructions
			if(!empty($sige_css))
			{
				if(!empty($document->_custom))
				{
					$custom_tags = array();

					foreach($document->_custom as $key => $custom_tag)
					{
						if(preg_match('@<style type="text/css">(.*)</style>@Us', $custom_tag, $match))
						{
							$custom_tags[] = $match[1];
							unset($document->_custom[$key]);
						}
					}

					// If content is loaded from the turbo file, then the CSS instructions need to be prepared for the output
					if($sige_css == 1)
					{
						if(preg_match('@<style type="text/css">(.*)</style>@Us', $head, $match))
						{
							$sige_css = $match[1];
						}
					}

					if(!empty($custom_tags))
					{
						$head = '<style type="text/css">'.implode('', $custom_tags).$sige_css.'</style>';
					}
				}
			}

			if(!empty($head))
			{
				$document->addCustomTag($head);
			}
		}
	}

	/**
	 * Creates the correct CSS and JS instructions for the loaded gallery
	 *
	 * @param $sige_css
	 *
	 * @return array|string
	 */
	private function getHeadData($sige_css)
	{
		if(!empty($sige_css))
		{
			if(!$this->plugin_parameters['turbo'] OR ($this->plugin_parameters['turbo'] AND $this->turbo_css_read_in))
			{
				$head = '<style type="text/css">'.$sige_css.'</style>';

				if($this->turbo_css_read_in)
				{
					file_put_contents($this->absolute_path.$this->root_folder.$this->images_dir.'/sige_turbo_css-'.$this->plugin_parameters['lang'].'.txt', $head);
				}

				return $head;
			}

			$head = file_get_contents($this->absolute_path.$this->root_folder.$this->images_dir.'/sige_turbo_css-'.$this->plugin_parameters['lang'].'.txt');

			return $head;
		}

		if($this->sigcountarticles == 0)
		{
			$head = array();
			$head[] = '<link rel="stylesheet" href="'.$this->live_site.'/plugins/content/sige/plugin_sige/sige.css" type="text/css" media="screen" />';

			if($this->plugin_parameters['js'] == 1)
			{
				JHtml::_('behavior.framework');

				$head[] = '<script type="text/javascript" src="'.$this->live_site.'/plugins/content/sige/plugin_sige/slimbox.js"></script>';
				$head[] = '<script type="text/javascript">
                                Slimbox.scanPage = function() {
                                    $$("a[rel^=lightbox]").slimbox({counterText: "'.JText::_('PLG_SIGE_SLIMBOX_IMAGES').'"}, null, function(el) {
                                        return (this == el) || ((this.rel.length > 8) && (this.rel == el.rel));
                                    });
                                };
                                if (!/android|iphone|ipod|series60|symbian|windows ce|blackberry/i.test(navigator.userAgent)) {
                                    window.addEvent("domready", Slimbox.scanPage);
                                }
                                </script>';
				$head[] = '<link rel="stylesheet" href="'.$this->live_site.'/plugins/content/sige/plugin_sige/slimbox.css" type="text/css" media="screen" />';
			}
			elseif($this->plugin_parameters['js'] == 2)
			{
				if($this->plugin_parameters['lang'] == 'de-DE')
				{
					$head[] = '<script type="text/javascript" src="'.$this->live_site.'/plugins/content/sige/plugin_sige/lytebox.js"></script>';
				}
				else
				{
					$head[] = '<script type="text/javascript" src="'.$this->live_site.'/plugins/content/sige/plugin_sige/lytebox_en.js"></script>';
				}

				$head[] = '<link rel="stylesheet" href="'.$this->live_site.'/plugins/content/sige/plugin_sige/lytebox.css" type="text/css" media="screen" />';
			}
			elseif($this->plugin_parameters['js'] == 3)
			{
				if($this->plugin_parameters['lang'] == 'de-DE')
				{
					$head[] = '<script type="text/javascript" src="'.$this->live_site.'/plugins/content/sige/plugin_sige/shadowbox.js"></script>';
				}
				else
				{
					$head[] = '<script type="text/javascript" src="'.$this->live_site.'/plugins/content/sige/plugin_sige/shadowbox_en.js"></script>';
				}

				$head[] = '<link rel="stylesheet" href="'.$this->live_site.'/plugins/content/sige/plugin_sige/shadowbox.css" type="text/css" media="screen" />';
				$head[] = '<script type="text/javascript">Shadowbox.init();</script>';
			}
			elseif($this->plugin_parameters['js'] == 4)
			{
				JHtml::_('behavior.framework');

				$head[] = '<script type="text/javascript" src="'.$this->live_site.'/plugins/content/sige/plugin_sige/milkbox.js"></script>';
				$head[] = '<link rel="stylesheet" href="'.$this->live_site.'/plugins/content/sige/plugin_sige/milkbox.css" type="text/css" media="screen" />';
			}
			elseif($this->plugin_parameters['js'] == 5)
			{
				JHtml::_('jquery.framework');

				$head[] = '<script type="text/javascript" src="'.$this->live_site.'/plugins/content/sige/plugin_sige/slimbox2.js"></script>';
				$head[] = '<script type="text/javascript">
                                if (!/android|iphone|ipod|series60|symbian|windows ce|blackberry/i.test(navigator.userAgent)) {
                                    jQuery(function($) {
                                        $("a[rel^=\'lightbox\']").slimbox({counterText: "'.JText::_('PLG_SIGE_SLIMBOX_IMAGES').'"}, null, function(el) {
                                            return (this == el) || ((this.rel.length > 8) && (this.rel == el.rel));
                                        });
                                    });
                                }
                                </script>';
				$head[] = '<link rel="stylesheet" href="'.$this->live_site.'/plugins/content/sige/plugin_sige/slimbox2.css" type="text/css" media="screen" />';
			}
			elseif($this->plugin_parameters['js'] == 6)
			{
				JHtml::_('jquery.framework');

				$head[] = '<script type="text/javascript" src="'.$this->live_site.'/plugins/content/sige/plugin_sige/venobox/venobox.js"></script>';
				$head[] = '<script type="text/javascript">jQuery(document).ready(function(){jQuery(\'.venobox\').venobox();});</script>';
				$head[] = '<link rel="stylesheet" href="'.$this->live_site.'/plugins/content/sige/plugin_sige/venobox/venobox.css" type="text/css" media="screen" />';
			}

			return "\n".implode("\n", $head)."\n";
		}

		return '';
	}

	/**
	 * Resizes all loaded images to the specified resolution
	 *
	 * @param $images
	 */
	private function resizeImages($images)
	{
		$this->createEmptyDirectory($this->absolute_path.$this->root_folder.$this->images_dir.'/resizedimages');
		$num = count($images);

		for($a = 0; $a < $num; $a++)
		{
			$this->resizeImage($images[$a]['filename']);
		}
	}

	/**
	 * Creates an empty directory and index.html file in the transferred path
	 *
	 * @param $path
	 */
	private function createEmptyDirectory($path)
	{
		if(!is_dir($path))
		{
			mkdir($path, 0755);
			file_put_contents($path.'/index.html', '');
		}
	}

	/**
	 * Resizes a specific image properly
	 *
	 * @param $image
	 */
	private function resizeImage($image)
	{
		if(!empty($image))
		{
			$file_name_thumb = $this->absolute_path.$this->root_folder.$this->images_dir.'/resizedimages/'.$image;

			if(!file_exists($file_name_thumb) OR !empty($this->plugin_parameters['images_new']))
			{
				$image_type = substr(strtolower($image), -3);

				if($image_type == 'jpg')
				{
					$image_source = imagecreatefromjpeg($this->absolute_path.$this->root_folder.$this->images_dir.'/'.$image);
					$image_thumb = $this->resizeImageThumbnail($image, $image_source);
					imagejpeg($image_thumb, $this->absolute_path.$this->root_folder.$this->images_dir.'/resizedimages/'.$image, $this->plugin_parameters['quality']);
				}
				elseif($image_type == 'png')
				{
					$image_source = imagecreatefrompng($this->absolute_path.$this->root_folder.$this->images_dir.'/'.$image);
					$image_thumb = $this->resizeImageThumbnail($image, $image_source);
					imagepng($image_thumb, $this->absolute_path.$this->root_folder.$this->images_dir.'/resizedimages/'.$image, $this->plugin_parameters['quality_png']);
				}
				elseif($image_type == 'gif')
				{
					$image_source = imagecreatefromgif($this->absolute_path.$this->root_folder.$this->images_dir.'/'.$image);
					$image_thumb = $this->resizeImageThumbnail($image, $image_source);
					imagegif($image_thumb, $this->absolute_path.$this->root_folder.$this->images_dir.'/resizedimages/'.$image);
				}

				if(isset($image_source) AND is_resource($image_source))
				{
					imagedestroy($image_source);
				}

				if(isset($image_thumb) AND is_resource($image_thumb))
				{
					imagedestroy($image_thumb);
				}

				return;
			}
		}
	}

	/**
	 * Creates the copy of the resized image
	 *
	 * @param $image
	 * @param $image_source
	 *
	 * @return resource
	 */
	private function resizeImageThumbnail($image, $image_source)
	{
		$width_original = imagesx($image_source);
		$height_original = imagesy($image_source);
		list($height_new, $width_new) = $this->calculateSize($image, 0);
		$image_thumb = imagecreatetruecolor($width_new, $height_new);
		imagecopyresampled($image_thumb, $image_source, 0, 0, 0, 0, $width_new, $height_new, $width_original, $height_original);

		return $image_thumb;
	}

	/**
	 * Creates the watermark on the images
	 *
	 * @param $images
	 * @param $single_yes
	 */
	private function createWatermark($images, $single_yes)
	{
		$this->createEmptyDirectory($this->absolute_path.$this->root_folder.$this->images_dir.'/wm');
		$num = count($images);

		if(empty($this->plugin_parameters['single_gallery']) AND $single_yes)
		{
			$num = 1;
		}

		for($a = 0; $a < $num; $a++)
		{
			$this->createWatermarkImage($images[$a]['filename']);
		}
	}

	/**
	 * Creates the watermark on a specific image
	 *
	 * @param $image
	 */
	private function createWatermarkImage($image)
	{
		if(!empty($image))
		{
			$image_hash = $this->encryptImageName($image);
			$file_name_watermark = $this->absolute_path.$this->root_folder.$this->images_dir.'/wm/'.$image_hash;

			if(!file_exists($file_name_watermark) OR !empty($this->plugin_parameters['watermark_new']))
			{
				$image_watermark = imagecreatefrompng($this->absolute_path.'/plugins/content/sige/plugin_sige/watermark.png');

				if($this->plugin_parameters['watermarkimage'])
				{
					$image_watermark = imagecreatefrompng($this->absolute_path.'/plugins/content/sige/plugin_sige/'.$this->plugin_parameters['watermarkimage']);
				}

				$watermark_width = imagesx($image_watermark);
				$watermark_height = imagesy($image_watermark);
				$image_type = substr(strtolower($image), -3);

				if($image_type == 'jpg')
				{
					$image_source = imagecreatefromjpeg($this->createWatermarkImageThumbnailSourcePath($image));
					$image_source_watermark = $this->createWatermarkImageCopy($image_source, $image_watermark, $watermark_height, $watermark_width);
					imagejpeg($image_source_watermark, $this->absolute_path.$this->root_folder.$this->images_dir.'/wm/'.$image_hash, $this->plugin_parameters['quality']);
				}
				elseif($image_type == 'png')
				{
					$image_source = imagecreatefrompng($this->createWatermarkImageThumbnailSourcePath($image));
					$image_source_watermark = $this->createWatermarkImageCopy($image_source, $image_watermark, $watermark_height, $watermark_width);
					imagepng($image_source_watermark, $this->absolute_path.$this->root_folder.$this->images_dir.'/wm/'.$image_hash, $this->plugin_parameters['quality_png']);
				}
				elseif($image_type == 'gif')
				{
					$image_source = imagecreatefromgif($this->createWatermarkImageThumbnailSourcePath($image));
					$image_source_temp = imagecreatetruecolor(imagesx($image_source), imagesy($image_source));
					imagecopy($image_source_temp, $image_source, 0, 0, 0, 0, imagesx($image_source), imagesy($image_source));
					$image_source = $image_source_temp;

					$image_source_watermark = $this->createWatermarkImageCopy($image_source, $image_watermark, $watermark_height, $watermark_width);
					imagegif($image_source_watermark, $this->absolute_path.$this->root_folder.$this->images_dir.'/wm/'.$image_hash);
				}

				if(isset($image_source) AND is_resource($image_source))
				{
					imagedestroy($image_source);
				}

				if(isset($image_watermark) AND is_resource($image_watermark))
				{
					imagedestroy($image_watermark);
				}
			}
		}

		return;
	}

	/**
	 * Creates an image name hash to hide the original image name
	 *
	 * @param $image
	 *
	 * @return string
	 */
	private function encryptImageName($image)
	{
		$image_name = substr($image, 0, -4);
		$image_type = substr(strtolower($image), -3);
		$image_hash = md5($image_name);

		if($this->plugin_parameters['encrypt'] == 0)
		{
			$image_hash = str_rot13($image_name);
		}

		if($this->plugin_parameters['encrypt'] == 2)
		{
			$image_hash = sha1($image_name);
		}

		return $image_hash.'.'.$image_type;
	}

	/**
	 * Creates the watermark image thumbnail source path
	 *
	 * @param $image
	 *
	 * @return string
	 */
	private function createWatermarkImageThumbnailSourcePath($image)
	{
		if($this->plugin_parameters['resize_images'])
		{
			return $this->absolute_path.$this->root_folder.$this->images_dir.'/resizedimages/'.$image;
		}

		return $this->absolute_path.$this->root_folder.$this->images_dir.'/'.$image;
	}

	/**
	 * Creates the watermark image copy
	 *
	 * @param $image_source
	 * @param $image_watermark
	 * @param $watermark_height
	 * @param $watermark_width
	 *
	 * @return mixed
	 */
	private function createWatermarkImageCopy($image_source, $image_watermark, $watermark_height, $watermark_width)
	{
		$width_original = imagesx($image_source);
		$height_original = imagesy($image_source);

		if($this->plugin_parameters['watermarkposition'] == 1)
		{
			imagecopy($image_source, $image_watermark, 0, 0, 0, 0, $watermark_width, $watermark_height);
		}
		elseif($this->plugin_parameters['watermarkposition'] == 2)
		{
			imagecopy($image_source, $image_watermark, $width_original - $watermark_width, 0, 0, 0, $watermark_width, $watermark_height);
		}
		elseif($this->plugin_parameters['watermarkposition'] == 3)
		{
			imagecopy($image_source, $image_watermark, 0, $height_original - $watermark_height, 0, 0, $watermark_width, $watermark_height);
		}
		elseif($this->plugin_parameters['watermarkposition'] == 4)
		{
			imagecopy($image_source, $image_watermark, $width_original - $watermark_width, $height_original - $watermark_height, 0, 0, $watermark_width, $watermark_height);
		}
		else
		{
			imagecopy($image_source, $image_watermark, ($width_original - $watermark_width) / 2, ($height_original - $watermark_height) / 2, 0, 0, $watermark_width, $watermark_height);
		}

		return $image_source;
	}

	/**
	 * Limits the image list if corresponding parameter is set
	 *
	 * @param $images_loaded
	 * @param $images_loaded_rest
	 */
	private function limitImageList(&$images_loaded, &$images_loaded_rest)
	{
		if($this->plugin_parameters['limit'] AND (empty($this->plugin_parameters['single']) OR empty($this->plugin_parameters['single_gallery'])))
		{
			$images_loaded_rest = $images_loaded;

			if($images_loaded > $this->plugin_parameters['limit_quantity'])
			{
				$images_loaded = $this->plugin_parameters['limit_quantity'];
			}
		}
	}

	/**
	 * Creates and stores thumbnails of the original images
	 *
	 * @param $images
	 * @param $images_loaded
	 */
	private function createThumbnails($images, $images_loaded)
	{
		if(empty($this->plugin_parameters['list']) AND empty($this->plugin_parameters['word']))
		{
			$this->createEmptyDirectory($this->absolute_path.$this->root_folder.$this->images_dir.'/thumbs');

			for($a = 0; $a < $images_loaded; $a++)
			{
				$this->createThumbnail($images[$a]['filename']);
			}
		}
	}

	/**
	 * Creates a thumbnail from a specific image
	 *
	 * @param $image
	 */
	private function createThumbnail($image)
	{
		if(!empty($image))
		{
			$file_name_thumb = $this->absolute_path.$this->root_folder.$this->images_dir.'/thumbs/'.$image;

			if($this->plugin_parameters['watermark'])
			{
				$file_name_thumb = $this->absolute_path.$this->root_folder.$this->images_dir.'/thumbs/'.$this->encryptImageName($image);
			}

			if(file_exists($file_name_thumb) == false OR !empty($this->plugin_parameters['thumbs_new']))
			{
				$image_source_path = $this->createThumbnailSourceImagePath($image);
				$image_type = substr(strtolower($image), -3);

				if($image_type == 'jpg')
				{
					$image_source = imagecreatefromjpeg($image_source_path);
					$image_thumb = $this->createThumbnailResize($image, $image_source);
					imagejpeg($image_thumb, $this->createThumbnailDestinationImagePath($image), $this->plugin_parameters['quality']);
				}
				elseif($image_type == 'png')
				{
					$image_source = imagecreatefrompng($image_source_path);
					$image_thumb = $this->createThumbnailResize($image, $image_source);
					imagepng($image_thumb, $this->createThumbnailDestinationImagePath($image), $this->plugin_parameters['quality_png']);
				}
				elseif($image_type == 'gif')
				{
					$image_source = imagecreatefromgif($image_source_path);
					$image_thumb = $this->createThumbnailResize($image, $image_source);
					imagegif($image_thumb, $this->createThumbnailDestinationImagePath($image));
				}

				if(isset($image_source) AND is_resource($image_source))
				{
					imagedestroy($image_source);
				}

				if(isset($image_thumb) AND is_resource($image_thumb))
				{
					imagedestroy($image_thumb);
				}
			}
		}

		return;
	}

	/**
	 * Creates the thumbnail source image path
	 *
	 * @param $image
	 *
	 * @return string
	 */
	private function createThumbnailSourceImagePath($image)
	{
		if($this->plugin_parameters['watermark'])
		{
			return $this->absolute_path.$this->root_folder.$this->images_dir.'/wm/'.$this->encryptImageName($image);
		}

		return $this->absolute_path.$this->root_folder.$this->images_dir.'/'.$image;
	}

	/**
	 * Creates the resized thumbnail copy
	 *
	 * @param $image
	 * @param $image_source
	 *
	 * @return resource
	 */
	private function createThumbnailResize($image, $image_source)
	{
		list($height_new, $width_new) = $this->calculateSize($image, 1);

		$height_original = imagesy($image_source);
		$width_original = imagesx($image_source);
		$image_thumb = imagecreatetruecolor($width_new, $height_new);

		if($this->plugin_parameters['crop'] AND ($this->plugin_parameters['crop_factor'] > 0 AND $this->plugin_parameters['crop_factor'] < 100))
		{
			list($crop_width, $crop_height, $x_coordinate, $y_coordinate) = $this->getCropInformation($width_original, $height_original);
			imagecopyresampled($image_thumb, $image_source, 0, 0, $x_coordinate, $y_coordinate, $width_new, $height_new, $crop_width, $crop_height);

			return $image_thumb;
		}

		if($this->plugin_parameters['thumbdetail'] == 1)
		{
			imagecopyresampled($image_thumb, $image_source, 0, 0, 0, 0, $width_new, $height_new, $width_new, $height_new);
		}
		elseif($this->plugin_parameters['thumbdetail'] == 2)
		{
			imagecopyresampled($image_thumb, $image_source, 0, 0, $width_original - $width_new, 0, $width_new, $height_new, $width_new, $height_new);
		}
		elseif($this->plugin_parameters['thumbdetail'] == 3)
		{
			imagecopyresampled($image_thumb, $image_source, 0, 0, 0, $height_original - $height_new, $width_new, $height_new, $width_new, $height_new);
		}
		elseif($this->plugin_parameters['thumbdetail'] == 4)
		{
			imagecopyresampled($image_thumb, $image_source, 0, 0, $width_original - $width_new, $height_original - $height_new, $width_new, $height_new, $width_new, $height_new);
		}
		else
		{
			imagecopyresampled($image_thumb, $image_source, 0, 0, 0, 0, $width_new, $height_new, $width_original, $height_original);
		}

		return $image_thumb;
	}

	/**
	 * Gets correct crop information from specified parameter values
	 *
	 * @param $width_original
	 * @param $height_original
	 *
	 * @return array
	 */
	private function getCropInformation($width_original, $height_original)
	{
		$biggest_side = $height_original;

		if($width_original > $height_original)
		{
			$biggest_side = $width_original;
		}

		$crop_percent = (1 - ($this->plugin_parameters['crop_factor'] / 100));

		$crop_width = $width_original * $crop_percent;
		$crop_height = $height_original * $crop_percent;

		if(!$this->plugin_parameters['ratio'] AND ($this->plugin_parameters['width'] == $this->plugin_parameters['height']))
		{
			$crop_width = $biggest_side * $crop_percent;
			$crop_height = $biggest_side * $crop_percent;
		}
		elseif(!$this->plugin_parameters['ratio'] AND ($this->plugin_parameters['width'] != $this->plugin_parameters['height']))
		{
			$crop_width = ($this->plugin_parameters['width'] * ($height_original / $this->plugin_parameters['height'])) * $crop_percent;
			$crop_height = $height_original * $crop_percent;

			if(($width_original / $this->plugin_parameters['width']) < ($height_original / $this->plugin_parameters['height']))
			{
				$crop_width = $width_original * $crop_percent;
				$crop_height = ($this->plugin_parameters['height'] * ($width_original / $this->plugin_parameters['width'])) * $crop_percent;
			}
		}

		$x_coordinate = ($width_original - $crop_width) / 2;
		$y_coordinate = ($height_original - $crop_height) / 2;

		return array($crop_width, $crop_height, $x_coordinate, $y_coordinate);
	}

	/**
	 * Creates the thumbnail destination image path
	 *
	 * @param $image
	 *
	 * @return string
	 */
	private function createThumbnailDestinationImagePath($image)
	{
		if($this->plugin_parameters['watermark'])
		{
			return $this->absolute_path.$this->root_folder.$this->images_dir.'/thumbs/'.$this->encryptImageName($image);
		}

		return $this->absolute_path.$this->root_folder.$this->images_dir.'/thumbs/'.$image;
	}

	/**
	 * Creates the HTML code for a specific image in the gallery
	 *
	 * @param $image
	 * @param $html
	 * @param $hidden
	 * @param $file_info
	 * @param $a
	 */
	private function htmlImage($image, &$html, $hidden, &$file_info, $a)
	{
		if(!empty($image))
		{
			$image_hash = $this->encryptImageName($image);
			$image_title = $image_alt = substr($image, 0, -4);
			$image_description = false;
			$image_link_file = false;

			if(!empty($file_info))
			{
				$this->htmlImageFileInfo($file_info, $image, $image_title, $image_description, $image_alt, $image_link_file);
			}

			if($this->plugin_parameters['iptc'] == 1)
			{
				$this->iptcInfo($image, $image_title, $image_description);
			}

			if(!empty($hidden))
			{
				$this->htmlImageHidden($html, $image_hash, $image, $image_title);

				return;
			}

			if($this->plugin_parameters['list'] AND !$this->plugin_parameters['word'])
			{
				$html .= '<li>';
			}
			elseif($this->plugin_parameters['word'])
			{
				$html .= '<span>';
			}
			else
			{
				$html .= '<li class="sige_cont_'.$this->sigcount.'"><span class="sige_thumb">';
			}

			$this->htmlImageAnchorTag($html, $image_hash, $image, $image_title, $image_description, $image_link_file);

			if(!$this->plugin_parameters['list'] AND !$this->plugin_parameters['word'])
			{
				$this->htmlImageImgTag($html, $image_hash, $image, $image_title, $image_alt, $image_description);
			}
			elseif($this->plugin_parameters['list'] AND !$this->plugin_parameters['word'])
			{
				$html .= $image_title;

				if(!empty($image_description))
				{
					$html .= ' - '.$image_description;
				}
			}
			elseif($this->plugin_parameters['word'])
			{
				$html .= JText::_($this->plugin_parameters['word']);
			}

			if($this->plugin_parameters['css_image'] AND !$this->plugin_parameters['image_link'])
			{
				$this->htmlImageImgTagCssImage($html, $image_hash, $image, $image_title, $image_alt, $image_description);
			}

			if(!$this->plugin_parameters['noslim'] OR $this->plugin_parameters['image_link'] OR $this->plugin_parameters['css_image'] OR !empty($image_link_file))
			{
				$html .= '</a>';
			}

			if($this->plugin_parameters['caption'])
			{
				$this->htmlImageCaption($html, $image_title);
			}

			if($this->plugin_parameters['list'] AND !$this->plugin_parameters['word'])
			{
				$html .= '</li>';
			}
			elseif($this->plugin_parameters['word'])
			{
				$html .= '</span>';
			}
			elseif(!$this->plugin_parameters['caption'])
			{
				$html .= '</span></li>';
			}
		}

		if($this->plugin_parameters['column_quantity'])
		{
			if(($a + 1) % $this->plugin_parameters['column_quantity'] == 0)
			{
				$html .= '<br class="sige_clr"/>';
			}
		}
	}

	/**
	 * Defines file info information if provided for the loaded image
	 *
	 * @param $file_info
	 * @param $image
	 * @param $image_title
	 * @param $image_description
	 * @param $image_alt
	 * @param $image_link_file
	 */
	private function htmlImageFileInfo(&$file_info, $image, &$image_title, &$image_description, &$image_alt, &$image_link_file)
	{
		foreach($file_info as $key => $value)
		{
			if($value[0] == $image)
			{
				$image_title = $value[1];

				if(!empty($value[2]))
				{
					$image_description = $value[2];
				}

				// Alt attribute for image
				if(!empty($value[3]))
				{
					$image_alt = $value[3];
				}

				// Link for image
				if(!empty($value[4]))
				{
					$image_link_file = $value[4];
				}

				// Remove information from file_info array to speed up the process for the following images
				unset($file_info[$key]);
				break;
			}
		}
	}

	/**
	 * Sets IPTC information if set and provided
	 *
	 * @param $image
	 * @param $image_title
	 * @param $image_description
	 */
	private function iptcInfo($image, &$image_title, &$image_description)
	{
		$iptc_title = '';
		$iptc_caption = '';
		$info = array();

		getimagesize(JPATH_SITE.$this->root_folder.$this->images_dir.'/'.$image, $info);

		if(isset($info['APP13']))
		{
			$iptc_php = iptcparse($info['APP13']);

			if(is_array($iptc_php))
			{
				$data = array('caption' => '', 'title' => '');

				if(isset($iptc_php["2#120"][0]))
				{
					$data['caption'] = $iptc_php["2#120"][0];
				}

				if(isset($iptc_php["2#005"][0]))
				{
					$data['title'] = $iptc_php["2#005"][0];
				}

				$iptc_title = utf8_encode(html_entity_decode($data['title'], ENT_NOQUOTES));
				$iptc_caption = utf8_encode(html_entity_decode($data['caption'], ENT_NOQUOTES));

				if($this->plugin_parameters['iptcutf8'] == 1)
				{
					$iptc_title = html_entity_decode($data['title'], ENT_NOQUOTES);
					$iptc_caption = html_entity_decode($data['caption'], ENT_NOQUOTES);
				}
			}
		}

		if(!empty($iptc_title))
		{
			$image_title = $iptc_title;
		}

		if(!empty($iptc_caption))
		{
			$image_description = $iptc_caption;
		}
	}

	/**
	 * Creates the hidden output for the gallery - e.g. used in the lightbox gallery view
	 *
	 * @param $html
	 * @param $image_hash
	 * @param $image
	 * @param $image_title
	 *
	 * @return string
	 */
	private function htmlImageHidden(&$html, $image_hash, $image, $image_title)
	{
		if(!$this->plugin_parameters['noslim'])
		{
			if($this->plugin_parameters['watermark'])
			{
				$html .= '<span style="display: none"><a href="'.$this->live_site.$this->root_folder.$this->images_dir.'/wm/'.$image_hash.'"';
			}
			else
			{
				if($this->plugin_parameters['resize_images'])
				{
					$html .= '<span style="display: none"><a href="'.$this->live_site.$this->root_folder.$this->images_dir.'/resizedimages/'.$image.'"';
				}
				else
				{
					$html .= '<span style="display: none"><a href="'.$this->live_site.$this->root_folder.$this->images_dir.'/'.$image.'"';
				}
			}

			$this->htmlImageRelAttribute($html);

			$html .= ' title="';

			$this->htmlImageAddTitleAttribute($html, $image_hash, $image, $image_title);

			$html .= '"></a></span>';
		}

		return $html;
	}

	/**
	 * Creates the image rel attribute code for a specific image
	 *
	 * @param $html
	 */
	private function htmlImageRelAttribute(&$html)
	{
		if($this->plugin_parameters['connect'])
		{
			if($this->plugin_parameters['view'] == 0 OR $this->plugin_parameters['view'] == 5)
			{
				$html .= ' rel="lightbox.sig'.$this->plugin_parameters['connect'].'"';
			}
			elseif($this->plugin_parameters['view'] == 1)
			{
				$html .= ' rel="lytebox.sig'.$this->plugin_parameters['connect'].'"';
			}
			elseif($this->plugin_parameters['view'] == 2)
			{
				$html .= ' rel="lyteshow.sig'.$this->plugin_parameters['connect'].'"';
			}
			elseif($this->plugin_parameters['view'] == 3)
			{
				$html .= ' rel="shadowbox[sig'.$this->plugin_parameters['connect'].']"';
			}
			elseif($this->plugin_parameters['view'] == 4)
			{
				$html .= ' data-milkbox="milkbox-'.$this->plugin_parameters['connect'].'"';
			}
			elseif($this->plugin_parameters['view'] == 6)
			{
				$html .= ' class="venobox" data-gall="venobox-'.$this->plugin_parameters['connect'].'"';
			}

			return;
		}

		if($this->plugin_parameters['view'] == 0 OR $this->plugin_parameters['view'] == 5)
		{
			$html .= ' rel="lightbox.sig'.$this->sigcount.'"';
		}
		elseif($this->plugin_parameters['view'] == 1)
		{
			$html .= ' rel="lytebox.sig'.$this->sigcount.'"';
		}
		elseif($this->plugin_parameters['view'] == 2)
		{
			$html .= ' rel="lyteshow.sig'.$this->sigcount.'"';
		}
		elseif($this->plugin_parameters['view'] == 3)
		{
			$html .= ' rel="shadowbox[sig'.$this->sigcount.']"';
		}
		elseif($this->plugin_parameters['view'] == 4)
		{
			$html .= ' data-milkbox="milkbox-'.$this->sigcount.'"';
		}
		elseif($this->plugin_parameters['view'] == 6)
		{
			$html .= ' class="venobox" data-gall="venobox-'.$this->sigcount.'"';
		}

		return;
	}

	/**
	 * Creates the title attribute data for a specific image
	 *
	 * @param $html
	 * @param $image_hash
	 * @param $image
	 * @param $image_title
	 * @param $image_description
	 */
	private function htmlImageAddTitleAttribute(&$html, $image_hash, $image, $image_title, $image_description = '')
	{
		if($this->plugin_parameters['displaynavtip'] AND !empty($this->plugin_parameters['navtip']))
		{
			$html .= $this->plugin_parameters['navtip'].'&lt;br /&gt;';
		}

		if($this->plugin_parameters['displaymessage'] AND !empty($this->article_title))
		{
			if(!empty($this->plugin_parameters['message']))
			{
				$html .= $this->plugin_parameters['message'].': ';
			}

			$html .= '&lt;em&gt;'.$this->article_title.'&lt;/em&gt;&lt;br /&gt;';
		}

		if($this->plugin_parameters['image_info'])
		{
			$html .= '&lt;strong&gt;&lt;em&gt;'.$image_title.'&lt;/em&gt;&lt;/strong&gt;';

			if(!empty($image_description))
			{
				$html .= ' - '.$image_description;
			}
		}

		if($this->plugin_parameters['print'] == 1)
		{
			$html .= ' &lt;a href=&quot;'.$this->live_site.'/plugins/content/sige/plugin_sige/print.php?img='.rawurlencode($this->htmlImagePrintPath($image, $image_hash)).'&amp;name='.rawurlencode($image_title).'&quot; title=&quot;Print&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;'.$this->live_site.'/plugins/content/sige/plugin_sige/print.png&quot; /&gt;&lt;/a&gt;';
		}

		if($this->plugin_parameters['download'] == 1)
		{
			$html .= ' &lt;a href=&quot;'.$this->live_site.'/plugins/content/sige/plugin_sige/download.php?img='.rawurlencode($this->htmlImageDownloadPath($image, $image_hash)).'&quot; title=&quot;Download&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;'.$this->live_site.'/plugins/content/sige/plugin_sige/download.png&quot; /&gt;&lt;/a&gt;';
		}
	}

	/**
	 * Returns the correct print path
	 *
	 * @param $image
	 * @param $image_hash
	 *
	 * @return string
	 */
	private function htmlImagePrintPath($image, $image_hash)
	{
		if($this->plugin_parameters['watermark'])
		{
			return $this->live_site.$this->root_folder.$this->images_dir.'/wm/'.$image_hash;
		}

		if($this->plugin_parameters['resize_images'])
		{
			return $this->live_site.$this->root_folder.$this->images_dir.'/resizedimages/'.$image;
		}

		return $this->live_site.$this->root_folder.$this->images_dir.'/'.$image;
	}

	/**
	 * Returns the correct download path
	 *
	 * @param $image
	 * @param $image_hash
	 *
	 * @return string
	 */
	private function htmlImageDownloadPath($image, $image_hash)
	{
		if($this->plugin_parameters['watermark'])
		{
			return $this->root_folder.$this->images_dir.'/wm/'.$image_hash;
		}

		if($this->plugin_parameters['resize_images'])
		{
			return $this->root_folder.$this->images_dir.'/resizedimages/'.$image;
		}

		return $this->root_folder.$this->images_dir.'/'.$image;
	}

	/**
	 * Creates the anchor tag code for a specific image
	 *
	 * @param $html
	 * @param $image_hash
	 * @param $image
	 * @param $image_title
	 * @param $image_description
	 * @param $image_link_file
	 */
	private function htmlImageAnchorTag(&$html, $image_hash, $image, $image_title, $image_description, $image_link_file)
	{
		if($this->plugin_parameters['image_link'] OR !empty($image_link_file))
		{
			// Use link from captions.txt if provided
			if(!empty($image_link_file))
			{
				// Add http:// if not already set
				if(!preg_match('@http.?://@', $image_link_file))
				{
					$image_link_file = 'http://'.$image_link_file;
				}

				$html .= '<a href="'.$image_link_file.'" title="'.$image_link_file.'" ';
			}
			else
			{
				$html .= '<a href="http://'.$this->plugin_parameters['image_link'].'" title="'.$this->plugin_parameters['image_link'].'" ';
			}

			if($this->plugin_parameters['image_link_new'])
			{
				$html .= 'target="_blank"';
			}

			$html .= '>';

			return;
		}

		if($this->plugin_parameters['noslim'] AND $this->plugin_parameters['css_image'])
		{
			$html .= '<a class="sige_css_image" href="#sige_thumbnail">';

			return;
		}

		if(!$this->plugin_parameters['noslim'])
		{
			if($this->plugin_parameters['watermark'])
			{
				$html .= '<a href="'.$this->live_site.$this->root_folder.$this->images_dir.'/wm/'.$image_hash.'"';
			}
			elseif($this->plugin_parameters['resize_images'])
			{
				$html .= '<a href="'.$this->live_site.$this->root_folder.$this->images_dir.'/resizedimages/'.$image.'"';
			}
			else
			{
				$html .= '<a href="'.$this->live_site.$this->root_folder.$this->images_dir.'/'.$image.'"';
			}

			if($this->plugin_parameters['css_image'])
			{
				$html .= ' class="sige_css_image';

				// Add Venobox class if this JS application is selected
				if($this->plugin_parameters['view'] == 6)
				{
					$html .= ' venobox';
				}

				$html .= '"';
			}

			$this->htmlImageRelAttribute($html);

			$html .= ' title="';

			$this->htmlImageAddTitleAttribute($html, $image_hash, $image, $image_title, $image_description);

			$html .= '" >';

			return;
		}
	}

	/**
	 * Creates the image tag code for a specific image
	 *
	 * @param $html
	 * @param $image_hash
	 * @param $image
	 * @param $image_title
	 * @param $image_alt
	 * @param $image_description
	 */
	private function htmlImageImgTag(&$html, $image_hash, $image, $image_title, $image_alt, $image_description)
	{
		if($this->plugin_parameters['thumbs'])
		{
			$html .= '<img alt="'.$image_alt.'" title="'.$image_title;

			if(!empty($image_description))
			{
				$html .= ' - '.$image_description;
			}

			if($this->plugin_parameters['watermark'])
			{
				$html .= '" src="'.$this->live_site.$this->root_folder.$this->images_dir.'/thumbs/'.$image_hash.'" />';
			}
			else
			{
				$html .= '" src="'.$this->live_site.$this->root_folder.$this->images_dir.'/thumbs/'.$image.'" />';
			}

			return;
		}

		$this->htmlImageImgTagDynamic($html, $image_hash, $image, $image_title, $image_alt, $image_description);
	}

	/**
	 * Creates the image tag code for a specific image using on-the-fly thumbnail generation
	 *
	 * @param $html
	 * @param $image_hash
	 * @param $image
	 * @param $image_title
	 * @param $image_alt
	 * @param $image_description
	 */
	private function htmlImageImgTagDynamic(&$html, $image_hash, $image, $image_title, $image_alt, $image_description)
	{
		$html .= '<img alt="'.$image_alt.'" title="'.$image_title;

		if($image_description)
		{
			$html .= ' - '.$image_description;
		}

		if($this->plugin_parameters['watermark'])
		{
			$html .= '" src="'.$this->live_site.'/plugins/content/sige/plugin_sige/showthumb.php?img='.$this->root_folder.$this->images_dir.'/wm/'.$image_hash.'&amp;width='.$this->plugin_parameters['width'].'&amp;height='.$this->plugin_parameters['height'].'&amp;quality='.$this->plugin_parameters['quality'].'&amp;ratio='.$this->plugin_parameters['ratio'].'&amp;crop='.$this->plugin_parameters['crop'].'&amp;crop_factor='.$this->plugin_parameters['crop_factor'].'&amp;thumbdetail='.$this->plugin_parameters['thumbdetail'].'" />';

			return;
		}

		if($this->plugin_parameters['resize_images'])
		{
			$html .= '" src="'.$this->live_site.'/plugins/content/sige/plugin_sige/showthumb.php?img='.$this->root_folder.$this->images_dir.'/resizedimages/'.$image.'&amp;width='.$this->plugin_parameters['width'].'&amp;height='.$this->plugin_parameters['height'].'&amp;quality='.$this->plugin_parameters['quality'].'&amp;ratio='.$this->plugin_parameters['ratio'].'&amp;crop='.$this->plugin_parameters['crop'].'&amp;crop_factor='.$this->plugin_parameters['crop_factor'].'&amp;thumbdetail='.$this->plugin_parameters['thumbdetail'].'" />';

			return;
		}

		$html .= '" src="'.$this->live_site.'/plugins/content/sige/plugin_sige/showthumb.php?img='.$this->root_folder.$this->images_dir.'/'.$image.'&amp;width='.$this->plugin_parameters['width'].'&amp;height='.$this->plugin_parameters['height'].'&amp;quality='.$this->plugin_parameters['quality'].'&amp;ratio='.$this->plugin_parameters['ratio'].'&amp;crop='.$this->plugin_parameters['crop'].'&amp;crop_factor='.$this->plugin_parameters['crop_factor'].'&amp;thumbdetail='.$this->plugin_parameters['thumbdetail'].'" />';

		return;
	}

	/**
	 * Creates the image tag code for a specific image using the CSS image tooltip
	 *
	 * @param $html
	 * @param $image_hash
	 * @param $image
	 * @param $image_title
	 * @param $image_alt
	 * @param $image_description
	 */
	private function htmlImageImgTagCssImage(&$html, $image_hash, $image, $image_title, $image_alt, $image_description)
	{
		$html .= '<span>';

		if($this->plugin_parameters['watermark'])
		{
			$html .= '<img src="'.$this->live_site.$this->root_folder.$this->images_dir.'/wm/'.$image_hash.'"';
		}
		else
		{
			if($this->plugin_parameters['resize_images'])
			{
				$html .= '<img src="'.$this->live_site.$this->root_folder.$this->images_dir.'/resizedimages/'.$image.'"';
			}
			else
			{
				$html .= '<img src="'.$this->live_site.$this->root_folder.$this->images_dir.'/'.$image.'"';
			}
		}

		if($this->plugin_parameters['css_image_half'] AND !$this->plugin_parameters['list'])
		{
			$imagedata = getimagesize($this->absolute_path.$this->root_folder.$this->images_dir.'/'.$image);
			$html .= ' width="'.($imagedata[0] / 2).'" height="'.($imagedata[1] / 2).'"';
		}

		$html .= ' alt="'.$image_alt.'" title="'.$image_title;

		if($image_description)
		{
			$html .= ' - '.$image_description;
		}

		$html .= '" /></span>';
	}

	/**
	 * Adds image caption to a specific image
	 *
	 * @param $html
	 * @param $image_title
	 */
	private function htmlImageCaption(&$html, $image_title)
	{
		if(!$this->plugin_parameters['list'] AND !$this->plugin_parameters['word'])
		{
			if($this->plugin_parameters['single'] AND !empty($this->plugin_parameters['scaption']))
			{
				$html .= '</span><span class="sige_caption">'.$this->plugin_parameters['scaption'].'</span></li>';
			}
			else
			{
				$html .= '</span><span class="sige_caption">'.$image_title.'</span></li>';
			}
		}
	}

	/**
	 * Compares timestamps of images - ascending
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return int
	 */
	private function timeasc($a, $b)
	{
		return strcmp($a['timestamp'], $b['timestamp']);
	}

	/**
	 * Compares timestamps of images - descending
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return int
	 */
	private function timedesc($a, $b)
	{
		return strcmp($b['timestamp'], $a['timestamp']);
	}
}
