<?php
defined('BASEPATH') || exit('No direct script access allowed');

use Aws\S3\S3Client;
use Pushok\AuthProvider;
use Pushok\Client;
use Pushok\Notification;
use Pushok\Payload;
use Pushok\Payload\Alert;
use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

/**
 * Description of General Library
 *
 * @category libraries
 * 
 * @package libraries
 *
 * @module General
 * 
 * @class General.php
 * 
 * @path application\libraries\General.php
 * 
 * @version 4.0
 * 
 * @author CIT Dev Team
 * 
 * @since 01.08.2016
 */
Class General
{

    protected $CI;
    protected $_email_subject;
    protected $_email_content;
    protected $_email_params;
    protected $_push_content;
    protected $_notify_error;
    protected $_expression_eval;
    protected $_aws_avial_obj;
    protected $_aws_avail_buckets;
    protected $_hmvc_module_paths;

    public function __construct()
    {
        $this->CI = & get_instance();
    }


    // public function CISendMail($to = '', $subject = '', $body = '', $from_email = '', $from_name = '', $cc = '', $bcc = '', $attach = array(), $params = array(), $reply_to = array())
    // {
    //     $success = FALSE;
    //     try {
    //         if (empty($to)) {
    //             throw new Exception("Receiver email address is missing..!");
    //         }
    //         if (empty($body) || trim($body) == "") {
    //             throw new Exception("Email body content is missing..!");
    //         }
    //         $this->_email_subject = $subject;
    //         $this->_email_content = $body;
    //         $this->_email_params = array(
    //             'from_name' => $from_name,
    //             'from_email' => $from_email,
    //         );


    //         if ($this->CI->config->item('email_sending_library') == 'phpmailer') {

    //             require_once($this->CI->config->item('third_party') . 'phpmailer/vendor/autoload.php');
    //             $mail = new PHPMailer(true);

    //             $mail->SMTPDebug = 0;
    //             $mail->isSMTP();
    //             $mail->isHTML(true);
    //             $mail->SMTPAuth = true;
    //             $mail->Host = $this->CI->config->item('USE_SMTP_SERVERHOST');
    //             $mail->Username = $this->CI->config->item('USE_SMTP_SERVERUSERNAME');
    //             $mail->Password = $this->CI->config->item('USE_SMTP_SERVERPASS');
    //             $mail->SMTPSecure = $this->CI->config->item('USE_SMTP_SERVERCRYPTO');
    //             $mail->Port = $this->CI->config->item('USE_SMTP_SERVERPORT');
    //             $mail->setFrom($from_email, $from_name);
    //             $mail->addAddress($to, $to);
    //             if (isset($reply_to['reply_name']) && isset($reply_to['reply_email'])) {
    //                 $mail->addReplyTo($reply_to['reply_email'], $reply_to['reply_name']);
    //             } else {
    //                 $mail->addReplyTo($from_email, $from_name);
    //             }
    //             if (!empty($cc)) {
    //                 $mail->addCC($cc);
    //                 $this->_email_params['cc'] = $cc;
    //             }
    //             if (!empty($bcc)) {
    //                 $mail->addBCC($bcc);
    //                 $this->_email_params['bcc'] = $bcc;
    //             }
    //             if (is_array($attach) && count($attach) > 0) {
    //                 foreach ($attach as $ak => $av) {
    //                     $mail->addAttachment($av['filename'], $av['newname']);
    //                 }
    //             }
    //             $mail->Subject = $subject;
    //             $mail->Body = $body;
    //             $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    //             $success = $mail->send();
    //         } else {
    //             $this->CI->load->library('email');
                
    //             $this->CI->email->from($from_email, $from_name);
    //             if (isset($reply_to['reply_name']) && isset($reply_to['reply_email'])) {
    //                 $this->CI->email->reply_to($reply_to['reply_email'], $reply_to['reply_name']);
    //             } else {
    //                 $this->CI->email->reply_to($from_email, $from_name);
    //             }
    //             $this->CI->email->to($to);
    //             if (!empty($cc)) {
    //                 $this->CI->email->cc($cc);
    //                 $this->_email_params['cc'] = $cc;
    //             }
    //             if (!empty($bcc)) {
    //                 $this->CI->email->bcc($bcc);
    //                 $this->_email_params['bcc'] = $bcc;
    //             }
    //             $this->CI->email->subject($subject);
    //             $this->CI->email->message($body);
    //             if (is_array($attach) && count($attach) > 0) {
    //                 foreach ($attach as $ak => $av) {
    //                     $this->CI->email->attach($av['filename'], $av['position'], $av['newname']);
    //                 }
    //             }
    //             $success = $this->CI->email->send();
                
    //             if (is_array($attach) && count($attach) > 0) {
    //                 $this->CI->email->clear(TRUE);
    //             }
    //             if (!$success) {
    //                 throw new Exception($this->CI->email->print_debugger(array("subject")));
    //             }

    //         }

    //         $message = "Email send successfully..!";
    //     } catch (Exception $e) {
    //         $message = $e->getMessage();
    //         $this->_notify_error = $message;
    //         print_r($message);die;

    //     }
    //     print_r($success);die;
    //     return $success;
    // }
    
    public function uploadAWSData($temp_file = '', $folder_name = '', $file_name = '')
    {
        $folder_name = rtrim(trim($folder_name), "/");
        $bucket_name = $this->CI->config->item('AWS_BUCKET_NAME');        
        try {
            $response = FALSE;
            if (trim($file_name) == "" || trim($bucket_name) == "" || trim($folder_name) == "") {
                return $response;
            }
            $s3 = $this->getAWSConnectionObject();
            if (version_compare(PHP_VERSION, '5.5', '>=')) {

                
                    $object_config = array(
                        'ACL' => 'public-read',
                        'Bucket' => $bucket_name,
                        'Key' => $folder_name . '/' . $file_name,
                        'SourceFile' => $temp_file,
                        'ContentType' => mime_content_type($temp_file)
                    );

                    $response = $s3->putObject($object_config);                
            } else {
                $object_folder = $bucket_name . "/" . $folder_name;
                $response = $s3->putObjectFile($temp_file, $object_folder, $file_name, S3::ACL_PUBLIC_READ);

            }
        } catch (Exception $e) {
            
        }
        return $response;
    }

    public function get_setting($key)
    {
        $this->CI->db->select('vSettingName,vSettingValue');
        $this->CI->db->from('app_setting');
        $this->CI->db->where('vSettingName',$key);
        $query_obj = $this->CI->db->get();
        $result = is_object($query_obj) ? $query_obj->result_array() : array();
        return $result[0]['vSettingValue'];
    }
    public function getAWSConnectionObject()
    {
        if (is_object($this->_aws_avial_obj)) {
            return $this->_aws_avial_obj;
        }
        
        $AWS_ACCESSKEY  = $this->get_setting('AWS_ACCESSKEY');
        $AWS_SECRECTKEY = $this->get_setting('AWS_SECRECTKEY');
        $AWS_SSL_VERIFY = ($this->get_setting('AWS_SSL_VERIFY')  == "Y") ? TRUE : FALSE;
        $AWS_END_POINT  = $this->get_setting('AWS_END_POINT');

        try {
            if (version_compare(PHP_VERSION, '5.5', '>=')) {
                $AWS_SERVER_REGION = (trim($AWS_END_POINT)) ? trim($AWS_END_POINT) : "us-east-1";
                require_once ($this->CI->config->item('third_party') . "aws_s3/vendor/autoload.php");
                $aws_config = array(
                    'version' => 'latest',
                    'region' => $AWS_SERVER_REGION,
                    'scheme' => ($AWS_SSL_VERIFY) ? 'https' : 'http',
                    'credentials' => array(
                        'key' => $AWS_ACCESSKEY,
                        'secret' => $AWS_SECRECTKEY
                    )
                );
                $this->_aws_avial_obj = new S3Client($aws_config);

            } else {
                $AWS_SERVER_REGION = (trim($AWS_END_POINT)) ? "s3-" . trim($AWS_END_POINT) . ".amazonaws.com" : FALSE;
                require_once ($this->CI->config->item('third_party') . "aws_s3/S3.php");
                if ($AWS_SERVER_REGION) {
                    $this->_aws_avial_obj = new S3($AWS_ACCESSKEY, $AWS_SECRECTKEY, $AWS_SSL_VERIFY, $AWS_SERVER_REGION);
                } else {
                    $this->_aws_avial_obj = new S3($AWS_ACCESSKEY, $AWS_SECRECTKEY, $AWS_SSL_VERIFY);
                }
            }
        } catch (Exception $e) {
            
        }
        return $this->_aws_avial_obj;
    }
}

/* End of file General.php */
/* Location: ./application/libraries/General.php */