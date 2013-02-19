<?php

/*
Class:        Ctz_image
creation:     02/02/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_image extends Ctz_object
{
   /* MEMBERS */
   public $uri;
   public $adir;
   public $basename;
   public $info;

   public $wmax;
   public $hmax;

   /* METHODS */

   public function __construct ($uri) {
      parent::__construct();

      $referer=$_SERVER['HTTP_REFERER'];

      if (stripos($referer, 'http://'.$_SERVER['SERVER_NAME']) === FALSE) {
         $this->wmax=1920;
         $this->hmax=1080;
      }
      else {
         $this->wmax=800;
         $this->hmax=500;
      }

      $this->uri=$uri;
      $this->basename=basename($uri);
      $this->init();
   }

   public function __destruct () {
      parent::__destruct();
   }

   public function init () {
      $system=ctz_var('SYSTEM');
      $this->adir=$system->get_dir_album($this->uri);

      $extrainfo=array();
      $file=$this->adir."/".$this->basename;
      if (is_file($file)) {
         $this->info=@getimagesize($file, $extrainfo);
         if (is_array($this->info)) {
            $this->info=$this->info+$extrainfo;
         }
      }
   }

   public function get_data () {
      //DEV: HAS TO MANAGE TRANSPARENCY FOR PNG AND GIF
      //
      $file=$this->adir."/".$this->basename;
      if (!is_file($file))
         return;

      switch ($this->info['mime']) {
      case 'image/jpeg':
         $image=@imagecreatefromjpeg($file);
         break;
      case 'image/gif':
         $image=@imagecreatefromgif($file);
         break;
      case 'image/png':
         $image=@imagecreatefrompng($file);
         break;
      default:
            break;
      }
      $w0=$this->info[0];
      $h0=$this->info[1];

      $r0=$w0/$h0;
      $rmax=$this->wmax/$this->hmax;
      if ($r0 < $rmax) {
         $h=$this->hmax;
         $w=round($w0*$this->hmax/$h0);
      }
      else {
         $w=$this->wmax;
         $h=round($h0*$this->wmax/$w0);
      }

      $resize=imagecreatetruecolor($w, $h);
      imagecopyresampled($resize, $image, 0, 0, 0, 0, $w, $h, $w0, $h0);
      imagedestroy($image);

      $system=ctz_var('SYSTEM');
      $tmpname=tempnam($system->get_dir('tmp', true), 'ctz_tmp_');
      switch ($this->info['mime']) {
      case 'image/jpeg':
         @imagejpeg($resize, $tmpname);
         break;
      case 'image/gif':
         @imagegif($resize, $tmpname);
         break;
      case 'image/png':
         @imagepng($resize, $tmpname);
         break;
      default:
            break;
      }
      imagedestroy($resize);

      $res=file_get_contents($tmpname);
      unlink($tmpname);
      return $res;
   }
}

?>
