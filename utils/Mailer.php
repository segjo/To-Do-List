<?php

require_once('../../credentials.php');
require_once('PHPMailer.php');

class Mailer {

    private $mail;
    function __construct() {
        $this->mail = new PHPMailer();
        $this->mail->IsSMTP();                                      // set mailer to use SMTP
        $this->mail->Host = MAIL_HOST;  // specify main and backup server
        $this->mail->SMTPAuth = MAIL_SMTPAUTH;     // turn on SMTP authentication
        $this->mail->Username = MAIL_USERNAME;  // SMTP username
        $this->mail->Password = MAIL_PASSWORD; // SMTP password
        $this->mail->Port = MAIL_PORT;
        $this->mail->From = MAIL_USERNAME;
    }

    public function sendActivationMail($receiver, $userName, $actCode) {
        $this->mail->FromName = "To-Do-List Mailer";
        $this->mail->AddAddress($receiver, $userName);

        $this->mail->WordWrap = 50;                                 // set word wrap to 50 characters
        $this->mail->IsHTML(true);                                  // set email format to HTML

        $this->mail->Subject = "Activate your To-Do-List Account";
        $this->mail->Body = 'Congratulations '.$userName.", you've just had an account created for you on To-Do-List with the email address ". $receiver. ".".
                '<br /> To complete your registration, please visit this URL: '.
                '<br /><a href="https://'.SERVER_URL.'/api/profile/activate/?activateCode='.$actCode .'">https://'.SERVER_URL.'/api/profile/activate/?activateCode='.$actCode .'<a/>';
        
        //$this->mail->AltBody = "This is the body in plain text for non-HTML mail clients";

        if (!$this->mail->Send()) {
            syslog(LOG_ERROR, 'Message could not be sent. Mailer Error: ' . $this->mail->ErrorInfo);
            exit;
        }

        syslog(LOG_NOTICE, 'Message has been sent: ' . $receiver);
    }
    
    public function sendListShare($receiver, $userName, $senderUserName ,$shareCode) {
        $this->mail->FromName = "To-Do-List Mailer";
        $this->mail->AddAddress($receiver, $userName);

        $this->mail->WordWrap = 50;                                 // set word wrap to 50 characters
        $this->mail->IsHTML(true);                                  // set email format to HTML

        $this->mail->Subject = "Invitation to share a To-Do-List";
        $this->mail->Body = 'Hi '.$userName.", you've just get a invitation for a To-Do-List from ".$senderUserName.".".
                '<br /> To accept, please visit this URL: '.
                '<br /><a href="https://'.SERVER_URL.'/api/todolist/activate/?activateCode='.$shareCode .'">https://'.SERVER_URL.'/api/todolist/activate/?activateCode='.$shareCode .'<a/>';
        
        //$this->mail->AltBody = "This is the body in plain text for non-HTML mail clients";

        if (!$this->mail->Send()) {
            syslog(LOG_ERROR, 'Message could not be sent. Mailer Error: ' . $this->mail->ErrorInfo);
            exit;
        }

        syslog(LOG_NOTICE, 'Message has been sent: ' . $receiver);
    }

}

?>