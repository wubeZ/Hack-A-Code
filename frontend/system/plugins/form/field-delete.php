<?php
   global $db;
   if (isset($_REQUEST['fieldid']))
   {
      $sql = "DELETE FROM CMS_FORM_FIELDS WHERE id='".$_REQUEST['fieldid']."'";
      mysqli_query($db, $sql) or die(mysqli_error($db));
   }
?>