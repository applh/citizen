<?php

/*
Class:        Ctz_plugin_mailer
creation:     03/02/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_plugin_mailer extends Ctz_plugin
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
         $this->tab_action=array("store-message");
   }

   public function activate ($action, $object) {
      switch ($action) {
      case 'store-message':
         $system=ctz_var('SYSTEM');
         $system->send_mail($object);
         break;
      default:
         break;
      }
   }

}

?>
