<?php
   global $db;

   $field_id = isset($_REQUEST['fieldid']) ? $_REQUEST['fieldid'] : -1;
 
   if (isset($_REQUEST['pageid']) && 
       isset($_REQUEST['field_name']) &&
       isset($_REQUEST['field_type']) &&
       isset($_REQUEST['field_extra']) &&
       isset($_REQUEST['field_required']))
   {
      $page_id = $_REQUEST['pageid'];

      $field_name = get_magic_quotes_gpc() ? trim($_REQUEST['field_name']) : addslashes(trim($_REQUEST['field_name']));
      $field_name = str_replace("\\'", "&#39;", $field_name);
      $field_type = get_magic_quotes_gpc() ? $_REQUEST['field_type'] : addslashes($_REQUEST['field_type']);
      $field_extra = get_magic_quotes_gpc() ? trim($_REQUEST['field_extra']) : addslashes(trim($_REQUEST['field_extra']));
      $field_extra = str_replace("\\'", "&#39;", $field_extra);
      $field_required = $_REQUEST['field_required'];

      if ($field_id >= 0)
      {
         $sql = "UPDATE CMS_FORM_FIELDS SET field_name='$field_name', field_type='$field_type', field_extra='$field_extra', field_required='$field_required', page_id='$page_id' WHERE id = '$field_id'";
         mysqli_query($db, $sql) or die(mysqli_error($db));
      }
      else
      {
         $sql = "SELECT * FROM CMS_FORM_FIELDS WHERE page_id='$page_id'";
         $result = mysqli_query($db, $sql);
         $field_index = mysqli_num_rows($result);
         $field_index = $field_index + 1;
         $sql = "INSERT CMS_FORM_FIELDS (`field_index`, `field_name`, `field_type`, `field_extra`, `field_required`, `page_id`) VALUES ($field_index, '$field_name', '$field_type', '$field_extra', '$field_required', '$page_id')";
         mysqli_query($db, $sql) or die(mysqli_error($db));
      } 
   }
?>