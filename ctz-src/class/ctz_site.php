<?php

/*
Class:        Ctz_site
creation:     22/01/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_site extends Ctz_object
{
   /* MEMBERS */
   public $response;

   /* METHODS */

   public function __construct () {
      parent::__construct();
      $this->init();
   }

   public function __destruct () {
      parent::__destruct();
   }

   public function init () {
   }

   public function get_response () {

      // check and process for if needed
      $this->process_form();

      if (!$this->response) {
         $this->response=new Ctz_response();
      }

      return $this->response;
   }

   public function process_form () {
      $processform=ctz_var('process_form');
      
      if (!$processform) return;

      $system=ctz_var('SYSTEM');
      $write=$system->store_message();
      if ($write === FALSE) {
         $res='ERROR - Please try again later!';
      }
      else {
         $res='OK - Thanks for your message!';
      }
      ctz_set('response_ajax', $res);
   }
}

?>
