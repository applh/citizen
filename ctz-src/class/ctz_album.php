<?php

/*
Class:        Ctz_Album
creation:     22/01/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_album extends Ctz_object
{
   /* MEMBERS */
   public $uri;
   public $dir;
   public $parent;

   public $tabtabscene;

   public $tabtext;

   public $tabheader;

   public $tableft;
   public $prolog;
   public $tabscene;
   public $tabcenter;
   public $epilog;
   public $tabright;

   public $tabfooter;

   public $contact_htm;

   public $tabjpg;
   public $tabpng;
   public $tabgif;

   public $taballfiles;
   public $tabmeta;

   /* METHODS */

   public function __construct ($uri) {
      parent::__construct();

      $pathinfo=pathinfo($uri);
      if ($pathinfo['extension']) {
         // uri like /uri/file.ext
         $this->uri=$pathinfo['dirname'];
      }
      else {
         // uri like /uri or /uri/
         $this->uri=$uri;
      }

      $this->init();
   }

   public function __destruct () {
      parent::__destruct();
   }

   public function init () {

      $this->tabtabscene=array();

      $useuri=$this->uri;
      // some security
      $useuri=preg_replace(",[^a-z0-9-/.],", "", $useuri);

      $system=ctz_var('SYSTEM');
      $this->dir=$system->get_dir_album($useuri, true);

      // load rules
      $this->load_meta();
      // load media
      $this->load_scene();

   }

   public function check_redirect () {
      $redirect=ctz_var('album.redirect');
      return $redirect;
   }

   public function is_file () {
      $bn=basename($this->uri);
      if ($bn) {
         $f=$this->dir."/".$bn;
         $res=@file_exists($f);
      }
      return $res;
   }

   public function is_root () {
      $updir=dirname($this->uri);
      return ($updir == "");
   }

   public function get_parent () {
      if (!$this->parent) {
         if ($this->uri) {
            $this->parent=new Ctz_album(dirname($this->uri));
         }
      }
      return $this->parent;
   }

   public function load_meta () {
      if ($this->uri) {
         $system=ctz_var('SYSTEM');
         $this->tabmeta=$system->get_album_meta($this->uri);
         foreach($this->tabmeta as $var => $val) {
            // export the values
            ctz_set("album.$var", $val);
         }
      }
   }

   public function load_scene () {
      $system=ctz_var('SYSTEM');
      if ($this->uri) {
         $this->taballfiles=$system->get_album_files($this->uri);
      }
      if (is_dir($this->dir)) {
         // should improve by moving to the system
         // then load all album files once
         // filter the list to get elements
         $this->tableft=glob($this->dir."/left*.txt");
         $this->tabprolog=glob($this->dir."/prolog*.txt");
         $this->tabscene=glob($this->dir."/scene*.txt");
         $this->tabcenter=glob($this->dir."/center*.txt");
         $this->tabepilog=glob($this->dir."/epilog*.txt");
         $this->tabright=glob($this->dir."/right*.txt");

         $this->tabheader=glob($this->dir."/header*.txt");
         $this->tabfooter=glob($this->dir."/footer*.txt");

         $this->tabjpg=glob($this->dir."/*.jpg");
         $this->tabpng=glob($this->dir."/*.png");
         $this->tabgif=glob($this->dir."/*.gif");
      }
   }

   public function get_nbscene () {
      //DEV: to change as it doesn't count indexes
      //BUG: center-0, scene-1, center-2 => 2 scenes WRONG :-(
      if (!$this->tabscene && !$this->tabcenter) {
         $this->load_scene();
      }
      $tabnb=array(
         count($this->tableft),
         count($this->tabprolog),
         count($this->tabscene),
         count($this->tabcenter),
         count($this->tabepilog),
         count($this->tabright),
      );
      return max($tabnb);
   }

   public function get_scene ($s) {

      // use cache if available
      if (is_array($this->tabtabscene[$s])) {
         return $this->tabtabscene[$s];
      }

      $res=array();
      if (is_array($this->tabprolog)) {
         foreach($this->tabprolog as $j => $name) {
            list($index)=sscanf(basename($name), "prolog%d.txt");
            $index=abs(intval($index));
            if ($index == $s) {
               if (is_file($name)) {
                  $res["prolog"]=file_get_contents($name);
               }
            }
         }
      }

      if (is_array($this->tabscene)) {
         foreach($this->tabscene as $j => $name) {
            list($index)=sscanf(basename($name), "scene%d.txt");
            $index=abs(intval($index));
            if ($index == $s) {
               if (is_file($name)) {
                  $res["scene"]=file_get_contents($name);
               }
            }
         }
      }

      if (is_array($this->tabepilog)) {
         foreach($this->tabepilog as $j => $name) {
            list($index)=sscanf(basename($name), "epilog%d.txt");
            $index=abs(intval($index));
            if ($index == $s) {
               if (is_file($name)) {
                  $res["epilog"]=file_get_contents($name);
               }
            }
         }
      }

      if (is_array($this->tableft)) {
         foreach($this->tableft as $j => $name) {
            list($index)=sscanf(basename($name), "left%d.txt");
            $index=abs(intval($index));
            if ($index == $s) {
               if (is_file($name)) {
                  $res["left"]=file_get_contents($name);
               }
            }
         }
      }

      if (is_array($this->tabcenter)) {
         foreach($this->tabcenter as $j => $name) {
            list($index)=sscanf(basename($name), "center%d.txt");
            $index=abs(intval($index));
            if ($index == $s) {
               if (is_file($name)) {
                  $res["center"]=file_get_contents($name);
               }
            }
         }
      }

      if (is_array($this->tabright)) {
         foreach($this->tabright as $j => $name) {
            list($index)=sscanf(basename($name), "right%d.txt");
            $index=abs(intval($index));
            if ($index == $s) {
               if (is_file($name)) {
                  $res["right"]=file_get_contents($name);
               }
            }
         }
      }

      if (is_array($this->tabheader)) {
         foreach($this->tabheader as $j => $name) {
            list($index)=sscanf(basename($name), "header%d.txt");
            $index=abs(intval($index));
            if ($index == $s) {
               if (is_file($name)) {
                  $res["header"]=file_get_contents($name);
               }
            }
         }
      }

      if (is_array($this->tabfooter)) {
         foreach($this->tabfooter as $j => $name) {
            list($index)=sscanf(basename($name), "footer%d.txt");
            $index=abs(intval($index));
            if ($index == $s) {
               if (is_file($name)) {
                  $res["footer"]=file_get_contents($name);
               }
            }
         }
      }

      // set cache 
      $this->tabtabscene[$s]=$res;
         
      return $res;
   }

   function get_contact_form () {
      $f=$this->dir."/contact.htm";
      if (is_file($f)) {
         $res=file_get_contents($f);
      }
      return $res;
   }

}

?>
