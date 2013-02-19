<?php

/*
Class:        Ctz_memory
creation:     10/02/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_memory extends Ctz_object
{
   /* MEMBERS */
   public $dir;
   public $file_ini;
   public $file_txt;
   public $update;
   public $file_content;
   public $tabvar;

   public $tabdata;
   /* METHODS */

   public function __construct () {
      parent::__construct();
      $this->init();
   }

   public function __destruct () {
      // update if necessary
      $this->update();
      parent::__destruct();
   }

   public function init () {
      $system=ctz_var('SYSTEM');

      // some default values
      ctz_set('lang', 'en');

      $this->dir=$system->get_dir('memory', true);
      $this->file_ini=$this->dir."/site.ini";
      $this->file_txt=$this->dir."/site.txt";
      $this->update=false;
      //$this->file_content="hostname=".$_SERVER['SERVER_NAME'];
      //$this->file_content=array(123, "hello", "coucou");
      //$this->update();
      $this->load();
   }

   public function load () {
      if (is_file($this->file_ini)) {
         $this->tabvar=parse_ini_file($this->file_ini);
      }
      if (is_array($this->tabvar)) {
         foreach($this->tabvar as $var => $val) {
            if ($val) {
               $var=preg_replace('/[^a-z0-9_.-]/', '_', $var);
               ctz_set("site.$var", $val);
            }
         }
      }
      if (is_file($this->file_txt)) {
         $this->tabdata=$system->load_var($this->file_txt, $this, 'tabdata');
         $this->tabdata=unserialize($this->tabdata);
      }
   }

   public function update () {
      if ($this->update) {
         $system=ctz_var('SYSTEM');
         $system->write_file($this->file_ini, $this, "file_content");
         $system->write_file($this->file_txt, $this, "tabdata");
      }
   }
}

?>
