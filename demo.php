<?php
    include("SimpleMailer.class.php");
    include("../SimpleTpl/SimpleTpl.class.php");
    #include("Smarty.class.php");

    $mail = new Mailer();

    if (!$mail->from("noreply@leandns.com","LeanDNS Security Log"))
        die("Invalid E-Mail");

    $mail->replyto("noreply@example.com","Support Department");
    $mail->to("recepient1@example.com","Mikkel Mikjaer Christensen");
    $mail->to("recepient2@example.com","Mikkel Mikjaer Christensen");
   
    $mail->subject("Test email from SimpleMailer");

    $mail->template_plain("demo_plain.tpl");
    $mail->template_html("demo_html.tpl");

    $mail->assign("value","This is my testvalue");

    $mail->send();

?>
