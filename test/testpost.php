<?php
/**
 * Function to do some consistent posting
 * We need a range of titles for testing
 */

$json = array(
  'author'=>'William Shakespeare', 
  'title'=>'Macbeth',
  'text'=>'Mary had a little lamb; on a blasted heath'	
);
$data = json_encode($json);
$url = 'http://localhost/wordpress/index.php';
$c = curl_init();
curl_setopt($c, CURLOPT_URL, $url);
curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
curl_setopt($c, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($c, CURLOPT_POSTFIELDS, $data);
curl_setopt($c, CURLOPT_HTTPHEADER, array(
   'Content-Type: application/json'
  )
);

$output = curl_exec($c);
if($output === false){
    trigger_error('Error curl : '.curl_error($c),E_USER_WARNING);
}
else{
	print $data;
    var_dump($output);
}
curl_close($c);
?>
