<?php

function SUCCESS($code,$message,$data,$count,$total_page,$currnt_page)
{
    $res['success'] = $code;
    $res['message'] = $message;
    
    if($count !== '')
    {
        $res['count'] = $count;
    }

    if($total_page !== '')
    {
        $res['total_page'] = $total_page;
    }

    if($currnt_page !== '')
    {
        $res['currnt_page'] = $currnt_page;
    }
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