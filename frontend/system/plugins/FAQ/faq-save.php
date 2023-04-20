<?php
   global $db;

   $faq_id = isset($_REQUEST['faqid']) ? $_REQUEST['faqid'] : -1;
 
   if (isset($_REQUEST['pageid']) && 
       isset($_REQUEST['question']) &&
       isset($_REQUEST['answer']))
   {
      $page_id = $_REQUEST['pageid'];
      $timestamp = date("y-m-d H:i:s", time());

      $faq_question = get_magic_quotes_gpc() ? trim($_REQUEST['question']) : addslashes(trim($_REQUEST['question']));
      $faq_question = str_replace("\\'", "&#39;", $faq_question);
      $faq_answer = get_magic_quotes_gpc() ? trim($_REQUEST['answer']) : addslashes(trim($_REQUEST['answer']));
      $faq_answer = str_replace("\\'", "&#39;", $faq_answer);
      if ($faq_id >= 0)
      {
         $sql = "UPDATE CMS_FAQ SET faq_question = '$faq_question', faq_answer = '$faq_answer', faq_date = '$timestamp', page_id = '$page_id' WHERE id = '$faq_id'";
         mysqli_query($db, $sql) or die(mysqli_error($db));
      }
      else
      {
         $sql = "SELECT * FROM CMS_FAQ WHERE page_id='$page_id'";
         $result = mysqli_query($db, $sql);
         $faq_index = mysqli_num_rows($result);
         $faq_index = $faq_index + 1;
         $sql = "INSERT CMS_FAQ (`faq_index`, `faq_date`, `faq_question`, `faq_answer`, `page_id`) VALUES ('$faq_index', '$timestamp', '$faq_question', '$faq_answer', '$page_id')";
         mysqli_query($db, $sql) or die(mysqli_error($db));
      } 
   }
?>