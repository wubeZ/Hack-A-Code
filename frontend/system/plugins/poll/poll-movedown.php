<?php
   global $db;

   if (isset($_REQUEST['pollid']) && isset($_REQUEST['pageid']))
   {
      $sql = "SELECT * FROM CMS_POLL_OPTIONS WHERE id = ". $_REQUEST['pollid'];
      $result = mysqli_query($db, $sql);
      if ($data = mysqli_fetch_array($result))
      {
         $poll_index = $data['poll_index'];
         $new_index = $poll_index + 1;
   
         $sql = "UPDATE CMS_POLL_OPTIONS SET poll_index = $poll_index WHERE poll_index = $new_index AND page_id='". $_REQUEST['pageid']."'";
         mysqli_query($sql, $db) or die(mysqli_error($db));
         $sql = "UPDATE CMS_POLL_OPTIONS SET poll_index = $new_index WHERE id = '". $_REQUEST['pollid']."'";
         mysqli_query($db, $sql) or die(mysqli_error($db));
      }
   }
?>