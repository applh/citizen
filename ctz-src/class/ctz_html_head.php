<?php

/*
Class:        Ctz_html_head
creation:     22/01/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_html_head extends Ctz_object
{
   /* MEMBERS */
   public $title;
   public $meta;
   public $css_inline;
   public $js_inline;

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

      $system=ctz_var('SYSTEM');
      $N="\n";
      $this->css_inline.=$N.$system->get_media_content('/reset.css');
      $this->css_inline.=$N.$system->get_media_content('/text.css');
      $this->css_inline.=$N.$system->get_media_content('/960.css');

      $this->title=$_SERVER["SERVER_NAME"];

      $this->html.="\n".'<head profile="http://gmpg.org/xfn/11">';
      $this->html.="\n".'<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
      
      $this->html.="\n<!--CTZ-PAGE-TITLE-->";
      $this->html.="\n<!--CTZ-PAGE-DESCRIPTION-->";
      $this->html.="\n<!--CTZ-PAGE-KEYWORDS-->";

      $this->html.="\n<!--CTZ-LINK-RSS-->";
      $this->html.="\n".'<link rel="shortcut icon" href="/favicon.ico" />';
      
      //$this->html.="\n".'<link rel="stylesheet" href="http://monsite.fr-new.net/wp-content/themes/citizen/css/reset.css" />';
      //$this->html.="\n".'<link rel="stylesheet" href="http://monsite.fr-new.net/wp-content/themes/citizen/css/text.css" />';
      //$this->html.="\n".'<link rel="stylesheet" href="http://monsite.fr-new.net/wp-content/themes/citizen/css/960.css" />';
      $this->html.="\n".'<style type="text/css" media="screen">'."\n"
         .$this->css_inline
         ."\n</style>";
      $this->html.="\n<!--CTZ-INCLUDE-CSS-->";

      $this->html.="\n".'<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>';
      $this->html.="\n<!--CTZ-INCLUDE-JAVASCRIPT-->";
      $this->html.="\n".'<script type="text/javascript">'."\n"
         .$this->js_inline
         ."\n</script>";
      
      $this->html.="\n</head>";

   }

   public function get_content () {
      return $this->html;
   }
}

?>
