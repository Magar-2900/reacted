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


    public function CISendMail($to, $from_name, $subject, $body, $is_gmail = true) 
    {
        require_once APPPATH.'third_party/phpmailer/Exception.php';
        require_once APPPATH.'third_party/phpmailer/PHPMailer.php';
        require_once APPPATH.'third_party/phpmailer/SMTP.php';

        $this->phpmailer = new PHPMailer();
        $this->phpmailer->IsSMTP();
        $this->phpmailer->SMTPAuth = true; 
        if($is_gmail) 
        {            
            $this->phpmailer->SMTPSecure = 'ssl';
            $this->phpmailer->Host = 'smtp.hostinger.in';
            $this->phpmailer->Port = '465';
            $this->phpmailer->Username = 'noreply@ovaatech.com';
            $this->phpmailer->Password = 'Ovaa@2022';         
        } 
        else 
        {
            $this->phpmailer->Host = '465';
            $this->phpmailer->Username = 'noreply@ovaatech.com';
            $this->phpmailer->Password = 'Ovaa@2022';
        }
        $this->phpmailer->IsHTML(true);
        $this->phpmailer->From = 'noreply@ovaatech.com';
        $this->phpmailer->FromName = $from_name;
        $this->phpmailer->Sender = 'noreply@ovaatech.com';
        $this->phpmailer->AddReplyTo('noreply@ovaatech.com', $from_name);
        $this->phpmailer->Subject = $subject;
        $this->phpmailer->Body = $body;
        $this->phpmailer->AddAddress($to);      
        if(!$this->phpmailer->Send()) 
        {
           echo  $error = 'Mail error: '.$this->phpmailer->ErrorInfo;
           return false;
        } 
        else
        {
           echo $error = 'success';
           return true;
        }
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
    
    public function uploadAWSData($temp_file = '', $folder_name = '', $file_name = '')
    {
        $folder_name = rtrim(trim($folder_name), "/");
        $bucket_name = $this->get_setting('AWS_BUCKET_NAME');
        $file_name.'/'.$folder_name.'/'.$temp_file;

        try {
            $response = FALSE;
            if (trim($file_name) == "" || trim($bucket_name) == "" || trim($folder_name) == "") {
                //echo "hiii";
                return $response;
            }
            $s3 = $this->getAWSConnectionObject();
            if (version_compare(PHP_VERSION, '5.5', '>=')) {

                
                    $object_config = array(
                        'ACL' => 'private',
                        'Bucket' => $bucket_name,
                        'Key' => $folder_name . '/' . $file_name,
                        'SourceFile' => $temp_file,
                        'ContentType' => mime_content_type($temp_file)
                    );

                    $response = $s3->putObject($object_config);                
            } else {
                $object_folder = $bucket_name . "/" . $folder_name;
                $response = $s3->putObjectFile($temp_file, $object_folder, $file_name, S3::ACL_PUBLIC_READ);
                //echo $response;
            }
        } catch (Exception $e) {
            print_r($e->getMessage());
        }
        return $response;
    }

    public function getImageUrl($folder_name = '', $file_name = ''){
        $folder_name = rtrim(trim($folder_name), "/");
        $bucket_name = $this->get_setting('AWS_BUCKET_NAME');

        try {
            $response = FALSE;
            if (trim($file_name) == "" || trim($bucket_name) == "" || trim($folder_name) == "") {
                //echo "hiii";
                return $response;
            }
            $s3 = $this->getAWSConnectionObject();                
            /*$object_config = array(
                'Bucket' => $bucket_name,
                'Key' => $folder_name . '/' . $file_name
            );*/

            $cmd = $s3->getCommand('GetObject', [
                'Bucket' => $bucket_name,
                'Key' => $folder_name . '/' . $file_name
            ]);

            $request = $s3->createPresignedRequest($cmd, '+5 minutes');
            $presignedUrl = (string)$request->getUri();
            $response = $presignedUrl;              
        } catch (Exception $e) {
            print_r($e->getMessage());
        }
        return $response;
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