<?php

/*
Class:        Ctz_app
creation:     22/01/2010
modification: CTZDATEMODIFICATION
author:       LH
*/


class Ctz_app extends Ctz_object
{
   public $system;
   public $log;
   public $security;
   public $site;
   public $request;
   public $memory;

   public function __construct () {
      parent::__construct();
      $this->init();
   }

   public function __destruct () {
      parent::__destruct();
   }
   
   public function init () {
      // init the system
      $this->system=new Ctz_system();

      // check if security is ok ?
      $stop=ctz_var('security.stop');
      if ($stop) {
         header("HTTP/1.0 404 Not Found");
      }
      else {
         if (ctz_var('system_error')) {
            $installer=new Ctz_installer($this->system);
            $this->system->init();
         }
         // init the logs
         $this->log=new Ctz_log();

         // init the app memory
         $this->memory=new Ctz_memory();

         // basic security
         $this->security=new Ctz_security();

         // check if security is ok ?
         $stop=ctz_var('security.stop');

      }

      if (!$stop) {
         // load plugins and themes
         $this->custom('app-init');
 
         // parse the request
         $this->request=new Ctz_request();
         // setup the site
         $this->site=new Ctz_site();

         // get the response
         $this->site->get_response();
      }
      else {
         header("HTTP/1.0 404 Not Found");
      }

   }
}

?>
