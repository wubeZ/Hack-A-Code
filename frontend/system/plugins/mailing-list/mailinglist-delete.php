<?php
   global $db;
   if (isset($_REQUEST['mailinglistid']))
   {
      $sql = 'DELETE FROM CMS_MAILINGLIST_SUBSCRIBERS WHERE id='.$_REQUEST['mailinglistid'];
      mysqli_query($db, $sql) or die(mysqli_error($db));
   }
?>