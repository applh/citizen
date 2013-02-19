<?php

/*
Class:        Ctz_installer
creation:     22/01/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_installer extends Ctz_object
{
   /* MEMBERS */
   public $system;

   public $cursite;
   public $sitedir;

   /* METHODS */

   public function __construct ($system) {
      parent::__construct();
      $this->system=$system;
      $this->init();
   }

   public function __destruct () {
      parent::__destruct();
   }

   public function init () {
      $datadir=ctz_var('DATA_DIR');
      $this->cursite=$_SERVER['SERVER_NAME'];

      if (is_writeable($datadir)) {
         $sitemd5=$this->system->get_sitemd5();
         $siteindex=$sitemd5[0];
         if (!is_dir("$datadir/websites/$siteindex/$sitemd5")) {
            $this->install_site($this->cursite);
         }

         if (!$this->check_site($this->cursite)) {
         }
      }
   }

   public function install_site ($servername) {
      $datadir=ctz_var('DATA_DIR');
      $sitemd5=$this->system->get_sitemd5();
      $siteindex=$sitemd5[0];
      $this->sitedir="$datadir/websites/$siteindex/$sitemd5";
      if (!is_dir($this->sitedir)) {
         @mkdir($this->sitedir, ctz_var('CHMOD_DIR'), true);         
      }
   }

   public function check_site ($servername) {
      $res=true;

      // check albums dir
      $albumsdir=$this->sitedir."/".CTZ_ALBUMS;
      if (!is_dir($albumsdir)) {
         @mkdir($albumsdir, ctz_var('CHMOD_DIR'), true);
      }

      return $res;
   }
}

?>
