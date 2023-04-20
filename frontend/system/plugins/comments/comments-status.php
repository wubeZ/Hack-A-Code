<?php
   global $db;
   $sql = "UPDATE CMS_COMMENTS_ITEMS SET status='".$_REQUEST['comment_status']."' WHERE id='".$_REQUEST['comment_id']."'";
   mysqli_query($db, $sql) or die(mysqli_error($db));
?>