<?php
   global $db;

   $mailinglist_id = isset($_REQUEST['mailinglistid']) ? $_REQUEST['mailinglistid'] : -1;
 
   if (isset($_REQUEST['pageid']) && 
       isset($_REQUEST['mailinglist_name']) &&
       isset($_REQUEST['mailinglist_email']))
   {
      $page_id = $_REQUEST['pageid'];
      $mailinglist_name = get_magic_quotes_gpc() ? trim($_REQUEST['mailinglist_name']) : addslashes(trim($_REQUEST['mailinglist_name']));
      $mailinglist_email = get_magic_quotes_gpc() ? trim($_REQUEST['mailinglist_email']) : addslashes(trim($_REQUEST['mailinglist_email']));
      $mailinglist_phone = get_magic_quotes_gpc() ? trim($_REQUEST['mailinglist_phone']) : addslashes(trim($_REQUEST['mailinglist_phone']));

      if ($mailinglist_id >= 0)
      {
         $sql = "UPDATE CMS_MAILINGLIST_SUBSCRIBERS SET `mailinglist_name`='$mailinglist_name', `mailinglist_email`='$mailinglist_email', `mailinglist_phone`='$mailinglist_phone',`page_id`=$page_id WHERE `id` = $mailinglist_id";
         mysqli_query($db, $sql) or die(mysqli_error($db));
      }
      else
      {
         $sql = "INSERT CMS_MAILINGLIST_SUBSCRIBERS (`mailinglist_name`, `mailinglist_email`, `mailinglist_phone`, `page_id`) VALUES ('$mailinglist_name', '$mailinglist_email', '$mailinglist_phone', '$page_id')";
         mysqli_query($db, $sql) or die(mysqli_error($db));
      } 
   }
?>