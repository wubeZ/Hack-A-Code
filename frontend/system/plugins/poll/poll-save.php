<?php
   global $db;

   $poll_id = isset($_REQUEST['pollid']) ? $_REQUEST['pollid'] : -1;
 
   if (isset($_REQUEST['pageid']) && 
       isset($_REQUEST['poll_option']) &&
       isset($_REQUEST['poll_votes']))
   {
      $page_id = $_REQUEST['pageid'];

      $poll_option = get_magic_quotes_gpc() ? trim($_REQUEST['poll_option']) : addslashes(trim($_REQUEST['poll_option']));
      $poll_option = str_replace("\\'", "&#39;", $poll_option);
      $poll_votes = intval($_REQUEST['poll_votes']);
      if ($poll_id >= 0)
      {
         $sql = "UPDATE CMS_POLL_OPTIONS SET poll_option='$poll_option', poll_votes='$poll_votes', page_id='$page_id' WHERE id = '$poll_id'";
         mysqli_query($db, $sql) or die(mysqli_error($db));
      }
      else
      {
         $sql = "SELECT * FROM CMS_POLL_OPTIONS WHERE page_id='$page_id'";
         $result = mysqli_query($db, $sql);
         $poll_index = mysqli_num_rows($result);
         $poll_index = $poll_index + 1;
         $sql = "INSERT CMS_POLL_OPTIONS (`poll_index`, `poll_option`, `poll_votes`, `page_id`) VALUES ($poll_index, '$poll_option', '$poll_votes', '$page_id')";
         mysqli_query($db, $sql) or die(mysqli_error($db));
      } 
   }
?>