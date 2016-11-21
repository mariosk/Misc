<?php

//Author: Marios Karagiannopoulos (mariosk@gmail.com)
//Date  : 25.June.2012

function get_headers_curl($url)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL,            $url);
    curl_setopt($ch, CURLOPT_HEADER,         true);
    curl_setopt($ch, CURLOPT_NOBODY,         true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT,        15);

    $r = curl_exec($ch);
    $r = explode("\n", $r);
    return $r;
} 

error_reporting(E_ALL);
ini_set('display_errors','On');

//ini_set('default_socket_timeout', 150);

//set_time_limit(100);

//phpinfo();
//echo "test";
//exit;

$myFile = "monitorMySites.txt";
$file = fopen($myFile, "r");
$lines = array();
while (!feof($file)) {
    $line_of_text = fgets($file);
    //echo "</br>";
    //echo "'".$line_of_text."'";
    $trimmed = trim($line_of_text);
    //echo "</br>";
    //echo "'".$trimmed."'";
    //echo "</br>";
    array_push($lines, $trimmed);
}
fclose($file);
//echo "</br>";
//print_r($lines);

// Create a stream
$opts = array(
  'http'=>array(
    'method'=>"GET",
    'header'=>"Accept-language: en\r\n" .
              "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n" .
              "Accept-Encoding: gzip, deflate\r\n" .
              "Connection: keep-alive\r\n" .
	      "Cache-Control: no-cache\r\n" .
              "User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:13.0) Gecko/20100101 Firefox/13.0.1\r\n"
  )
);
$context = stream_context_create($opts);

$error_message = "";
$ch = curl_init();

foreach ($lines as &$value) {
    //echo $value;
    //echo "</br>";
    $pieces = explode(" *** ", $value);
    //echo $pieces[0]; // piece1
    //echo "</br>";
    //echo $pieces[1]; // piece2    
           
    $site_url = $pieces[0];     
    $expected_size = $pieces[1];    
    
    $range_of_bytes_to_ignore = 0;
    $bytes = explode(" ", $expected_size);
    if (is_array($bytes)) {	
        $expected_size = $bytes[0];
	if (count($bytes) >= 2) {
	  $range_of_bytes_to_ignore = $bytes[1];
	}
    }    
    /*
    echo "site = ".$pieces[0]; 
    echo "</br>";
    echo "expected_size=".$expected_size;
    echo "</br>";
    echo "range_of_bytes_to_ignore=".$range_of_bytes_to_ignore;
    echo "</br>";
    */
    $aHeaders = get_headers_curl($site_url);
    if (count($aHeaders) == 1) {
        $error_message = $error_message."Problem with site: ".$site_url." : "."There are no response headers, timeout?"."</br>\r\n";
    }
    else {
        //var_dump($aHeaders);
        //echo $aHeaders[0];
        if ( (strpos($aHeaders[0], 'HTTP/1.1 200 OK') === FALSE) And (strpos($aHeaders[0], 'HTTP/1.1 501 Not Implemented') === FALSE)) {
            $error_message = $error_message."Problem with site: ".$site_url." : ".$aHeaders[0]."</br>\r\n";
        }
        //else {
            //$error_message = $error_message."Site: ".$site_url." seems to be reached properly (OK)!</br>\r\n";
        //}

        curl_setopt($ch, CURLOPT_URL, $site_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $pageSource = curl_exec($ch);
        $real_size = strlen($pageSource);

        //$real_size = strlen(file_get_contents($site_url));
        //print "site_url = ".$site_url;
        //echo "</br>";
        //print "real_size = ".$real_size;
        //echo "</br>";
        $both = false;
        
        if ($range_of_bytes_to_ignore != 0) {
            $min = $expected_size - $range_of_bytes_to_ignore;
            //print "min = $min";
            //echo "</br>";
            $max = $expected_size + $range_of_bytes_to_ignore;
            //print "max = $max";        
            //echo "</br>";
            $range = range($min, $max);
            
            $both = in_array($real_size, $range);
            //print "BOTH CONDITIONS= ".$both;
            //print "<p>";
        }
                
        // both conditions should be TRUE
        if ($real_size != $expected_size && !$both) {
            $error_message = $error_message."Problem with site: ".$site_url." : Seems that site is hijacked!! Expected size is ".$expected_size." (+ or -".$range_of_bytes_to_ignore."). Content-Length though is: ".$real_size."</br>\r\n";
            //var_dump($aHeaders);
        }        
        //else {
            //$error_message = $error_message."Site: ".$site_url." seems to be reached properly (OK)!</br>\r\n";
        //}
        // sleep for 5 seconds
        sleep(5);
        //break;
    }
        
    //echo "</br>";
    //echo "</br>";
    //print_r($http_response_header);    
}

curl_close($ch);

if ($error_message != "") {
    //echo $error_message;
    //define the receiver of the email
    //$to = 'mariosk@gmail.com';
    $to = 'mariosk@gmail.com';
    //define the subject of the email
    $subject = 'WebSite Ping Robot';
    //create a boundary string. It must be unique
    //so we use the MD5 algorithm to generate a random hash
    $random_hash = md5(date('r', time()));
    //define the headers we want passed. Note that they are separated with \r\n
    $headers = "From: robot@mysites.com\r\nReply-To: mariosk@gmail.com";
    //add boundary string and mime type specification
    $headers .= "\r\nContent-Type: multipart/mixed; boundary=\"DOT4U-bdry-".$random_hash."\"";
    //define the body of the message.
    ob_start(); //Turn on output buffering
    ?>
DOT4U-bdry-<?php echo $random_hash; ?> 
Content-Type: text/plain; charset="iso-8859-1"
Content-Transfer-Encoding: 7bit

<?php echo $error_message ?>

DOT4U-bdry-<?php echo $random_hash; ?>--    
<?
    //copy current buffer contents into $message variable and delete current output buffer
    $message = ob_get_clean();
    //send the email
    $mail_sent = @mail( $to, $subject, $message, $headers );
    //if the message is sent successfully print "Mail sent". Otherwise print "Mail failed" 
    echo $mail_sent ? "Mail sent" : "Mail failed";
}
?>