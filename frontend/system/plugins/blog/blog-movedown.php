<?php
   global $db;

   if (isset($_REQUEST['blogid']) && isset($_REQUEST['pageid']))
   {
      $sql = "SELECT * FROM CMS_BLOG_ITEMS WHERE id = '". $_REQUEST['blogid']."'";
      $result = mysqli_query($db, $sql);
      if ($data = mysqli_fetch_array($result))
      {
         $blog_index = $data['blog_index'];
         $new_index = $blog_index + 1;
   
         $sql = "UPDATE CMS_BLOG_ITEMS SET blog_index = $blog_index WHERE blog_index = $new_index AND page_id = '". $_REQUEST['pageid']."'";
         mysqli_query($db, $sql) or die(mysqli_error($db));
         $sql = "UPDATE CMS_BLOG_ITEMS SET blog_index = $new_index WHERE id = '". $_REQUEST['blogid']."'";
         mysqli_query($db, $sql) or die(mysqli_error($db));
      }
   }
?>