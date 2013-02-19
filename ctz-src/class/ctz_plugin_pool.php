<?php

/*
Class:        Ctz_plugin_pool
creation:     02/02/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_plugin_pool extends Ctz_object
{
   /* MEMBERS */
   public $isready;
   public $tabplugin;
   public $tabaction;
   /* METHODS */

   public function __construct () {
      parent::__construct();
      $this->isready=false;
      $this->init();
   }

   public function __destruct () {
      parent::__destruct();
   }

   public function init () {
      if (!$this->isready) {
         $this->load_plugins();
      }
   }

   public function load_plugins () {
      $system=ctz_var('SYSTEM');
      $this->tabaction=array();
      $this->tabplugin=$system->load_plugins();
      foreach($this->tabplugin as $p => $plugin) {
         // register each plugin action
         $tabpaction=$plugin->get_tabaction();
         if (is_array($tabpaction)) {
            foreach($tabpaction as $i => $action) {
               // find the group
               $group=$this->tabaction[$action];
               // create if not found
               if (!$group) 
                  $group=array();
               // add plugin to the group
               $group[]=$plugin;
               // store the group
               $this->tabaction[$action]=$group;
            }
         }
      }

      $this->isready=true;
   }

   public function activate ($action, $object) {
      if (!is_array($this->tabaction)) 
         return;
      
      $groupaction=$this->tabaction[$action];
      if (!is_array($groupaction)) 
         return;

      foreach($groupaction as $p => $plugin) {
         $plugin->activate($action, $object);
      }
   }

}

?>
