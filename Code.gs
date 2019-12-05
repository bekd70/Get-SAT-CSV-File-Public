function getSatMsg() {

  //search inbox for email with specific Label
  //The email is automatically labeled by gmail on arriving with a specific Subject
  var threads = GmailApp.search('label:"SAT File"');
  //only runs if the email is present
  if (threads.length>0){
    var msgs = GmailApp.getMessagesForThreads(threads);
    //there should only ever be one thread, but will still work it there are multiple messages
    for (var i = 0; i < threads.length; i++) {
      var message = threads[i].getMessages()[0]

      var subject = message.getSubject();
      var content = message.getPlainBody();
      var msgSent = message.getDate();

      //used to isolate the file name in the body of the email
      //the filename of the csv is directly after the <span id='filename'> and directly before the end </span>
      //the filename gets saved in the first array block
      var tmp = content.split("<span id='fileName'>");
      var filename = tmp[1].split("</span>");
      Logger.log("the filename is: " + filename[0]);
    }

    //the url for the php utility to save the CSV file to the MySQL DB
    var url = "http://serverurl/Get-SAT-CSV-File-GH-public/getSatData.php?filename=" + filename[0]
    var data = UrlFetchApp.fetch(url)
    Logger.log("The Response Code is " + data.getResponseCode());
    Logger.log(data.getContentText());
    //if sucessful, send email and remove the label from the email so that it doesn't run again on this message
    if(data.getResponseCode()==200){
      var label = GmailApp.getUserLabelByName("SAT File");
      //email notification of success or failure will go to this address
      var email = 'user@domain.com';
      var sub = "SAT CSV File Import Success";
      var msg = "Output from the CSV Import is:<br>" + data.getContentText();
      var messageSent = GmailApp.sendEmail(email, sub, msg, {
        htmlBody: msg
      });
      for (var i=0; i< threads.length; i++) {
          //remove the labe from the email if successful
        threads[i].removeLabel(label);
      }
    }
    //if not sucessful send only email
    else{
      var label = GmailApp.getUserLabelByName("SAT File");
      var email = 'user@domain.com';
      var sub = "SAT CSV File Import error";
      var msg = "The import returned with the error code of " + data.getResponseCode() + " Output from the CSV Import is:<br>" + data.getContentText();
      var messageSent = GmailApp.sendEmail(email, sub, msg, {
        htmlBody: msg
      });
    }
  }
  else{
    Logger.log("There were no threads");
  }
}
