<?php

function removeWhiteSpaces($string) {
    return preg_replace('/\s+/', '', $string);
}

function checkZip($zip) {
    // Remove everything not a digit
    $newZip = "";
    for ($i = 0; $i < strlen($zip); $i++) {
        if (ctype_digit($zip[$i])) {
            $newZip .= $zip[$i];
        }
    }

    if (strlen($newZip) != 5) {
        echo "ERROR in zip: ".$newZip."\n";
    }

    return substr($newZip,0,3)." ".substr($newZip,3,2);
}

function fixPersonalNumber($personalNumber) {
    // Remove everything not a digit
    $newPnr = "";
    for ($i = 0; $i < strlen($personalNumber); $i++) {
        if (ctype_digit($personalNumber[$i])) {
            $newPnr .= $personalNumber[$i];
        }
    }

    // Check if 10 or 12 chars
    if (strlen($newPnr) == 12) {
        if (substr($newPnr, 0, 2) == "19" || substr($newPnr, 0, 2) == "20") {
            $newPnr = substr($newPnr,2);
        }
    }
    if (strlen($newPnr) != 10) {
        echo "ERROR in pnr: ".$newPnr."\n";
    }
    $newPnr = substr($newPnr, 0, 6).'-'.substr($newPnr, 6, 4);

    return $newPnr;

}

function genderDetector($personalNumber) {
    return ($personalNumber[9] % 2 == 0) ? "Kvinna" : "Man";
}

function fixMobile($number) {
    // Remove everything not a digit
    $newCell = "";
    for ($i = 0; $i < strlen($number); $i++) {
        if (ctype_digit($number[$i])) {
            $newCell .= $number[$i];
        }
    }
    if (substr($newCell,0,2) == "46") {
        $newCell = "0".substr($newCell,2);
    }
    return $newCell;
}

function printHeader($nwList, $returnArray = false) {
    $list = [];
    foreach($nwList as $key => $value) {
        $list[] = $key;
    }
    if (!$returnArray) {
        echo implode(';', $list) . "\n";
    } else {
        return $list;
    }
}

function printRow($list, $returnArray = false) {
    $nwList = buildNWList();
    $tmpList = [];
    foreach($nwList as $key => $value) {
        $tmpList[$key] = array_key_exists($key, $list) ? $list[$key] : $value;
    }
    $tmpList2 = [];
    foreach($tmpList as $key => $value) {
        $tmpList2[] = $value;
    }
    if (!$returnArray) {
        echo implode(';', $tmpList2) . "\n";
    } else {
        return $tmpList2;
    }
}


function buildNWList() {
     return [
        "p_id" => null,
        "customerNumber" => null,
        "firstName" => null,
        "lastName" => null,
        "organization" => null,
        "businessUnit" => null,
        "birthDate" => null,
        "personNumber" => null,
        "gender" => null,
        "email" => null,
        "streetAddress" => null,
        "postalCode" => null,
        "postalCity" => null,
        "homePhone" => null,
        "workPhone" => null,
        "mobilePhone" => null,
        "category" => null,
        "region" => null,
        "groups" => null,
        "jobTitle" => null,
        "cardNumber" => null,
        "s_id" => null,
        "product" => null,
        "price" => null,
        "start" => null,
        "end" => null,
        "debitedUntil" => null,
        "renew" => null,
        "payer_id" => null,
        "m_id" => null,
        "betalarNummer" => null,
        "kontoInnehavare" => null,
        "clearingNummer" => null,
        "kontoNummer" => null,
        "registered" => null
     ];
}

$nwList = buildNWList();



$fileName = "../nordicdata/nordic_list.csv";
$f = fopen($fileName, "r");
$output = fopen('nordic.csv','w');
fputcsv($output, printHeader($nwList, true));

$i = 0;
while ($row = fgetcsv($f)) {

    $addressData = explode(";", $row[1]);

    if (!isset($addressData[0]) ||
        !isset($addressData[1]) ||
        !isset($addressData[2]) ||
        !isset($addressData[3]) ||
        !isset($addressData[4]) ||
        !isset($addressData[5]) ||
        !isset($addressData[6]) ||
        !isset($addressData[7])
    ) {
        echo "ERROR: ".print_r($addressData, TRUE);
        continue;
    }

    $personalNumber = fixPersonalNumber($addressData[2]);

    $list = [
        "firstName" => trim(ucwords(strtolower($addressData[0]))),
        "lastName" => trim(ucwords(strtolower($addressData[1]))),
        "p_id" => trim($personalNumber),
        "personNumber" => trim($personalNumber),
        "organization" => "LETS DEAL AB",
        "businessUnit" => "Gymkort",
        "gender" => genderDetector($personalNumber),
        "mobilePhone" => trim(fixMobile($addressData[3])),
        "email" => trim(strtolower($addressData[4])),
        "streetAddress" => trim(ucwords(strtolower($addressData[5]))),
        "postalCode" => trim(checkZip($addressData[6])),
        "postalCity" => trim(ucwords(strtolower($addressData[7]))),
        "product" => $row[0] == "12 mån" ? "Årskort" : $row[0]
    ];

    //printRow($list);
    fputcsv($output, printRow($list, true));
    if ($i++ == 300000) {
        break;
    }
}
fclose($f);
fclose($output);
