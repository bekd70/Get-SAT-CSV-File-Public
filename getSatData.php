<?php
	ob_start();
	include_once 'dbconnect.php';


	if($_GET && $_GET['filename']){

		$url = "https://scoresdownload.collegeboard.org/pascoredwnld/file?filename=".$_GET['filename'];
		//saves the username and password that will access the college board api
        //filename to download will be passed in Url
        //college board username and password will be passed in POST
		$payload= "{\"username\": \"collegeboard_username\",\"password\": \"collegeboard_password\"}";
		//echo $payload;
		$ch = curl_init();

		curl_setopt_array($ch, [
		    CURLOPT_RETURNTRANSFER => true,
		    CURLOPT_URL => $url,
		    CURLOPT_POST => 1,
		    CURLOPT_HTTPHEADER => array('Content-Type: application/json','Accept: application/json'),
		    CURLOPT_POSTFIELDS => $payload,
		    CURLOPT_MAXREDIRS => 10,     // stop after 10 redirects
		    CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
			CURLOPT_TIMEOUT        => 120

		]);

		$data = curl_exec($ch);
        //saves the data from csv file to array
		$data2 = json_decode($data, $assoc = true);

		$errors = curl_error($ch);
		$response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$fileUrl = $data2['fileUrl'];

		$rawData = file_get_contents($fileUrl);
		$rows = explode("\n",$rawData);
		$satData = array();
		foreach($rows as $row) {
			$satData[] = str_getcsv($row);
		}

		curl_close($ch);
		$insert = "INSERT INTO sat_data (AI_CODE, AI_NAME, COHORT_YEAR, DISTRICT_NAME, NAME_LAST, NAME_FIRST, NAME_MI, SEX, DERIVED_AGGREGATE_RACE_ETH,
			BIRTH_DATE, CB_ID, ADDRESS_LINE1, ADDRESS_CITY, ADDRESS_COUNTRY, EMAIL, LATEST_ASSESSMENT_DATE, LATEST_GRADE_LEVEL, LATEST_SAT_TOTAL,
			LATEST_SAT_EBRW, LATEST_SAT_MATH_SECTION, LATEST_SAT_READING, LATEST_SAT_WRIT_LANG, LATEST_SAT_MATH, PERCENTILE_NATREP_SAT_TOTAL,
			PERCENTILE_NATREP_SAT_EBRW, PERCENTILE_NATREP_SAT_MATH_SECTION, PERCENTILE_NATREP_SAT_READING, PERCENTILE_NATREP_SAT_WRIT_LANG,
			PERCENTILE_CBSNATION_SAT_MATH, PERCENTILE_NATUSER_SAT_TOTAL, PERCENTILE_NATUSER_SAT_EBRW, PERCENTILE_NATUSER_SAT_MATH_SECTION,
			PERCENTILE_NATUSER_SAT_READING,  PERCENTILE_NATUSER_SAT_WRIT_LANG, PERCENTILE_CBSSTATE_SAT_MATH)\n";
			//echo $insert;
		$numrows = sizeof($satData);
        //output to pass back to GAS Script calling this file
		$output = '';
        //CB adds an empty row at the end
		for($i=1;$i<$numrows-1;$i++){

			$values = "VALUES (".$satData[$i][0].", \"".$satData[$i][1]."\", ".$satData[$i][3].", \"".$satData[$i][4]."\", \"".
			$satData[$i][5]."\", \"".$satData[$i][6]."\", ".(($satData[$i][7] == '')?"NULL":("\"".$satData[$i][7]."\"")).", \"".$satData[$i][8]."\", ".
			$satData[$i][21].", \"".$satData[$i][22]."\", ".$satData[$i][24].", \"".$satData[$i][26]."\", \"".$satData[$i][28]."\", \"".
			$satData[$i][32]."\", \"".$satData[$i][36]."\", \"".(($satData[$i][43] == '')?"NULL":($satData[$i][43]))."\", ".
			(($satData[$i][44] == '')?"NULL":($satData[$i][44])).", ".(($satData[$i][46] == '')?"NULL":($satData[$i][46])).", ".
			(($satData[$i][47] == '')?"NULL":($satData[$i][47])).", ".(($satData[$i][48] == '')?"NULL":($satData[$i][48])).", ".
			(($satData[$i][49] == '')?"NULL":($satData[$i][49])).", ".$satData[$i][50].", ".
			(($satData[$i][51] == '')?"NULL":($satData[$i][51])).", ".(($satData[$i][72] == '')?"NULL":($satData[$i][72])).", ".
			(($satData[$i][73] == '')?"NULL":($satData[$i][73])).", ".(($satData[$i][74] == '')?"NULL":($satData[$i][74])).", ".
			(($satData[$i][75] == '')?"NULL":($satData[$i][75])).", ".(($satData[$i][76] == '')?"NULL":($satData[$i][76])).", ".
			(($satData[$i][88] == '')?"NULL":($satData[$i][88])).", ".(($satData[$i][92] == '')?"NULL":($satData[$i][92])).", ".
			(($satData[$i][93] == '')?"NULL":($satData[$i][93])).", ".(($satData[$i][94] == '')?"NULL":($satData[$i][94])).", ".
			(($satData[$i][95] == '')?"NULL":($satData[$i][95])).", ".(($satData[$i][96] == '')?"NULL":($satData[$i][96])).", ".
			(($satData[$i][108] == '')?"NULL":($satData[$i][108])).");\n\n";
			//echo $values;
			$insertSql = $insert.$values;
			//echo $insertSql;
			if ($mysqli->query($insertSql) === TRUE) {
                //This will get passed back to the Google Apps Script that called this script
				echo "Insert row number $i Succesful<br>";
			}
			else{
                //This will get passed back to the Google Apps Script that called this script
				echo "<br>Error on row number $i: ".date('Y-m-d H:i:s', time()).": \n" . $insertSql . "<br>\n" . $mysqli->error."<br>\n";
			}

		}
		$result = ob_get_contents();

		return $result; //to GAS Script
		ob_end_clean();
	}
?>
