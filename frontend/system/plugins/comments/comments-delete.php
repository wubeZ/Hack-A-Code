<?php
   global $db;
   $sql = "DELETE FROM CMS_COMMENTS_ITEMS WHERE id='".$_REQUEST['comment_id']."'";
   mysqli_query($db, $sql) or die(mysqli_error($db));
?>