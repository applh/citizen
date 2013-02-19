<?php

class Ctz_object
{
   /* MEMBERS */
   public static $total=0;
   public static $train='';

   public $serial;

   /* METHODS */

   public function __construct () {      
      Ctz_object::$total++;
      Ctz_object::$train.=','.Ctz_object::$total.','.get_class($this);
   }

   public function __destruct () {
      Ctz_object::$total--;
      Ctz_object::$train.='#'.Ctz_object::$total.','.get_class($this);
      //echo get_class($this).'#';
   }

   public function get () {
      return $this->serial;
   }

   public function custom ($action, $serial='') {
      if ($serial) {
         $this->serial=$serial;
      }
      $custom=new Ctz_custom($action, $this);

   }
}

?>
