<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'Model/config.php';
require 'Model/Contacts.php';
require 'simple_html_dom.php';
$opt = getopt("a:c:");
$action = trim($opt['a']);
$cookie = 'ELQSITEVISITED=YES; __cfduid=d60a64f4c1992db7b4cc3b7a71ce040f21573539559; _ga=GA1.2.929437662.1573539565; _mkto_trk=id:197-OCJ-776&token:_mch-zoominfo.com-1573539565590-70811; _pxvid=5ec0cd41-0514-11ea-bece-7d7d231835a6; _gid=GA1.2.1530950322.1575868412; _pxhd=bf912bbd347ce00aa03a5899955b79457ed60852220599aed8dbc30897bab42e:5ec0cd41-0514-11ea-bece-7d7d231835a6; landing_page=https://www.zoominfo.com/people; ELQSITEVISITED=YES; cf_clearance=f6f1f572fd9d5b33b3df65e33a3b4c397b39f891-1575928056-0-150; amplitude_id_14ff67f4fc837e2a741f025afb61859czoominfo.com=eyJkZXZpY2VJZCI6IjZjYzBlNGM1LWZhYTYtNGI5MC1iMDUzLTI5NTU5OTI5OTNjNlIiLCJ1c2VySWQiOm51bGwsIm9wdE91dCI6ZmFsc2UsInNlc3Npb25JZCI6MTU3NTkyMzAzOTgzOSwibGFzdEV2ZW50VGltZSI6MTU3NTkyODkxOTE5MCwiZXZlbnRJZCI6MTQsImlkZW50aWZ5SWQiOjAsInNlcXVlbmNlTnVtYmVyIjoxNH0=; _px3=cd18e0755e39686f992da177c2411dd4dd1f86d30638c4da22a9f373e3c83dfe:gEYBoaLE04wYezSXXTXl125VG7J77KN2ozzwkQEJEB0/LqwfrRBKyPSJQbelhZ+4OIJ5o1jOivLDOZ5ScKWF1Q==:1000:mAMPFgmrGdLt56fNTVC5CRvLgR5/dp1g/GqsFpykba46URkHZ0EYhZkmxnrZKk9r+8OAvN6guSYj+djGnGj33+5O5oqWPRvYWoo4HOgPnQT4DhEy0eDAWgo2ShVf+PR+wKYMrc5oFPm80UaE9Y2m4SiQiqQsXwXqhYuCbRWQHac=';
$limit = 10;
$contactsClass = new Contacts();



// STATUSES
# 1 = not found
# 2 = found
# 3 = completed

if($action == 'import'){
    $flag = true;
    $fileHandle = fopen('file.csv', "r");
    while (($data = fgetcsv($fileHandle, 10000, ",")) !== FALSE) {
        if ($flag) {
            $flag = false;
            continue;
        }
        $analys = ($data[0] ? $data[0] : 0);
        echo $data[1] . "\n";
        $nameArr = explode(' ', $data[1]);
        $middleName = '';
        $suffix = '';
        switch (count($nameArr)){
            case '2':
                $firstName = $nameArr[0];
                $lastName = $nameArr[1];
                break;
            case '3':
                if(strpos($nameArr[1], '.') !== false){
                    $firstName = $nameArr[0];
                    $middleName = $nameArr[1];
                    $lastName = $nameArr[2];
                }else if(strpos($nameArr[2], '.') !== false){
                    $firstName = $nameArr[0];
                    $lastName = $nameArr[1];
                    $suffix = $nameArr[2];
                }else{
                    $firstName = $nameArr[0];
                    $lastName = $nameArr[2];
                }
                break;
        }

        if($firstName != ''){
            $contactsClass->insertContact($analys, $firstName, $lastName, $middleName, $suffix);
        }
    }
}else if($action == 'search'){
    $contacts = $contactsClass->getContacts(0,  $limit);
    $successCount = 0;
    foreach($contacts as $contact){
        $url = 'https://www.zoominfo.com/people/'.ucfirst($contact['first_name']).'/'.ucfirst($contact['last_name']);
        $htmlData = $contactsClass->curlToProfile($url);
        $html = str_get_html($htmlData);
        if($html){
            $challengeForm = $html->find('#challenge-form', 0);
            if(!$challengeForm){
//            //set cookie
//            $formData = $contactsClass->getAspFormDataByString($htmlData);
//            $actionUrl = 'https://www.zoominfo.com'.$challengeForm->getAttribute('action');
//            $output = $contactsClass->acceptTerms($formData, $actionUrl);
//
//            $htmlData = $contactsClass->curlTo($url);
//            $html = str_get_html($htmlData);
//                $file = fopen("test-4.html","w");
//                echo fwrite($file,$htmlData);
//                fclose($file);

//            echo $url . "\n";
                if($html){
                    $table = $html->find('.page_table', 0);
                    if($table){
                        $tableRow = $table->find('.tableRow');
                        if($tableRow){
                            $hasMatch = false;
                            foreach($tableRow as $tr){
                                $td = $tr->find('.tableRow_companyDetails', 0);
                                if($td){
                                    $personName = $td->find('.tableRow_personName', 0);
                                    if($personName){
                                        $a = $personName->find('a', 0);
                                        if($a){
                                            $person = $a->innertext;
                                            $contactUrl = "https://www.zoominfo.com".$a->getAttribute('href');
                                            $contactUrlArr = explode('/', $a->getAttribute('href'));
                                            if($person == ucfirst($contact['first_name']).' '.ucfirst($contact['last_name'])){
                                                $contactsClass->setUrl($contact['id'], $contactUrl, $contactUrlArr[count($contactUrlArr) - 1]);
                                                echo $contactUrl . "\n";
                                                $successCount++;
                                                $hasMatch = true;
                                                break;
                                            }
                                        }
                                    }
                                }
                            }


                            if($hasMatch == false){
//                            echo ucfirst($contact['first_name']).' '.ucfirst($contact['last_name']) . " not found \n";
                                $contactsClass->setStatus($contact['id'], 1);
                            }

                        }

                    }else{
                        $contactsClass->setStatus($contact['id'], 1);
                    }
                }
            }
        }
    }

    echo "Success count $successCount out of $limit \n";
}else if($action == 'scrape'){
    $contacts = $contactsClass->getContacts(2,  1);
    $successCount = 0;
    foreach($contacts as $contact){
        $contactUrl = $contact['url'];
        $individualId = $contact['contact_id'];
        $name = $contact['first_name'] . ' ' . $contact['last_name'];
        echo $contactUrl . "\n";
        $htmlData = $contactsClass->curlTo($contactUrl);
        $html = str_get_html($htmlData);

        if($html){
            $personInfo = $html->find('.personMain_info', 0);
            if($personInfo){
                $sectionDetails = $personInfo->find('.primeSection_details', 0);
                if($sectionDetails){
                    $detailsRow = $sectionDetails->find('.primeSection_details-row');
                    $company = '';
                    $hqPhone = '';
                    $lastUpdated = '';
                    $location = '';
                    foreach($detailsRow as $row){
                        $title = trim($row->find('.primeSection_details-left', 0)->plaintext);
                        $value = trim($row->find('.primeSection_details-right', 0)->plaintext);

//                        echo $title . ' - ' . $value . "\n";
                        if($title == 'Location:'){
                            $location = $value;
                        }
                        if($title == 'Company:'){
                            $company = $value;
                        }
                        if($title == 'HQ Phone:'){
                            $hqPhone = $value;
                        }

                        if($title == 'Last Updated:'){
                            $lastUpdated = $value;
                        }

                    }

                    echo $location . "\n";
                    echo $company . "\n";
                    echo $hqPhone . "\n";
                    echo $lastUpdated . "\n";

                    echo "--------------- \n";

                }
            }
        }

    }
}

