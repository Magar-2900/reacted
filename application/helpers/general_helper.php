<?php

function SUCCESS($code,$message,$data)
{
    $res['success'] = $code;
    $res['message'] = $message;
    $res['data'] = $data;
    return $res;
}

function ERROR($code,$message)
{        
    $res['success'] = $code;
    $res['message'] = $message;
    $res['data'] = [];   
    return $res;
}

function validateAccessToken($headers)
{
    
    $CI =& get_instance();
    try
    {
        $decodedToken = $CI->authorization_token->validateToken($headers['Authorization']);    
        return $decodedToken;
    }
    catch(Exception $e)
    {
        return $e->getMessage();
    }
}