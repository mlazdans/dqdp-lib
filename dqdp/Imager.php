<?php declare(strict_types = 1);

namespace dqdp;

use Closure;
use Exception;
use Imagick;
use ImagickPixel;

class Imager
{
	var $im;
	var $root;
	var $config = [];

	function __construct(){
		$this->im = new Imagick();
		$this->set_root(".");
	}

	function set_root($root): Imager {
		$this->root = $root;

		return $this;
	}

	# $max_w=NULL, $max_h=NULL, $format='webp', $quality=75
	# $aspect_ver = W/H, $aspect_hor = W/H, autorotate=true
	# $crop = false, frame = [], upscale = false
	function add_config($config): Imager {
		$this->config[] = eo($config);

		return $this;
	}

	# filename, fd, blob, processor
	# TODO: params class
	function process_image($params): bool {
		$params = eo($params);
		try {
			if($params->filename){
				$this->im->readImage($params->filename);
			} elseif($params->fd){
				$this->im->readImageFile($params->filename);
			} elseif($params->blob){
				$this->im->readImageBlob($params->blob);
			} elseif($params->Imagick && $params->Imagick instanceof Imagick){
				$this->im = $params->Imagick;
			} else {
				trigger_error("Missing config");
				return false;
			}
		} catch (Exception $e){
			trigger_error($e->getMessage());
			return false;
		}

		//file_put_contents("$this->root/original", $this->im->getImageBlob());
		foreach($this->config as $config){
			$c = eo([
				'format'=>'webp',
				'quality'=>75,
				'autorotate'=>true,
				'upscale'=>false,
				'crop'=>false,
				'frame'=>false
			])->merge($config);

			if(empty($c->max_w) && empty($c->max_h)){
				trigger_error("Invalid dimensions: required max_w or max_h");
				return false;
			}

			$I = clone $this->im;
			if($c->autorotate){
				$this->autorotate($I);
			}

			try {
				$I->setImageFormat($c->format);
				$I->setImageCompressionQuality($c->quality);
				$c->W = $I->getImageWidth();
				$c->H = $I->getImageHeight();
			} catch (Exception $e){
				trigger_error($e->getMessage());
				return false;
			}

			$c->is_vertical = $c->H > $c->W;
			$c->is_smaller = ($c->W < $c->max_w) || ($c->H < $c->max_h);
			$c->aspect_o = round($c->W / $c->H, 3);

			# NOTE: filename Jāatstāj šeit pirms uzstāda otru default max_w/max_h
			$c->filename = "$this->root/".($c->filename??"$c->max_w"."x$c->max_h").".$c->format";
			$c->aspect_hor = $c->aspect_hor??$c->aspect_o;
			$c->aspect_ver = $c->aspect_ver??$c->aspect_o;
			$c->aspect = $c->is_vertical ? $c->aspect_ver : $c->aspect_hor;

			if(empty($c->max_w)){
				$c->max_w = round($c->max_h * $c->aspect, 3);
			}

			if(empty($c->max_h)){
				$c->max_h = round($c->max_w / $c->aspect, 3);
			}

			# NOTE: frame jāastāj pirms crop pārēķina
			if($c->frame){
				$c->frame = eo([
					'bg'=>'black',
					'w'=>$c->max_w,
					'h'=>$c->max_h,
				])->merge(is_scalar($c->frame) ? [] : $c->frame);
			}

			if($c->is_smaller && !$c->upscale){
				$c->max_w = min($c->max_w, $c->W);
				$c->max_h = min($c->max_h, $c->H);
			} elseif($c->crop) {
			} else {
				if($c->is_vertical){
					$c->max_w = round($c->max_h * $c->aspect, 3);
				} else {
					$c->max_h = round($c->max_w / $c->aspect, 3);
				}
			}

			try {
				$I->cropThumbnailImage($c->max_w, $c->max_h);
				if($frame = $c->frame){
					$I->setImageBackgroundColor(new ImagickPixel($frame->bg));
					$I->extentImage($frame->w, $frame->h, ($c->max_w - $frame->w)/2, ($c->max_h - $frame->h)/2);
				}

				if($params->processor){
					if(is_callable([$params, 'processor']) || $params->processor instanceof Closure){
						if(!$params->processor->__invoke($I, $c)){
							return false;
						}
					}
				} else {
					$I->writeImage($c->filename);
				}
				$I->destroy();
			} catch (Exception $e){
				trigger_error($e->getMessage());
				return false;
			}
		}

		return true;
	}

	function autorotate(Imagick $image): bool {
		try {
			switch ($image->getImageOrientation()) {
				case Imagick::ORIENTATION_TOPLEFT:
					break;
				case Imagick::ORIENTATION_TOPRIGHT:
					$image->flopImage();
					break;
				case Imagick::ORIENTATION_BOTTOMRIGHT:
					$image->rotateImage("#000", 180);
					break;
				case Imagick::ORIENTATION_BOTTOMLEFT:
					$image->flopImage();
					$image->rotateImage("#000", 180);
					break;
				case Imagick::ORIENTATION_LEFTTOP:
					$image->flopImage();
					$image->rotateImage("#000", -90);
					break;
				case Imagick::ORIENTATION_RIGHTTOP:
					$image->rotateImage("#000", 90);
					break;
				case Imagick::ORIENTATION_RIGHTBOTTOM:
					$image->flopImage();
					$image->rotateImage("#000", 90);
					break;
				case Imagick::ORIENTATION_LEFTBOTTOM:
					$image->rotateImage("#000", -90);
					break;
				default: // Invalid orientation
					break;
			}

			$image->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);

			return true;
		} catch (Exception $e){
			trigger_error($e->getMessage());
			return false;
		}
	}
}
