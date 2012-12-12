<?php
if( !function_exists( 'curl_setopt' ) ) die( 'CURL required.' );

if( isset( $_COOKIE['username'] ) || isset( $_POST['username'] ) )
{
	if( !isset( $_COOKIE['username'] ) )
	{
		setcookie('username', $_POST['username'], time()+60*60*24*365);
		setcookie('password', $_POST['password'], time()+60*60*24*365);
	}

	$post = array(
		'act'	=>	'login',
		'to'	=>	'',
		'al_test'	=>	'3',
		'_origin'	=>	'http://vk.com',
		'ip_h'		=>	'24de5b091bd2f338fa',
		'email'		=>	$_COOKIE['username'],
		'pass'		=>	$_COOKIE['password'],
		'expire'	=>	'',
	);

	$ch = curl_init();

	curl_setopt( $ch, CURLOPT_URL, "http://login.vk.com/" );
	curl_setopt( $ch, CURLOPT_HEADER, 0 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_POST, count( $post ) );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $post );
	curl_setopt( $ch, CURLOPT_COOKIEJAR, 'cookies.txt' );
	curl_setopt( $ch, CURLOPT_COOKIEFILE, 'cookies.txt' );

	$res = curl_exec( $ch );
	curl_close( $ch );

	die( $res );

	//http://vk.com/search?c[section]=audio&c[q]=
}
else
{
?>
<form method="post">
	<label for="username">Username:</label> <input type="text" name="username" id="username" /><br />
	<label for="password">Password:</label> <input type="password" name="password" id="password" /><br />
	<input type="submit" name="login" value="Login to VK.com" />
</form>
<?php
}
?>