<?php
   global $db;

   if (isset($_REQUEST['pageid']) && 
       isset($_REQUEST['mailinglist_from']) &&
       isset($_REQUEST['mailinglist_subject']) &&
       isset($_REQUEST['mailinglist_body']))
   {
      $page_id = $_REQUEST['pageid'];
      $subject = addslashes($_REQUEST['mailinglist_subject']);
      $body = addslashes($_REQUEST['mailinglist_body']);

      $sql = "SELECT * FROM CMS_MAILINGLIST_SUBSCRIBERS WHERE page_id=".$page_id. " AND mailinglist_status=1";
      $result = mysqli_query($db, $sql);
      $num_rows = mysqli_num_rows($result);
      $emails = '';
      while ($data = mysqli_fetch_array($result))
      {
//         echo htmlspecialchars($data['mailinglist_email']);
  //       echo "\n";

         $emails .= $data['mailinglist_email'];
         $emails .= ',';
      }
      $headers = 'From: '.$_REQUEST['mailinglist_from']."\r\n";
      $headers.='Reply-To:'.$_REQUEST['mailinglist_from']."\r\n";
      $headers .= 'Content-Type: text/plain; charset=iso-8859-1'."\r\n";
      $headers .= 'X-Mailer: PHP '.phpversion();
      $headers .= 'MIME-Version: 1.0'."\n";
      mail($emails, $subject, $body, $headers);
      
      echo "Message sent to $num_rows recipients."; 
   }
?>