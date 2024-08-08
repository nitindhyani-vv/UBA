<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    function __construct() {
        parent::__construct();
    }

    public function index() {
        echo '<h1>Welcome</h1>';
        $this->send_email();
    }
    
    
    private function send_email() {
        $this->load->library('email');
        
        $from_email = 'info@ubaaverages.com';
        $from_name = 'UBA';
        $to_email = 'halim.lardjane@gmail.com';
        $subject = 'Verify your UBA Account';
        
        $body = '<img src="'.base_url('public/img/logo.png').'" style="widyh:300px;" title="UBA">';
        $body.= '<h2>Hi Halim Lardjane,</h2>';
        $body.= '<p>Please verify your account by clicking on the verification click below,<p>'; 
        $body.= '<p style="margin-top:18px;">Thank You!<p>';
        
        $message = '
        <html>
            <head>
               <title>UBA</title>
               <link rel="icon" type="image/png" href="'.base_url('public/img/favicon.ico').'">   
            </head>
            <body>'.$body.'</body>
        </html>';
        
        
        $this->email->from($from_email, $from_name);
        $this->email->to($to_email);
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->set_mailtype("html");
        $this->email->set_newline("\r\n");
        
        if ($this->email->send()) {
            print "Email sent.";
        } 
        else {
           print "Could not send email, please try again later.";
        }
    }
    
}

