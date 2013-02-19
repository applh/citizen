<?php

/*
Class:        Ctz_Error
creation:     22/01/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_Error extends Ctz_object
{
   /* MEMBERS */
   public $message;

   /* METHODS */

   public function __construct ($error="") {
      parent::__construct();
      $this->init();
      if ($error) $this->message=$error;
   }

   public function __destruct () {
      parent::__destruct();
   }

   public function init () {

   }
}

?>
