<?php

/*
Class:        Ctz_plugin_robot
creation:     25/02/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_plugin_robot extends Ctz_plugin
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
      $this->tab_action=array("plugin-robot");

   }
   
   public function activate ($action, $object) {
      $system=ctz_var('SYSTEM');

      switch ($action) {
      case 'plugin-robot':
         // activate cache clean up
         ctz_set('cache.clean', 'active');
         break;
      default:

         break;
      }
   }
}

?>
