<?php

/*
Class:        Ctz_cache
creation:     22/01/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_cache extends Ctz_object
{
   /* MEMBERS */
   public $active;

   public $cachedir;
   public $maxage; // in seconds
   public $maxsize; // in bytes
   public $minsize; // in bytes

   public $tabfiles;

   /* METHODS */

   public function __construct () {
      parent::__construct();
      $this->init();
   }

   public function __destruct () {

      // only active when request to plugin_robot
      if (ctz_var('cache.clean')) {
         $this->clean_cache();
      }

      parent::__destruct();
   }

   public function init () {
      $system=ctz_var('SYSTEM');
      $this->cachedir=$system->get_dir('cache', true);

      $this->maxage=300; // 5 minutes
      $this->maxsize=20000000.0; // 20 MB
      $this->minsize=-1; // no bytes minimum

      // override with user value ?
      $usermaxage=ctz_var('cache.maxage');
      if ($usermaxage) 
         $this->maxage=$usermaxage;
      // override with user value ?
      $usermaxsize=ctz_var('cache.maxsize');
      if ($usermaxsize) 
         $this->maxsize=$usermaxsize;
      // override with user value ?
      $userminsize=intval(ctz_var('cache.minsize'));
      if ($userminsize) 
         $this->minsize=$userminsize;

      $this->active=true;

      //DEV: don't cache AJAX request
      // should improve to take into account REQUEST parameters in cache file
      if ( $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) {
         $this->active=false;
      }
   }

   public function get_key ($uri) {
      $referer=$_SERVER['HTTP_REFERER'];
      if (!empty($referer)) {
         $referer="__MWA_REFERER";
      }
      //$code=$uri.serialize($_REQUEST).$_SERVER['HTTP_REFERER'];
      //$code=$uri.serialize($_REQUEST).$referer;
      $code=$uri.serialize($_REQUEST);
      $res=md5($code);
      return $res;
   }

   public function get_key2 ($uri) {
      $code=$uri.serialize($_REQUEST);
      $code2=md5($code);
      $res=substr($code2, 0, 3).'/'.$code2;

      return $res;
   }

   public function has_cache ($uri) {
      $res=false;

      if (!$this->active) return $res;

      $file=$this->cachedir."/".$this->get_key2($uri);
      // get the updated info
      clearstatcache();

      if (is_file($file)) {
         $mtime=filemtime($file);
         $now=time();
         $delta=$now-$mtime;
         if ($delta < $this->maxage) {
            $res=true;
         }
         if ($res && ($this->minsize > -1)) {
            $fsize=filesize($file);
            if ($fsize < $this->minsize) {
               $res=false;
            }
         }
      }
      return $res;
   }

   public function get_cache ($uri) {
      $res=false;
      if (!$this->active) return $res;

      $file=$this->cachedir."/".$this->get_key2($uri);
      if (is_file($file)) {
         $res=@file_get_contents($file);
      }
      return $res;
   }

   public function set_cache ($uri, &$data) {
      $res=false;

      if (!$this->active) return $res;

      $file=$this->cachedir."/".$this->get_key2($uri);

      $cachedir=dirname($file);
      if (!is_dir($cachedir)) mkdir($cachedir);

      if (!ctz_var('cache-image-resource')) {
         $res=file_put_contents($file, $data);
      }
      else {
         $res=file_put_contents($file, $data);
      }

      return $res;
   }

   public function empty_old () {
      $now=time();
      $maxage=2*$this->maxage;
      foreach($this->tabfiles as $f) {
         if ($now - filemtime($f) > $maxage) {
            unlink($f);
	 }
      }

   }

   public function clean_cache () {
      // sounds that disk_total_space returns all available space and not used space
      // doesn't work on all systems (e.g. OVH :-( )
      //$totalsize=(float) @disk_total_space($this->cachedir);
      //$free=$this->maxsize - $totalsize;

      $file=$this->cachedir."/*";
      $this->tabfiles=glob($file);
      $free=$this->maxsize;
      foreach($this->tabfiles as $i => $f) {
         $fsize=filesize($f);
         $free-= $fsize;
         if ($free < 0) {
            $this->empty_old();
	    break;
         }
      }
   }

   public function process_queue () {
   }
}

?>
