<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Libraries\Pdf;
use CodeIgniter\Session\Session;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
class BaseController extends Controller
{
    public $customer_id = 1;
    public $user_id = 2;
    public $pdf = null;
    public $api_emailId = 'fmi_audits_ho@fmlogistic.com'; // Add your email configuration
    public $api_emailPassword = 'Welcome@2026'; // Add your email password
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = [];

    protected Session $session;
    /**
     * Constructor.
     */

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        $this->session = session();
        date_default_timezone_set('Asia/Kolkata');
        // Skip ACL check for public methods
        $router = service('router');
        $controller = $router->controllerName();
        $method = $router->methodName();

        // Get an instance of the controller
        $controllerInstance = new $controller();

        if (property_exists($controllerInstance, 'publicMethods') && in_array($method, $controllerInstance->publicMethods)) {
            return;
        }

        if (isset($_SESSION['user_id']) && isset($_SESSION['customer_id'])) {
            $this->customer_id = $_SESSION['customer_id'];
            $this->user_id = $_SESSION['user_id'];
        }

        // Auto-run ACL Schema Migration if needed
        $db = \Config\Database::connect();
        $needsMigration = false;
        
        if (!$db->tableExists('alert_user_client_mapping')) {
            $needsMigration = true;
        } else {
            $mappingCols = $db->getFieldNames('alert_user_client_mapping');
            if (!in_array('status', $mappingCols)) {
                $needsMigration = true;
            }
        }
        
        if (!$needsMigration && $db->tableExists('alert_final_structured_audit')) {
            $auditCols = $db->getFieldNames('alert_final_structured_audit');
            if (!in_array('snapshot_site_id', $auditCols)) {
                $needsMigration = true;
            }
        }

        if ($needsMigration) {
            $mig = new \App\Controllers\Admin\DataMigration();
            $mig->runMultiSiteAclMigration($db);
        }
    }
    /**
     * Validate file type for uploads
     * Only allows images (jpg, jpeg, png, gif), PDFs, and Excel files (xls, xlsx)
     * 
     * @param string $file_name The name of the file input field
     * @return array Returns ['valid' => bool, 'message' => string, 'extension' => string]
     */
    function validateFileType($file_name = 'attach')
    {
        // changes on 1/10/25 by darsh: allow CSV uploads used by audit templates
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'xls', 'xlsx', 'csv'];
        $allowedMimeTypes = [
            // Images
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            // PDFs
            'application/pdf',
            // Excel files
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            // changes on 1/10/25 by darsh: CSV MIME fallbacks
            'text/csv',
            'application/csv',
            'text/plain'
        ];

        if (!isset($_FILES[$file_name]['name']) || empty($_FILES[$file_name]['name'])) {
            return ['valid' => false, 'message' => 'No file selected', 'extension' => ''];
        }

        $extension = strtolower(pathinfo($_FILES[$file_name]['name'], PATHINFO_EXTENSION));
        $mimeType = $_FILES[$file_name]['type'];

        // Check file extension
        if (!in_array($extension, $allowedExtensions)) {
            return [
                'valid' => false,
                'message' => 'Invalid file type. Only images (JPG, PNG, GIF), PDFs, and Excel files (XLS, XLSX) are allowed.',
                'extension' => $extension
            ];
        }

        // Check MIME type for additional security
        if (!in_array($mimeType, $allowedMimeTypes)) {
            return [
                'valid' => false,
                'message' => 'Invalid file format. Please upload a valid image, PDF, or Excel file.',
                'extension' => $extension
            ];
        }

        // Check file size (max 10MB)
        $maxSize = 10 * 1024 * 1024; // 10MB in bytes
        if ($_FILES[$file_name]['size'] > $maxSize) {
            return [
                'valid' => false,
                'message' => 'File size too large. Maximum allowed size is 10MB.',
                'extension' => $extension
            ];
        }

        return ['valid' => true, 'message' => 'File is valid', 'extension' => $extension];
    }

    /**
     * Enhanced uploadImage method with file type validation
     * Only allows images (jpg, jpeg, png, gif), PDFs, and Excel files (xls, xlsx)
     * 
     * @param string $path Upload directory path
     * @param string $file_name File input field name
     * @param string $unlink_url URL of file to delete (optional)
     * @param int $quality Image quality for compression (optional)
     * @return string|false Returns file URL on success, false on failure
     */
    function uploadImage($path, $file_name = 'attach', $unlink_url = null, $quality = 50)
    {
        // Validate file type first
        $validation = $this->validateFileType($file_name);
        if (!$validation['valid']) {
            // Log the validation error
            log_message('error', 'File upload validation failed: ' . $validation['message']);
            return false;
        }

        $attach_url = "";
        if (isset($_FILES[$file_name]['name'])) {
            $exte = $validation['extension'];
            $temp = $path;
            $image_folder = APPPATH . "../" . $temp;

            if (!file_exists($image_folder)) {
                mkdir($image_folder, 0777, true);
            }

            $image_name = "" . rand() . "-" . time() . "-dW5pdGdsbw." . $exte;
            $image_folder = $image_folder . "/" . $image_name;

            // Move uploaded file
            if (move_uploaded_file($_FILES[$file_name]['tmp_name'], $image_folder)) {
                $attach_url = $temp . "/" . $image_name;

                // Delete old file if specified
                if (isset($unlink_url) && $unlink_url != "") {
                    if (file_exists(APPPATH . "../" . $unlink_url)) {
                        unlink(APPPATH . "../" . $unlink_url);
                    }
                }
            } else {
                log_message('error', 'Failed to move uploaded file: ' . $_FILES[$file_name]['name']);
                return false;
            }
        }
        return $attach_url;
    }
    public function numbertowords($number)
    {
        $no = floor($number);
        $point = round($number - $no, 2) * 100;
        $hundred = null;
        $digits_1 = strlen($no);
        $i = 0;
        $str = array();
        $words = array(
            '0' => '',
            '1' => 'one',
            '2' => 'two',
            '3' => 'three',
            '4' => 'four',
            '5' => 'five',
            '6' => 'six',
            '7' => 'seven',
            '8' => 'eight',
            '9' => 'nine',
            '10' => 'ten',
            '11' => 'eleven',
            '12' => 'twelve',
            '13' => 'thirteen',
            '14' => 'fourteen',
            '15' => 'fifteen',
            '16' => 'sixteen',
            '17' => 'seventeen',
            '18' => 'eighteen',
            '19' => 'nineteen',
            '20' => 'twenty',
            '30' => 'thirty',
            '40' => 'forty',
            '50' => 'fifty',
            '60' => 'sixty',
            '70' => 'seventy',
            '80' => 'eighty',
            '90' => 'ninety'
        );
        $digits = array('', 'hundred', 'thousand', 'lakh', 'crore');
        while ($i < $digits_1) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str[] = ($number < 21) ? $words[$number] .
                    " " . $digits[$counter] . $plural . " " . $hundred
                    :
                    $words[floor($number / 10) * 10]
                    . " " . $words[$number % 10] . " "
                    . $digits[$counter] . $plural . " " . $hundred;
            } else
                $str[] = null;
        }
        $str = array_reverse($str);
        $result = implode('', $str);
        $points = ($point) ?
            "." . $words[$point / 10] . " " .
            $words[$point = $point % 10] : '';
        return $result . $points . " AED";
    }
    function compress_image($source_url, $destination_url, $quality)
    {

        $info = getimagesize($source_url);

        if ($info['mime'] == 'image/jpeg')
            $image = imagecreatefromjpeg($source_url);
        elseif ($info['mime'] == 'image/gif')
            $image = imagecreatefromgif($source_url);
        elseif ($info['mime'] == 'image/png')
            $image = imagecreatefrompng($source_url);

        imagejpeg($image, $destination_url, $quality);
        return $destination_url;
    }
    public function update_details($user_id)
    {

        // ads_customer_device
        // user_id
        //ads_device_master

        $db = db_connect();
        // $ads_customer_device = $db->table('ads_device_master');
        // $ads_customer_device->where("user_id",$user_id);
        // $data = $ads_customer_device->select("serial_no")->get()->getResultArray();
        $sql = "select serial_no from ads_device_master where device_id in (select device_id from ads_customer_device where user_id = ?)";
        $query = $db->query($sql, [$user_id]);
        $resultdata = $query->getResultArray();
        $data['device_list'] = [];
        foreach ($resultdata as $row) {
            array_push($data['device_list'], $row['serial_no']);
        }
        // return $data['device_list'];
        return view('fcm', $data);
    }
    function get_time_ago($time)
    {
        $time_difference = time() - $time;

        if ($time_difference < 1) {
            return 'less than 1 second ago';
        }
        $condition = array(
            12 * 30 * 24 * 60 * 60 => 'year',
            30 * 24 * 60 * 60 => 'month',
            24 * 60 * 60 => 'day',
            60 * 60 => 'hour',
            60 => 'minute',
            1 => 'second'
        );

        foreach ($condition as $secs => $str) {
            $d = $time_difference / $secs;

            if ($d >= 1) {
                $t = round($d);
                return 'about ' . $t . ' ' . $str . ($t > 1 ? 's' : '') . ' ago';
            }
        }
    }

    function convert_number($no)
    {
        $t = $no;
        if ($no > 1000 && $no < 1000000) {
            $t = round($no / 1000, 1) . "K";
        } else if ($no > 1000000 && $no < 1000000000) {
            $t = round($no / 1000000, 1) . "M";
        } else if ($no > 1000000000) {
            $t = round($no / 1000000000, 1) . "B";
        }
        return $t;
    }
    function isUrlExists($url)
    {
        $headers = get_headers($url);
        return strpos($headers[0], '200') !== false;
    }

    function send_email($Id = null, $cc = array())
    {
        ini_set('memory_limit', '-1');

        $db = db_connect();
        // $this->load->library('pdf');
        $this->pdf = new Pdf();

        $body = '';
        $from_mail = '';
        if (isset($Id) && $Id != '') {
            $where['mail_id'] = $Id;
            $data = $db->table("alert_mail")->where($where)->get()->getResultArray();//dt
            //group by mail id
            $data = $data[0];
            $up['resend_counter'] = $data['resend_counter'] + 1;
            $up['update_date'] = date('Y-m-d H:i:s');
            $db->table("alert_mail")->update($where, $up);
            // 			$this->Base_Models->UpadateValue('alert_mail', $up, $where);
            $cc = explode(",", $data['cc_emails']);
            $from_mail = $data['from_email'];
        } else {
            // update with server 
            $request = service('request');
            $data = $request->getVar();
            //write newdata into ->AddValues  db
            $para['update_date'] = date('Y-m-d H:i:s');
            $para['receiver_email'] = $data['receiver_email'];
            $para['subject'] = $data['subject'];
            $para['body'] = $data['body'];
            $para['doc_id'] = $data['doc_id'];
            $para['doc_category'] = $data['doc_category'];
            if (!empty($cc))
                $para['cc_emails'] = implode(",", $cc);
            $from_mail = $para['from_email'] = $_POST['from_email'];
            $para['user_id'] = isset($_POST['user_id']) ? $_POST['user_id'] : (isset($_SESSION['login_id']) ? $_SESSION['login_id'] : '');

            if (isset($_POST['email_reminder']) && $_POST['email_reminder'] == 1) {
                $para['email_reminder'] = $data['email_reminder'];

            }

            $mail_id = $db->table("alert_mail")->insert($para);
            if (isset($_POST['allow_feedback']) && $_POST['allow_feedback'] == 1) {
                $code = $this->gererate_code();
                $url = base_url(index_page() . '/Customer_feedback/get_feedback/' . rand(1000, 8457));
                $body .= 'Feed Back link: <a href=\"' . $url . '\">Click Here To Visit</a><br>LINK:' . $url . ' <br><p>and your access code is: ' . $code . '</p>';

                $para2['mail_id'] = $mail_id;
                $para2['link_address'] = $url;
                $para2['pass_code'] = $code;
                $para2['doc_category'] = $data['doc_category'];
                $para2['doc_id'] = $data['doc_id'];
                // 			$this->Base_Models->AddValues(abc,val)
                //  $db->table(abc)->insert(val)
                $Id = $db->table("alert_customer_feed_back_link")->insert($para2);
                //$data['doc_id'];

            }
            // NEW CHANGE: Removed debug code that was preventing execution
        }

        if ($data['doc_category'] == 0) {

            $where['lmra_details_id'] = $data['doc_id'];
            $data['details'] = $db->query(" select alert_lmra_details.*,alert_users.* from alert_lmra_details inner join alert_users on alert_users.user_id=alert_lmra_details.user_id and lmra_details_id=" . $data['doc_id'])->getResultArray();

            // NEW CHANGE: Removed debug code that was preventing execution





            $data['lmra_comments'] = $db->query(" select alert_users.* ,alert_lmra_comments .* 
		                        from alert_lmra_comments 
		                            inner join alert_users on alert_users.user_id=alert_lmra_comments.user_id 
		                                and lmra_id=" . $data['doc_id'])->getResultArray();

            $html = view("Reports/lmra_view_details_pdf", $data);
            // NEW CHANGE: Removed debug code that was preventing execution 
            // $html=$this->load->view("Reports/lmra_view_details_pdf",$data,true);
            //$data['details']=$this->Base_Models->GetAllValues('alert_near_miss',$where);

            // 		$html = $this->output->get_output();
            $this->pdf->loadhtml($html);

            try {
                $this->pdf->render();
                $output = $this->pdf->output();
                $id = "lmradetails.pdf";
            } catch (exception $e) {

            }
        } else {
            // 			echo"here";
            $where['near_miss_id'] = $data['doc_id'];

            $data['details'] = $db->query(" select alert_near_miss.*,alert_users.* from alert_near_miss inner join alert_users on alert_users.user_id=alert_near_miss.user_id and near_miss_id=" . $data['doc_id'])->getResultArray();

            $data['near_miss_chat'] = $db->query(" select alert_near_miss_chat.*,alert_users.* 
	                                            from alert_near_miss_chat 
	                                                inner join alert_users on alert_users.user_id=alert_near_miss_chat.user_id 
	                                                    and near_miss_id=" . $data['doc_id'])->getResultArray();

            $html = view("Reports/nearmiss_brif_details_pdf", $data);
            // $html=$this->load->view("Reports/nearmiss_brif_details_pdf",$data, true);
            //$data['details']=$this->Base_Models->GetAllValues('alert_near_miss',$where);

            //$html = $this->output->get_output();
            $this->pdf->loadhtml($html);
            try {
                $this->pdf->render();
                $id = "NearMissDetails.pdf";
                $output = $this->pdf->output();

            } catch (exception $e) {
                print_r($e);
                // exit();
                // $id="NearMissDetails.pdf";
                // $output = $this->pdf->output();;
            }



        }

        $email = isset($data['receiver_email']) ? $data['receiver_email'] : " ";
        $subject = isset($data['subject']) ? $data['subject'] : "no subject";
        $name = "";
        $body .= isset($data['body']) ? $data['body'] : " ";
        $formname = "AlfaLaval";
        if (isset($id) && $id != "")
            file_put_contents(APPPATH . "core/" . $id, $output);
        $attachment = true;

        // if($li !=null){
        //     $li->send_mail();
        // }

        //     print_r($from_mail);
        //   print_r($email);

        // NEW CHANGE: Uncommented and fixed email sending
        $response_mail = $this->send($email, $name, $from_mail, $subject, $body, $formname, $attachment, $id, $cc);


    }
    public function send($email = "unitglo.pvt.ltd@gmail.com", $name = "Unitglo", $from_email = null, $subject = "Email Send Function", $html = "", $fromname = "AlfaLaval", $attachment = false, $id = null, $cc = null, $bcc = null)
    {
        $email = urldecode($email);
        $subject = urldecode($subject);
        $html = urldecode($html);
        $from = '';

        /*		$html .= '<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; width: 820px;">
            <table align="center" border="0" cellpadding="0" cellspacing="0" >
                <tr colspan="4">
                    <td colspan="4" style=" border:none;">
                        <a href="#"><img style=" height:100%; width:100%;" src="http://subodhancapacitor.com/images/header.jpg"></a>
                    </td>
                </tr>
                <tr colspan="2">
                    <td colspan="4"  style="padding: 30px 30px; border:none; background-color:rgba(255, 224, 1, 0.7);">';
                if ($format != 1) {
                    $html .= $format;
                }
                $html .= '</td>

                </tr>


            </table>
        </body>		';
        */




        require_once APPPATH . 'core/class.phpmailer.php';
        require_once APPPATH . 'core/class.smtp.php';
        $mail = new PHPMailer();
        $mail->IsSMTP(); // set mailer to use SMTP
        $mail->Host = "smtpout.secureserver.net"; // specify main and backup server
        $mail->Port = 80; // set the port to use
        $mail->SMTPAuth = true; // turn on SMTP authentication
        $mail->Username = $this->api_emailId; // your SMTP username or your gmail username
        $mail->Password = $this->api_emailPassword; // your SMTP password or your gmail password
        if (isset($from_email)) {
            $from = $from_email; // Reply to this email
        } else {
            $from = "no-replay@alert.com"; // Reply to this email
        }
        // $from = "no-replay@subodhancapacitor.com"; // Reply to this email
        //	$to = "" . $email; // Recipients email ID
        $name = "" . $name; // Recipient's name
        $mail->From = $from;
        $mail->FromName = $fromname; // Name to indicate where the email came from when the recepient received
        $email_arr = explode(",", $email);
        $name_arr = explode(",", $name);
        for ($i = 0; $i < count($email_arr); $i++) {
            $mail->AddAddress($email_arr[$i], isset($name_arr[$i]) ? $name_arr[$i] : '');
        }
        if ($cc != null) {
            foreach ($cc as $key => $val) {
                if (strpos($val, "<,>") === false) {
                    $mail->AddCC($val);
                } else {
                    list($a, $b) = explode("<,>", $val);
                    $mail->AddCC($b, $a);
                }
            }
        }
        if ($bcc != null) {
            foreach ($bcc as $key => $val) {
                if (strpos($val, "<,>") === false) {
                    $mail->AddBCC($val);
                } else {
                    list($a, $b) = explode("<,>", $val);
                    $mail->AddBCC($b, $a);
                }
            }
        }

        $mail->AddReplyTo($from, $fromname);
        if ($attachment == true)
            $mail->AddAttachment(__DIR__ . "/" . $id);

        // $mail->WordWrap = 50; // set word wrap
        $mail->IsHTML(true); // send as HTML
        $mail->Subject = "" . $subject;
        $mail->Body = " " . $html; // HTML Body
        //$mail->AltBody = strip_tags ( $html ); // Text Body
        $mail->Sender = 'no-replay@alert.com';
        // $mail->AddAttachment($amol);
        if (!$mail->Send()) {
            return "Mailer Error: " . $mail->ErrorInfo;
            print_r($mail->ErrorInfo);
        } else {
            // if ($attachment == true){
            // 	unlink( __DIR__ ."/". $id );
            // 	header('Location: '.$_SERVER['HTTP_REFERER']);

            // }
            return 1;
            //$this->base_view ( "login/load_popup" );

        }

        //header('Location: '.$_SERVER['HTTP_REFERER']);
    }

    // NEW CHANGE: Added missing gererate_code method
    public function gererate_code()
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        $length = 6; // 6 character code

        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $code;
    }

    function getMimeTypeSafe($filePath)
    {
        if (!file_exists($filePath)) {
            return 'application/octet-stream';
        }

        // 1) Best method: Fileinfo
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = finfo_file($finfo, $filePath);
                finfo_close($finfo);

                if ($mime !== false && !empty($mime)) {
                    return $mime;
                }
            }
        }

        // 2) Final fallback by extension (removed deprecated mime_content_type)
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'mp4' => 'video/mp4',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv' => 'text/csv',
            'txt' => 'text/plain',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        return $mimeTypes[$ext] ?? 'application/octet-stream';
    }

    public function set_active_client()
    {
        $request = \Config\Services::request();
        $client = $request->getPost('client');
        
        if ($client) {
            $this->session->set('selected_active_client', $client);
            return $this->response->setJSON(['status' => 1, 'message' => 'Active client updated']);
        }
        return $this->response->setJSON(['status' => 0, 'message' => 'Invalid client']);
    }
}
