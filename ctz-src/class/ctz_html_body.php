<?php

/*
Class:        Ctz_html_body
creation:     22/01/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_html_body extends Ctz_object
{
   /* MEMBERS */
   public $html;
   public $pagetitle;

   /* METHODS */

   public function __construct () {
      parent::__construct();
      $this->init();
   }

   public function __destruct () {
      parent::__destruct();
   }

   public function init () {
      $this->pagetitle=$_SERVER["SERVER_NAME"];
      
      $this->html.="\n".'<body class="CTZ-BODY-CLASS-WEB">';

      $this->html.="\n".'<div class="pagebox">';
      $this->html.="\n".'<div class="container_width">';
      $this->html.="\n".'<div id="page" class="page grid_width alpha omega">';
      
      $this->html.="\n".'<div class="page-content grid_width alpha omega"><!--CTZ-PAGE-CONTENT--></div>';
      
      $this->html.="\n".'<div class="footer page-footer grid_width alpha omega"><!--CTZ-PAGE-FOOTER--></div>';

      $this->html.="\n</div>"; // end page
      $this->html.="\n</div>"; // end container
      $this->html.="\n</div>"; // end pagebox

      $this->html.="\n".'<div class="footer body-footer"><!--CTZ-BODY-FOOTER--></div>';
      $this->html.="\n".'<!--CTZ-JS-FOOTER-->';

      $this->html.="\n</body>";

   }

   public function get_content () {
      return $this->html;
   }

}

?>
