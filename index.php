<?php
require 'Model/config.php';
require 'Model/Contacts.php';

$contacts = new Contacts();
$count = $contacts->getContactCount(3);

echo '<h1>'.$count['contactCount'].'</h1>';