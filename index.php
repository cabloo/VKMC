<?php
if( !function_exists( 'curl_setopt' ) ) die( 'CURL required.' );

if( isset( $_COOKIE['username'] ) || isset( $_POST['username'] ) )
{
	if( !isset( $_COOKIE['username'] ) )
	{
		setcookie('username', $_POST['username'], time()+60*60*24*365);
		setcookie('password', $_POST['password'], time()+60*60*24*365);
	}

	if( isset( $_POST['code'] ) )
	{
		$ch = curl_init();

		$post = array(
			'code'	=>	$_POST['code'],
		);

		curl_setopt( $ch, CURLOPT_URL, "https://login.vk.com/" . $_POST['url'] );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $post );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_COOKIEJAR, 'cookies.txt' );
		curl_setopt( $ch, CURLOPT_COOKIEFILE, 'cookies.txt' );
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );

		$res = curl_exec( $ch );
		curl_close( $ch );

		echo "https://login.vk.com/" . $_POST['url'] . "\n" . $res;
	}
	else
	{
		$post = array(
			'email'		=>	$_COOKIE['username'],
			'pass'		=>	$_COOKIE['password'],
		);

		$ch = curl_init();

		curl_setopt( $ch, CURLOPT_URL, "http://m.vk.com/login" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );

		$res = curl_exec( $ch );
		curl_close( $ch );

		$ch = curl_init();

		curl_setopt( $ch, CURLOPT_URL, strstr( substr( strstr( $res, 'action="' ), 8 ), '"', true ) );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $post );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_COOKIEJAR, 'cookies.txt' );
		curl_setopt( $ch, CURLOPT_COOKIEFILE, 'cookies.txt' );
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_REFERER, "http://m.vk.com/login" );

		$res = curl_exec( $ch );
		curl_close( $ch );

		if( $res != "" )
		{
			$exp = explode( 'action="/login.php?act=security_check', $res, 2 );
			if( isset( $exp[1] ) )
			{
				$q = substr( $exp[1], 0, strpos( $exp[1], '"' ) );
				//They are requesting a security code.
				?>
				<form method="post">
					<input type="hidden" name="referer" value="<?php echo curl_getinfo( $ch, CURLINFO_EFFECTIVE_URL ) ?>" />
					<input type="hidden" name="url" value="login.php?act=security_check<?php echo $q ?>" />
					Code: <input type="text" name="code" value="" /> &nbsp; <input type="submit" name="security-check" value="Go" />
				</form>
				<?php
				die( $res );
			}
		}
	}

	$search = "Adele";

	$ch = curl_init();

	curl_setopt( $ch, CURLOPT_URL, "http://m.vk.com/audio?act=search&q=" . urlencode( $search ) );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
	curl_setopt( $ch, CURLOPT_COOKIEJAR, 'cookies.txt' );
	curl_setopt( $ch, CURLOPT_COOKIEFILE, 'cookies.txt' );

	$res = curl_exec( $ch );
	curl_close( $ch );

	echo "http://m.vk.com/audio?act=search&q=" . urlencode( $search ) . ": " . $res;
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