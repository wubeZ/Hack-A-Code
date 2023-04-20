<?php
   global $db;

   if (isset($_REQUEST['faqid']) && isset($_REQUEST['pageid']))
   {
      $sql = "SELECT * FROM CMS_FAQ WHERE id = '". $_REQUEST['faqid']."'";
      $result = mysqli_query($db, $sql);
      if ($data = mysqli_fetch_array($result))
      {
         $faq_index = $data['faq_index'];
         $new_index = $faq_index + 1;
   
         $sql = "UPDATE CMS_FAQ SET faq_index = $faq_index WHERE faq_index = $new_index AND page_id = '". $_REQUEST['pageid']."'";
         mysqli_query($db, $sql) or die(mysqli_error($db));
         $sql = "UPDATE CMS_FAQ SET faq_index = $new_index WHERE id = '". $_REQUEST['faqid']."'";
         mysqli_query($db, $sql) or die(mysqli_error($db));
      }
   }
?>