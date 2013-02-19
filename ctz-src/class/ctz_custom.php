<?php

/*
Class:        Ctz_custom
creation:     02/02/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_custom extends Ctz_object
{
   /* MEMBERS */
   public $pluginpool;
   public $themepool;

   public $action;
   public $object;

   /* METHODS */

   public function __construct ($action, $object=null) {
      parent::__construct();

      $this->action=$action;
      $this->object=$object;
      $this->init();
   }

   public function __destruct () {
      parent::__destruct();
   }

   public function init () {

      // setup global plugin and theme pools
      $this->pluginpool=ctz_var('PLUGIN_POOL');
      if (!$this->pluginpool) {
         $this->pluginpool=new Ctz_plugin_pool();
         ctz_set('PLUGIN_POOL', $this->pluginpool);
      }

      $this->themepool=ctz_var('THEME_POOL');
      if (!$this->themepool) {
         $this->themepool=new Ctz_theme_pool();
         ctz_set('THEME_POOL', $this->themepool);
      }

      $this->pluginpool->activate($this->action, $this->object);
      $this->themepool->activate($this->action, $this->object);

   }
}

?>
