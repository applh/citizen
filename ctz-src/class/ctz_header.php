<?php

/*
Class:        Ctz_header
creation:     22/01/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_header extends Ctz_object
{
   /* MEMBERS */
   public $tabline;

   /* METHODS */

   public function __construct () {
      parent::__construct();
      $this->init();
   }

   public function __destruct () {
      parent::__destruct();
   }

   public function init () {
      $request=ctz_var('REQUEST');

      $ct=$request->get_content_type();
      $this->tabline=array(
         "Content-Type" => $ct,
      );
      switch ($ct) {
      case 'text/css':
      case 'text/javascript':
         $this->tabline["Vary"]="Accept-Encoding";
         $this->tabline["Cache-Control"]="public";
         break;
      default:
      break;
      }

   }

   public function get_content () {
      if (!headers_sent()) {
         foreach($this->tabline as $i => $l) {
             header("$i: $l");
         }
      }
   }

}

?>
