<?php

/*
Class:        Ctz_plugin
creation:     03/02/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_plugin extends Ctz_object
{
   /* MEMBERS */
   public $tab_action;

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

   public function get_tabaction () {
      return $this->tab_action;
   }

   public function activate ($action, $object) {
   }
}

?>
