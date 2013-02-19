<?php

/*
Class:        Ctz_log
creation:     29/01/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_log extends Ctz_object
{
   /* MEMBERS */

   /* METHODS */

   public function __construct () {
      parent::__construct();
      $this->init();
   }

   public function __destruct () {
      parent::__destruct();
   }

   public function init () {
      $system=ctz_var('SYSTEM');
      
      $system->logip();

      // register as global object
      ctz_set('LOG', $this);
   }
}

?>
