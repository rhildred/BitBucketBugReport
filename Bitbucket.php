<?php
/* ******************************************************************************
 * Bitbucket External Issue Submission Library
 * Author - Sherri Wheeler - Avinus Web Services - http://SyntaxSeed.com/
 * License - GPLv3 (http://www.gnu.org/licenses/quick-guide-gplv3.html)
 * Version - 1.0
 * ****************************************************************************** */

class Bitbucket{
    /* Function submitBug - sends the bug to the bitbucket API. Must contain title and content. User name/email is optional.*/
    static function submitBug($title, $content, $user='Anonymous', $component= "", $image = "", $bbAccount='rhildred', $status='new', $priority='major', $kind='bug'){
        $oCreds = json_decode(file_get_contents(__DIR__ . '/../../../creds/bitbucket.json'));
        $basicAuth= $oCreds->basicAuth;
        $bbRepo = $oCreds->repo;
        $url = "https://bitbucket.org/api/1.0/repositories/". $bbRepo . "/issues/";
;
        $ch = curl_init($url);

        if (get_magic_quotes_gpc()) {
            $title = stripslashes($title);
            $content = stripslashes($content);
            $user = stripslashes($user);
            $component = stripslashes($component);
            $bbAccount = stripslashes($bbAccount);
            $bbRepo = stripslashes($bbRepo);
        }

        $fields = array(
                                'title' => urlencode($title),
                                'content' => urlencode($content."\n\nSubmitted By Userid: ".$user. "\n\nUrl: " . $component),
                                'status' => urlencode($status),
                                'priority' => urlencode($priority),
                                'kind' => urlencode($kind)
                        );
        // Build POST url:
        $fieldsStr = '';
        foreach($fields as $key=>$value) {
            $fieldsStr .= $key.'='.$value.'&';
        }
        $fieldsStr = rtrim($fieldsStr, '&');


        //$curl_log = fopen(dirname(__FILE__) . "/curl.txt", 'w+'); // open file for READ and write
        //curl_setopt($ch, CURLOPT_STDERR, $curl_log);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic '.$basicAuth)); //YXZpbnVzOmJpdHBhc3MxOQ==
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

         $response = curl_exec($ch);
        curl_close($ch);

//        print_r($response);  // Debugging

        if( $response !== FALSE){
            $response = json_decode($response);
            if(isset($response->local_id) && intval($response->local_id) > 0 ){
                $bugurl = "https://bitbucket.org/". $bbRepo . "/issue/" . $response->local_id;
                return( array('issueid'=>$response->local_id, 'issueurl'=>$bugurl, 'result'=>'thank-you for your question') );
            }else{
                return(FALSE);
            }
        }else{
            return(FALSE);
        }

    }
}
?>
