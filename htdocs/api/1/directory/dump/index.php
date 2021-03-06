<?php
/*******************************************************************************
 *
 *  filename    : /api/1/directory/dump/index.php
 *  last change : 2013-11-10
 *  website     : http;//www.ecc12.com
 *  author      : Cameron King <cking@ecc12.com>
 *  copyright   : Copyright (c) 2013. Cameron King.
 *
 *  COPYING:
 *
 *  Permission to use, copy, modify, and/or distribute this software for any
 *  purpose with or without fee is hereby granted, provided that the above
 *  copyright notice and this permission notice appear in all copies.
 * 
 *  THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 *  WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 *  MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY
 *  SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 *  WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 *  ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF OR
 *  IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 *
 ******************************************************************************/

/**
 * Requires:
 *   ? uid=USERID         - assigned
 *   & key=APIKEY         - assigned
 * Returns JSON:
 *   {
 *     result: 1 or 0     - 1 if sucessful, 0 if error
 *     data: [            - empty array on failure, or array of families
 *       {
 *         id: ...,       - family object
 *         ...,
 *         people: [
 *           {
 *             id: ...,   - person object
 *             ...
 *           }, ...
 *         ]
 *       }, ...
 *     ]
 *   }
 */

require "../../../config.php";

$db = new mysqli($APICONFIG["db"]["host"], $APICONFIG["db"]["user"], $APICONFIG["db"]["password"], $APICONFIG["db"]["dbname"]);

function checkApiKey() {
  global $APICONFIG;
  $apikey = 0;
  if(isset($_GET["key"]) && strlen($_GET["key"]) &&
      isset($_GET["uid"]) && strlen($_GET["uid"])) {
    $apikey = $_GET["key"];
    $uid = $_GET["uid"];
    if(isset($APICONFIG["keys"][$uid]) && $apikey == $APICONFIG["keys"][$uid]) {
      return(1);
    }
  }
  return(0);
}

header("Content-type: text/x-json");

$response = array(
  'result' => 0,
  'data' => array()
);

if(! checkApiKey()) {
  print json_encode($response);
  exit;
}

$directorySQL = "select * from `v_directory_report`";
$directoryResults = $db->query($directorySQL);

$families = array();
while($person = $directoryResults->fetch_object())
{
  if(! isset($families[$person->per_fam_ID])) {
    $families[$person->per_fam_ID] = array(
      "id" => $person->per_fam_ID,
      "name" => $person->fam_Name,
      "address1" => $person->fam_Address1,
      "address2" => $person->fam_Address2,
      "city" => $person->fam_City,
      "state" => $person->fam_State,
      "zip" => $person->fam_Zip,
      "country" => $person->fam_Country,
      "homePhone" => $person->fam_HomePhone,
      "workPhone" => $person->fam_WorkPhone,
      "cellPhone" => $person->fam_CellPhone,
      "email" => $person->fam_Email,
      "people" => array()
    );
  }

  $new_person = array(
    "id" => $person->per_ID,
    "title" => $person->per_Title,
    "firstName" => $person->per_FirstName,
    "middleName" => $person->per_MiddleName,
    "lastName" => $person->per_LastName,
    "suffix" => $person->per_Suffix,
    "address1" => $person->per_Address1,
    "address2" => $person->per_Address2,
    "city" => $person->per_City,
    "state" => $person->per_State,
    "zip" => $person->per_Zip,
    "country" => $person->per_Country,
    "homePhone" => $person->per_HomePhone,
    "workPhone" => $person->per_WorkPhone,
    "cellPhone" => $person->per_CellPhone,
    "email" => $person->per_Email,
    "birthMonth" => $person->per_BirthMonth,
    "birthDay" => $person->per_BirthDay
  );

  $families[$person->per_fam_ID]["people"][] = $new_person;
}

$response["result"] = 1;
foreach($families as $k => $v) {
  $response["data"][] = $v;
}

print json_encode($response);
exit;

