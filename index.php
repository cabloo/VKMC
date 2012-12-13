<?php

function map_bitrate( $rate )
{
	$rates = array( 8, 16, 24, 32, 40, 48, 56, 64, 80, 96, 112, 128, 144, 160, 192, 224, 256, 320 );
	foreach( $rates as $r )
	{
		if( ($rate * 0.9) < $r && ($rate * 1.1) > $r )
			return $r;
	}
	return $rate;
}

function do_login(){
	$post = array(
		'email'		=>	$_COOKIE['username'],
		'pass'		=>	$_COOKIE['password'],
	);

	$ch1 = curl_init();

	curl_setopt( $ch1, CURLOPT_URL, "http://m.vk.com/login" );
	curl_setopt( $ch1, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch1, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt( $ch1, CURLOPT_MAXREDIRS, 10 );

	$res = curl_exec( $ch1 );
	curl_close( $ch1 );

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

	$r = curl_exec( $ch );
	$info = curl_getinfo( $ch );
	curl_close( $ch );

	if( $r != "" )
	{
		$exp = explode( 'action="/login.php?act=security_check', $r, 2 );
		if( isset( $exp[1] ) )
		{
			$q = substr( $exp[1], 0, strpos( $exp[1], '"' ) );
			//They are requesting a security code.
			?>
			<form method="post">
				<input type="hidden" name="referer" value="<?php echo $info['url'] ?>" />
				<input type="hidden" name="url" value="login.php?act=security_check<?php echo $q ?>" />
				Code: <input type="text" name="code" value="" /> &nbsp; <input type="submit" name="security-check" value="Go" />
			</form>
			<?php
			die( $r );
		}
	}
}
if( !function_exists( 'curl_setopt' ) ) die( 'CURL required.' );

if( isset( $_COOKIE['username'] ) || isset( $_POST['username'] ) )
{
	if( !isset( $_COOKIE['username'] ) )
	{
		setcookie('username', $_POST['username'], time()+60*60*24*365);
		setcookie('password', $_POST['password'], time()+60*60*24*365);

		do_login();
	}

	if( isset( $_POST['code'] ) )
	{
		$ch = curl_init();

		$post = array(
			'code'	=>	$_POST['code'],
		);

		curl_setopt( $ch, CURLOPT_URL, "http://m.vk.com/" . $_POST['url'] );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $post );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_COOKIEJAR, 'cookies.txt' );
		curl_setopt( $ch, CURLOPT_COOKIEFILE, 'cookies.txt' );
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_REFERER, $_POST['referer'] );

		curl_exec( $ch );
		curl_close( $ch );
	}

	if( isset( $_GET['search'] ) )
	{
		$search = $_GET['search'];

		$ch = curl_init();

		curl_setopt( $ch, CURLOPT_URL, "http://m.vk.com/audio?act=search&q=" . urlencode( $search ) );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_COOKIEJAR, 'cookies.txt' );
		curl_setopt( $ch, CURLOPT_COOKIEFILE, 'cookies.txt' );

		$res = curl_exec( $ch );
		curl_close( $ch );

		if( strpos( $res, 'action="https://login.vk.com/?act=login' ) !== false || strpos( $res, 'action="/login.php?act=security_check' ) !== false )
		{
			do_login();
			die( 'Search failed: Not logged in.' );
		}

		$end = str_replace( "\n", "", strstr( strstr( $res, '<div class="audios_wrap audios_list"' ), '<div class="show_more_wrap">', true ) );
		$songs = array(  );
		$check_br = true;
		function add_song( $matches )
		{
			global $songs, $check_br;
			if( !$matches[8] ) return;

			$song = array(
				'title'	=>	$matches[6],
				'artist'=>	$matches[4],
				'dur'	=>	$matches[1] . ':' . $matches[2],
				'url'	=>	$matches[8],
			);

			if( $check_br )
			{
				$ch = curl_init( $matches[8] );
				curl_setopt( $ch, CURLOPT_NOBODY, true );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $ch, CURLOPT_HEADER, true );
				$result = curl_exec( $ch );
				curl_close( $ch );
				preg_match( '/Content-Length: (\d+)/', $result, $m );
				$song['bitrate'] = map_bitrate( ( 8 * $m[1] / 1000 ) / ( (int)$matches[1] * 60 + (int)$matches[2] ) );
			}

			$songs[] = $song;
		}

		preg_replace_callback( '#<span>([0-9]{1,2}):([0-9]{1,2})<\/span>(.*)<span class="artist">(.*)</span>(.*)<span class="title">(.*)</span>(.*)<input type="hidden" value="(.*)">#U', 'add_song', $end );
		?>
		<form method="get" action="index.php">
			Search: <input type="text" name="search" value="<?php echo htmlspecialchars( $search ) ?>" /> &nbsp; <input type="submit" value="Search" />
		</form>
		<table width="100%">
			<thead>
				<tr>
					<th>
						Title
					</th>
					<th>
						Artist
					</th>
					<th>
						Duration
					</th>
					<th>
						Bitrate
					</th>
					<th>
						Download
					</th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach( $songs as $song )
				{
				?>
					<tr>
						<td>
							<?php echo $song['title'] ?>
						</td>
						<td>
							<?php echo $song['artist'] ?>
						</td>
						<td>
							<?php echo $song['dur'] ?>
						</td>
						<td>
							<?php echo $song['bitrate'] ?>
						</td>
						<td>
							<a href="<?php echo $song['url'] ?>">download</a>
						</td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
		<?php
		print_r( $songs );
	}
	else
	{
	?>
	<form method="get" action="index.php">
		Search: <input type="text" name="search" value="" /> &nbsp; <input type="submit" value="Search" />
	</form>
	<?php
	}
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