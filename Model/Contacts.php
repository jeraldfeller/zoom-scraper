<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 11/18/2019
 * Time: 7:40 AM
 */

class Contacts
{
    public $debug = TRUE;
    protected $db_pdo;

    public function insertContact($analys, $firstName, $lastName, $middleName, $suffix, $company = '')
    {
        $pdo = $this->getPdo();
        $sql = "INSERT INTO contacts (
                `analys`, `first_name`, `last_name`, `middle_name`, `suffix`, `company`, `completed`
                ) VALUES ($analys, '$firstName', '$lastName', '$middleName', '$suffix', '$company', 0)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pdo = null;
    }

    public function getContacts($status, $limit = 1){
        $pdo = $this->getPdo();
        $sql = 'SELECT * FROM `contacts` WHERE `completed` = '.$status.' ORDER BY RAND() LIMIT ' . $limit;
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $row;
        }
        $pdo = null;

        return $result;
    }

    public function setStatus($id, $status){
        $pdo = $this->getPdo();
        $sql = "UPDATE contacts SET completed = $status WHERE id = $id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pdo = null;
    }


    public function setProfile($id, $company, $position, $location, $hqPhone, $lastUpdated, $industries){
        $pdo = $this->getPdo();
        $sql = "UPDATE contacts SET company = '$company', position = '$position', location = '$location', hq_phone = '$hqPhone', last_updated = '$lastUpdated', industry = '$industries', last_updated = '$lastUpdated' WHERE id = $id";

        try{
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }catch (PDOException $e) {
            //error
            echo "Your fail message: " . $e->getMessage() ."\n";
            $file = fopen("sql-error.html","w");
            fwrite($file,$sql);
            fclose($file);
        }

        $pdo = null;
    }

    public function insertMeta($id, $individualId, $name, $organization, $position, $start, $end, $type){
        $pdo = $this->getPdo();
        $sql = "INSERT INTO contact_metas SET contact_id = $id, individual_id = '$individualId', name = '$name', organization = '$organization', position = '$position', start = '$start', end = '$end', type = '$type'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pdo = null;
    }

    public function setUrl($id, $url, $contactId){
        $pdo = $this->getPdo();
        $sql = "UPDATE contacts SET url = '$url', contact_id = '$contactId', completed = 2 WHERE id = $id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pdo = null;
    }


    public function getCookie(){
        $pdo = $this->getPdo();
        $sql = 'SELECT * FROM `options` WHERE `title` = "cookie"';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result = $row;
        }
        $pdo = null;

        return $result['content'];
    }


    public function curlTo($url){
        $curl = curl_init();
        $cookie = $this->getCookie();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Connection: keep-alive",
                "Host: www.zoominfo.com",
                "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3",
                "accept-encoding: gzip, deflate, br",
                "accept-language: en-US,en;q=0.9",
                "cookie: $cookie",
                "referer: https://www.zoominfo.com",
                "sec-fetch-mode: navigate",
                "sec-fetch-site: none",
                "sec-fetch-user: ?1",
                "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.79 Safari/537.36"
            ),
//            CURLOPT_COOKIEFILE => 'cookie/cookies.txt',
//            CURLOPT_COOKIEJAR => 'cookie/cookies.txt'
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err . "\n";
        } else {
            return $response;
        }
    }


    public function curlToProfile($url){
        $curl = curl_init();
        $cookie = $this->getCookie();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
                "accept-encoding: gzip, deflate, br",
                "accept-language: en-US,en;q=0.9",
                "cookie: $cookie",
                "sec-fetch-mode: navigate",
                "sec-fetch-site: none",
                "sec-fetch-user: ?1",
                "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.79 Safari/537.36"
            ),
//            CURLOPT_COOKIEFILE => 'cookie/cookies.txt',
//            CURLOPT_COOKIEJAR => 'cookie/cookies.txt'
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err . "\n";
        } else {
            return $response;
        }
    }

    public function getAspFormDataByUrl($url) {

        $requestHeaders = [
            'Accept: */*; q=0.01',
            'Accept-Encoding: none'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie/cookies.txt');
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie/cookies.txt');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36');

        $output = curl_exec($ch);
        $errno = curl_errno($ch);
        $errmsg = curl_error($ch);

        curl_close($ch);

        // No errors
        if ($errno !== 0) {
            // TODO: Log
            return false;
        }

        $formData = $this->getAspFormDataByString($output);
        return $formData;

    }


    public function acceptTerms($formData, $url) {

        $logMsg = "Attempting to accept terms for required cookie(s)...";
        echo $logMsg . "\n";

        $formData['jschl_answer'] = 67.3076341802;
        $formData = http_build_query($formData);


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie/cookies.txt');
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie/cookies.txt');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36');

        $output = curl_exec($ch);
        $errno = curl_errno($ch);
        $errmsg = curl_error($ch);

        curl_close($ch);

        if ($errno !== 0) {

            $logMsg = sprintf("Terms could not be accepted, cURL error: [%s] (%s)", $errmsg, $errno);
            echo $logMsg . "\n";
            return false;
        }
        else {

            $logMsg = "Terms were accepted. Cookie(s) was/were set";
            echo $logMsg . "\n";
            return $output;
        }

    }


    public function getAspFormDataByString($string) {

        // Extract __VIEWSTATE, __VIEWSTATEGENERATOR, and other asp puke
        $html = str_get_html($string);
        if (!$html) {
            // TODO: Log that HTML couldn't be parsed.
            return false;
        }

        $formData = [];

        $elements = $html->find("input[type=hidden]");
        foreach ($elements as $element) {

            if (isset($element->name) && isset($element->value)) {
                $formData[$element->name] = html_entity_decode($element->value, ENT_QUOTES);
            }
        }

        return $formData;

    }


    public function getPdo()
    {
        if (!$this->db_pdo) {
            if ($this->debug) {
                $this->db_pdo = new PDO(DB_DSN, DB_USER, DB_PWD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
            } else {
                $this->db_pdo = new PDO(DB_DSN, DB_USER, DB_PWD);
            }
        }
        return $this->db_pdo;
    }
}