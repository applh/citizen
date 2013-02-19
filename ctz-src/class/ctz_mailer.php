<?php

/*
Class:        Ctz_mailer
creation:     03/02/2010
modification: CTZDATEMODIFICATION
author:       LH
*/

class Ctz_mailer extends Ctz_object
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
   }

   public function load_lib () {
      $basedir=ctz_var('BASE_DIR');
      include_once("$basedir/lib/swiftmailer/swift_required.php");
   }

   public function send_mail ($obj) {
      $this->load_lib();

      $mail_from=array('contact@'.$_SERVER['SERVER_NAME'] => 'contact');
      $mail_to=array('www.hello@gmail.com' => 'admin');
      $mail_subject='[new message] contact';

      if ($obj) {
         if (is_array($obj)) {
            $tabmsg=$obj;
         }
         else {
            $tabmsg=unserialize($obj->serial);   
         }
         foreach($tabmsg as $var => $val) {
            $var=stripslashes($var);
            $val=stripslashes($val);
            $mail_body.="\n[$var]\n$val\n";
         }
      }
      //Create the message
      $message = Swift_Message::newInstance()
      //Give the message a subject
      ->setSubject($mail_subject)
      //Set the From address with an associative array
      ->setFrom($mail_from)
      //Set the To addresses with an associative array
      ->setTo($mail_to)
      //Give it a body
      ->setBody($mail_body)
      //And optionally an alternative body
      //->addPart('<q>Here is the message itself</q>', 'text/html')
      //Optionally add any attachments
      //->attach(Swift_Attachment::fromPath('my-document.pdf'))
      ;
      //Mail
      $transport = Swift_MailTransport::newInstance();
      $mailer = Swift_Mailer::newInstance($transport);
      //Send the message
      $result = $mailer->send($message);

      
   }

}

?>
