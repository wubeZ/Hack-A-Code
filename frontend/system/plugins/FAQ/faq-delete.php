<?php
   global $db;
   if (isset($_REQUEST['faqid']))
   {
      $sql = "DELETE FROM CMS_FAQ WHERE id='".$_REQUEST['faqid']."'";
      mysqli_query($db, $sql) or die(mysqli_error($db));
   }
?>