<?php

// TIMER

global $CTZ_TIMER;
$CTZ_TIMER=array(microtime(true));

function ctz_timer ($tag='') {
   global $CTZ_TIMER;

   $delta=microtime(true) - $CTZ_TIMER[0];
   $CTZ_TIMER[]=$delta;

   return $delta;
}

function ctz_mstimer ($tag='') {
   $res=ctz_timer($tag);
   $res=ceil(100*$res);
   return $res;
}

// CLASS

function ctz_createclass ($classname)
{
   $curdir=dirname(__FILE__);

   $classmodel="$curdir/../class/model/index.php";
   if (is_file($classmodel)) {
      $code=file_get_contents($classmodel);
      $update=array(
      	"CTZAUTHOR" => "LH",
      	"CTZDATECREATION" => date("d/m/Y"),
      	"CTZCLASS" => $classname,
      );
      $search=array_keys($update);
      $replace=array_values($update);
      $code=str_replace($search, $replace, $code);
   }

   $classcode="$curdir/../class/".strtolower(basename($classname)).".php";
   if (!is_file($classcode)) {
   	file_put_contents($classcode, $code);
   }
}

function ctz_autoload ($classname)
{
   //Don't interfere with other autoloaders
    if (0 !== strpos($classname, 'Ctz')) {
      return false;
    }

   $curdir=dirname(__FILE__);
   $classcode="$curdir/../class/".strtolower(basename($classname)).".php";
   if (defined('CTZ_DEV')) {
      if (!is_file($classcode)) {
         ctz_createclass($classname);
      }
   }
   if (is_file($classcode)) {
      require_once($classcode);
   }

}
// requires PHP5.x and better 5.3+
spl_autoload_register('ctz_autoload');

global $CTZ_VAR;
$CTZ_VAR=array();

function ctz_var ($var) {
   global $CTZ_VAR;
   $res=$CTZ_VAR[$var];
   return $res;
}

function ctz_set ($var, $val) {
   global $CTZ_VAR;
   $CTZ_VAR[$var]=$val;
}

ctz_set('CHMOD_DIR', 0777);

if (defined('CTZ_CHMOD_DIR')) {
   ctz_set('CHMOD_DIR', CTZ_CHMOD_DIR);
}

if (defined('CTZ_EMAIL')) {
   ctz_set('ctz_email', CTZ_EMAIL);
}



?>
