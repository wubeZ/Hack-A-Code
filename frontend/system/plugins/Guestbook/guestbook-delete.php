<?php
   global $db;
   if (isset($_REQUEST['guestbookid']))
   {
      $sql = "DELETE FROM CMS_GUESTBOOK_ITEMS WHERE id='".$_REQUEST['guestbookid']."'";
      mysqli_query($db, $sql) or die(mysqli_error($db));
   }
?>