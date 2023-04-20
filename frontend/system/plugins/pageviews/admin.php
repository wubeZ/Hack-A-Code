<?php
   global $db; 

   if (!$authorized)   {
      exit;
   }

   echo "<script src=\"https://www.gstatic.com/charts/loader.js\"></script>\n";
   echo "<script>\n";
   echo "google.charts.load('current', { packages: ['corechart'] });\n";
   echo "google.charts.setOnLoadCallback(function()\n";
   echo "{\n";
   echo "   var data = new google.visualization.DataTable();\n";
   echo "   data.addColumn('string', 'Page');\n";
   echo "   data.addColumn('number', 'Views');\n";

   $data = "";
   $sql = "SELECT name, views FROM CMS_PAGES ORDER BY views DESC LIMIT 10";
   $result = mysqli_query($db, $sql);
   while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
   {
      $data .= "['" . $row['name'] . "', " . $row['views'] . "],";
   }
   $data = rtrim($data, ',');

   echo "   data.addRows([$data]);\r\n";
   echo "   var optionsPie = {title:'Page Views - Top 10',width:'100%',height:'100%',is3D:true,animation:{startup:true,duration:2000,easing:'linear'},pieSliceText:'percentage'};\n";
   echo "   var chartPie = new google.visualization.PieChart(document.getElementById('pie'));\n";
   echo "   chartPie.draw(data, optionsPie);\n";

   echo "   var dataBar = new google.visualization.DataTable();\n";
   echo "   dataBar.addColumn('string', 'Page');\n";
   echo "   dataBar.addColumn('number', 'Views');\n";
   echo "   dataBar.addRows([$data]);\r\n";
   echo "   var optionsBar = {width:'100%',height:'100%',animation:{startup:true,duration:2000,easing:'linear'},isStacked:false};\n";
   echo "   var chartBar = new google.visualization.BarChart(document.getElementById('bars'));\n";
   echo "   chartBar.draw(dataBar, optionsBar);\n";
 
   echo "});\n";

   echo "</script>\n";

   echo "<div id=\"pie\" style=\"display:inline-block;width:100%;height:400px;margin-top:25px\"></div>\n";
   echo "<div id=\"bars\" style=\"display:inline-block;width:100%;margin-top:25px\"></div>\n";
?>