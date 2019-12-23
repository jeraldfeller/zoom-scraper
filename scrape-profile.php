<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'Model/config.php';
require 'Model/Contacts.php';
require 'simple_html_dom.php';
$limit = 25;
$contactsClass = new Contacts();


// STATUSES
# 1 = not found
# 2 = found
# 3 = completed

$contacts = $contactsClass->getContacts(2, $limit);
$successCount = 0;
foreach ($contacts as $contact) {
    $contactUrl = $contact['url'];
    $individualId = $contact['contact_id'];
    $name = ucfirst($contact['first_name']) . ' ' . ucfirst($contact['last_name']);
//    echo $contactUrl . "\n";
    $htmlData = $contactsClass->curlToProfile($contactUrl);
    $html = str_get_html($htmlData);
//    $file = fopen("test-3.html","w");
//            echo fwrite($file,$htmlData);
//            fclose($file);
    if ($html) {
        $challengeForm = $html->find('#challenge-form', 0);
        if(!$challengeForm){
            $personInfo = $html->find('.personMain_info', 0);
            if ($personInfo) {
                $personMainOccupation = $personInfo->find('.personMain_basicInfo-occupation', 0);
                $position = '';
                if ($personMainOccupation) {
                    $position = $personMainOccupation->plaintext;
                }
                $sectionDetails = $personInfo->find('.primeSection_details', 0);
                if ($sectionDetails) {
                    $detailsRow = $sectionDetails->find('.primeSection_details-row');
                    $company = '';
                    $hqPhone = '';
                    $lastUpdated = '';
                    $location = '';
                    foreach ($detailsRow as $row) {
                        $title = addslashes(trim($row->find('.primeSection_details-left', 0)->plaintext));
                        $value = addslashes(trim($row->find('.primeSection_details-right', 0)->plaintext));

//                        echo $title . ' - ' . $value . "\n";
                        if ($title == 'Location:') {
                            $location = $value;
                        }
                        if ($title == 'Company:') {
                            $company = $value;
                        }
                        if ($title == 'HQ Phone:') {
                            $hqPhone = $value;
                        }

                        if ($title == 'Last Updated:') {
                            $lastUpdated = $value;
                        }

                    }

                    // industries

                    $industriesInfo = $html->find('.personCompanyInfo_industries-link');
                    $industriesArr = [];
                    foreach ($industriesInfo as $ind) {
                        $industriesArr[] = addslashes(trim($ind->plaintext));
                    }

//                    echo $position . "\n";
//                    echo $location . "\n";
//                    echo $company . "\n";
//                    echo $hqPhone . "\n";
//                    echo $lastUpdated . "\n";
//                    echo implode(',', $industriesArr) . "\n";

                    $contactsClass->setProfile($contact['id'], $company, $position, $location, $hqPhone, $lastUpdated, implode(',', $industriesArr));


                    // Metas

                    $generalInfoContainer = $html->find('.personGeneral', 0);
                    if ($generalInfoContainer) {
                        $personSection = $generalInfoContainer->find('.personGeneral-section');
                        foreach ($personSection as $sec) {
                            $titleSection = $sec->find('.personGeneral-section-titleSection', 0);
                            if($titleSection){
                                $sectionInfoItem = $sec->find('.personGeneral-section-info-item');
                                $type = addslashes(trim($titleSection->plaintext));

//                                echo "\n\n";
//                                echo "##### $type ##### \n";
                                foreach ($sectionInfoItem as $row) {
                                    $position = '';
                                    $organization = '';
                                    $start = '';
                                    $end = '';

                                    $positionObj = $row->find('.personGeneral-section-info-item-info-mainText', 0);
                                    if ($positionObj) {
                                        $position = addslashes(trim($positionObj->plaintext));
                                    }

                                    $organizationObj = $row->find('.personGeneral-section-info-item-info-secondaryText', 0);
                                    if ($organizationObj) {
                                        $organization = addslashes(trim($organizationObj->plaintext));
                                    }

                                    $dateObj = $row->find('.personGeneral-section-info-item-info-secondaryText', 1);
                                    if ($dateObj) {
                                        $dateArr = explode('-', trim($dateObj->plaintext));
                                        if (count($dateArr) == 2) {
                                            $start = $dateArr[0];
                                            $end = $dateArr[1];
                                        }
                                    }

                                    $contactsClass->insertMeta($contact['id'], $contact['contact_id'], $name, $organization, $position, $start, $end, $type);
                                }
//                                echo "$organization \n";
//                                echo "$position \n";
//                                echo "$start - $end \n";
//
//                                echo "##### END ##### \n";
//
//                                echo "\n\n";
                            }
                        }
                    }


//                    echo "--------------- \n";
                    $contactsClass->setStatus($contact['id'], 3);
                    $successCount++;
                    echo "$individualId \n";

                }

            }
        }
    }

}

echo "Success count $successCount out of $limit \n";
