<?php
$ds          = DIRECTORY_SEPARATOR;  //1
 
$storeFolder = 'files';   //2
 
if (!empty($_FILES)) {
   
    $tempFile = $_FILES['file']['tmp_name'];          //3            
    echo '<script>';
  echo 'console.log('. json_encode(  $tempFile ) .')';
  echo '</script>';
      
    $targetPath = dirname( __FILE__ ) . $ds. $storeFolder . $ds;  //4
     
    $targetFile =  $targetPath. $_FILES['file']['name'];  //5
 
    move_uploaded_file($tempFile,$targetFile); //6
     
}
?>  