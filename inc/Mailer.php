<?php
namespace Utils;
use \stdClass;
require "../vendor/autoload.php"; //Composer autoload, TODO: I ONLY NEED TO LOAD S3
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer{
    private $mail = null;
    private  $emailsFolder;
    private $emailsMapping;
    
    function __construct($emailsFolder) {   
        $this->emailsFolder = $emailsFolder;
        $this->mail =  new PHPMailer();

        //$this->mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
        $this->mail->isSMTP();                                            // Send using SMTP
        $this->mail->Host       = EMAIL_SERVER;                    // Set the SMTP server to send through
        $this->mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $this->mail->Username   = EMAIL_USER;                     // SMTP username
        $this->mail->Password   = EMAIL_PASSWORD;                               // SMTP password
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $this->mail->Port       = EMAIL_PORT;    

        $this->mail->setFrom(EMAIL_USER, "(NO-REPLY) Podcaster");
        $this->mail->addReplyTo(EMAIL_USER, '(NO-REPLY) Podcaster');

        $this->emailsMapping = new stdClass();
        $this->emailsMapping->createAccount = "account-create.html";
        $this->emailsMapping->deleteAccount = "account-delete.html";     
    }

    public function addRecepients($email,$name){
        $this->mail->addAddress($email, $name);     // Add a recipient

    }

    public function loadTemplate($templateName){
        $template = $this->emailsMapping->$templateName;
        $this->mail->isHTML(true);                                  // Set email format to HTML
        
        $this->mail->Body    = file_get_contents($this->emailsFolder . $template);
    }

    public function setMessageParams($array){
       $message = $this->mail->Body;
        foreach ($array as $key => $value) {
            $message = str_replace("{{".$key."}}",$value, $message);
        }
        $this->mail->Body = $message;
    }

    public function send($subject){
        $this->mail->Subject = $subject;  
        try {
            $this->mail->send();
            //echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }
}


?>