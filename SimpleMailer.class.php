<?php

    class SimpleMailer
    {
        private $from, $to;
	private $attachments = array();

        private function validateEmail($email)
        {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        }

        public function from($email, $fullname)
        {
            if ($this->validateEmail($email))
            {
                $this->from["email"] = $email;
                $this->from["fullname"] = $fullname;
                
                return true;
            } else {
                return false;
            }
        }
    
        public function replyto($email, $fullname)
        {
            if ($this->validateEmail($email))
            {
                $this->replyto["email"] = $email;
                $this->replyto["fullname"] = $fullname;
        
                return true;
            } else {
                return false;
            }
        }

        public function to($email, $fullname)
        {
            if ($this->validateEmail($email))
            {
                $this->to[] = array("email" => $email, "fullname" => $fullname);
                return true;
            } else {
                return false;
            }
        }

        public function subject($subject)
        {
            $this->subject = $subject;
        }

        public function __construct()
        {
            if (class_exists("SimpleTpl"))
            {
                $this->tpl = new SimpleTpl();
            }
	    else if (class_exists("\Mikjaer\SimpleTpl\SimpleTpl"))
            {
 		$this->tpl = new \Mikjaer\SimpleTpl\SimpleTpl();
            }
            else if (class_exists("Smarty"))
            {
                $this->tpl = new Smarty();
            } else
            {
                die("No template-engine found. (SimpleTpl or Smarty)");
            }
        }

        public function template_plain($filename)
        {
            if (file_exists($filename))
                $this->template["plain"] = $filename;   
            else
                die("Template `$filename`not found!");
        }

        public function template_html($filename)
        {
            $this->template["html"] = $filename;
        }
 
        public function assign($key, $value)
        {
            $this->tpl->assign($key, $value);
        }

	public function attachfile($uri, $filename, $type)
	{
		return $this->attach(file_get_contents($uri), $filename, $type);
	}

	public function attach($content, $filename, $type)
	{
		$this->attachments[] = array("content"=>$content, "filename"=>$filename, "type"=>$type);
	}

        public function send()
        {
            $message = $headers="";

                # Global Headers

		    if (isset($this->from)) // From-header
                $headers .= 'From: '.$this->from["fullname"].' <'.$this->from["email"].'>' . "\r\n";
            
            if (isset($this->replyto)) // Replyto-header
                $headers .= 'Reply-To: '.$this->replyto["fullname"].' <'.$this->replyto["email"].'>' . "\r\n";

                # Message specific headers

            if ((isset($this->template["html"])) and (isset($this->template["plain"])) or ($this->attachments))    // Multimime
            {
                $boundary=md5(uniqid(rand()));
                
          //      $headers .= 'MIME-Version: 1.0'."\r\n";
                $headers .= "Content-type: multipart/mixed; boundary=\"mix-$boundary\"\n";

         //       $message = "This is multipart message using MIME\n";

		$message .= "--mix-" . $boundary . "\n";
                $message .= 'Content-Type: multipart/alternative; boundary="alt-'.$boundary."\"\n\n";
		if ($this->template["plain"])
		{
			$message .= "--alt-" . $boundary . "\n";
                	$message .= "Content-type: text/plain;charset=iso-8859-1\n";
                	$message .= "Content-Transfer-Encoding: 7bit". "\n\n";
                	$message .= $this->tpl->fetch($this->template["plain"]); 
                }

		if ($this->template["html"])
		{
			$message .= "--alt-" . $boundary . "\n";
                	$message .= "Content-type: text/html;charset=iso-8859-1\n";
                	$message .= "Content-Transfer-Encoding: 7bit". "\n\n";
                	$message .= $this->tpl->fetch($this->template["html"]);
                }

		$message .= "--alt-" . $boundary . "--\n";

		foreach ($this->attachments as $a)
		{
			$message .= "--mix-" . $boundary . "\n";
 			$message .= "Content-Type: ".$a["type"]."; name=\"" . $a["filename"] . "\"" . "\n";
     			$message .= "Content-Transfer-Encoding: base64" . "\n";
         		$message .= "Content-Disposition: attachment" . "\n\n";
			$message .= base64_encode($a["content"]);
			$message .= "\n";
		}
		
		#$message .= "--" . $boundary . "--";
		$message .= "--mix-" . $boundary . "--";
            } else if (isset($this->template["html"]))  // HTML
            {
                $headers .= 'MIME-Version: 1.0' . "\r\n";
		        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
                $message = $this->tpl->fetch($this->template["html"]);
            } else {    // Plain
                $message = $this->tpl->fetch($this->template["plain"]);
            }   

                # Send the messages

            foreach ($this->to as $r)
            {
	        $to = "To: $r[fullname] <$r[email]> \r\n"; 
                mail($r["email"],$this->subject,$message,$to.$headers);
	    }

        }
    }

?>
