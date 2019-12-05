# Get-SAT-CSV-File-Public
**Automatically download** CSV file with student SAT scores from College Board upon **email notification** and import data into MySQL table. You must be have K-12 Assessment Reporting Access Granted by your school's SAT Administrator in order to receive email notification by College Board.  The incoming email from College Board must bet Labeled **"SAT CSV"** by gmail. You can set up a filter on the incoming messages with the subject **"SAT CSV Score Data File Posted"** to automatically have the "SAT CSV" label applied.  Set google script (code.gs) to run on a **trigger every 5 minutes** to look for the email with the correct label.

The Google script, when run, will look for any message threads with the label and extract the csv file name from the body of the email.  The script them passes the filename to a PHP application in the url.

The PHP application requests the file from the College Board API.  The request has the filemane in the URL and the account username and password in POST values.  The are the same credentials for the K-12 Assessment Reporting Access Granted by your school's SAT Administrator.

The score data is transmitted as JSON and saved to an array.  The are over 800 columns that are send for each student score record.  This script is only saving 35 values from each record.  Each row from the JSON is inserted separately.

The output of each insert is saved in the Output Buffer.  The OB is then sent back to the google script in the response.  This data is sent in an email upon completion and the label is removed from the original email so that it will not be run again.

I have included an SQL script to create the table in MySQL.
