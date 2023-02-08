<?php 
if ( ! defined('BASEPATH')) exit ('No direct script access allowed');

if(!function_exists('login_credentials_content'))
{
    function login_credentials_content($username,$password) {
        $mail_body = '<p>Hi User,</p><p>Welcome to ISMP. Please find your login details below.</p><p><b>Username:</b> '.$username.' </p><p><b>Password:</b> '.$password.' </p><p><b>Click here: </b><a href="#">Reacted</a></p><p>Thank You,<br/><a href="#">Reacted</a><br/></p>';
        return $mail_body;
    }
}