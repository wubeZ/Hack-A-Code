<?php
   global $db;

   if (isset($_REQUEST['hash']))
   {
      $hash = mysqli_real_escape_string($db, $_REQUEST['hash']);
      $sql = "SELECT * FROM CMS_MAILINGLIST_SUBSCRIBERS WHERE mailinglist_hash='$hash'";
      $result = mysqli_query($db, $sql);
      if (mysqli_num_rows($result) != 0)
      {
         $sql = "UPDATE CMS_MAILINGLIST_SUBSCRIBERS SET `mailinglist_status`='1' WHERE mailinglist_hash = '$hash'";
         mysqli_query($db, $sql) or die(mysqli_error($db));
         echo "Thank you, your email has been added to the mailing list.";
      }
   }
?>