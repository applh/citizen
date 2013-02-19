<?php

/*
Class:        Ctz_html
creation:     22/01/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_html extends Ctz_object
{
   /* MEMBERS */
   public $doctype;
   public $head;
   public $body;
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
      //$this->doctype='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
      $this->doctype='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

      $this->head=new Ctz_html_head();
      $this->body=new Ctz_html_body();

   }

   public function get_content () {

      $this->html=
         $this->doctype
         ."\n".'<html xmlns="http://www.w3.org/1999/xhtml">'
         .$this->head->get_content()
         .$this->body->get_content()
         ."\n</html>";

      return $this->html;
   }
}

?>
