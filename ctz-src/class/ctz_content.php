<?php

/*
Class:        Ctz_content
creation:     22/01/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_content extends Ctz_object
{
   /* MEMBERS */
   public $type;
   public $data;
   public $html;

   /* METHODS */

   public function __construct () {
      parent::__construct();
      $this->init();
   }

   public function __destruct () {
      parent::__destruct();
   }

   public function init () {
      $this->html=new Ctz_html();
   }

   public function get_content () {
      return $this->html->get_content();

   }


}

?>
