<?php

/*
Class:        Ctz_markdown
creation:     22/01/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_markdown extends Ctz_object
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
      $basedir=ctz_var('BASE_DIR');
      include_once("$basedir/lib/markdown/markdown.php");
   }
}

?>
