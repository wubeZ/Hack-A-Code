<?php
   global $db;
   if (isset($_REQUEST['pollid']))
   {
      $sql = "DELETE FROM CMS_POLL_OPTIONS WHERE id='".$_REQUEST['pollid']."'";
      mysqli_query($db, $sql) or die(mysqli_error($db));
   }
?>