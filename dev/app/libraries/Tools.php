<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Description of Tools
 *
 * @author Halim
 * version 1.0
 */
class Tools {

    
    public static function send_email($params) {
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';
        $headers[] = 'From: info@ubaaverages.com';
        return @mail($params['recipients'], $params['subject'], $params['message'], implode("\r\n", $headers));
    }  
    
}
