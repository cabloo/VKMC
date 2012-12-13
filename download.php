<?php
header('Content-Type: audio/mpeg');
header('Content-Disposition: inline;filename="' . $_GET['title'] . '"');
header('Cache-Control: no-cache');
header('Content-Transfer-Encoding: chunked');
$ch = curl_init();

curl_setopt( $ch, CURLOPT_URL, $_GET['url'] );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
curl_setopt( $ch, CURLOPT_COOKIEJAR, 'cookies.txt' );
curl_setopt( $ch, CURLOPT_COOKIEFILE, 'cookies.txt' );

echo curl_exec( $ch );
curl_close( $ch );
?>
