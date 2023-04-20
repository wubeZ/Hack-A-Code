<?php
   global $db;
   if (isset($_REQUEST['blogid']))
   {
      $sql = "DELETE FROM CMS_BLOG_ITEMS WHERE id='".$_REQUEST['blogid']."'";
      mysqli_query($db, $sql) or die(mysqli_error($db));
   }
?>