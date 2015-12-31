<?php

session_start();


/*///////////////////////////////

		-------------------
		MAIN PROCESS (body)
		-------------------

///////////////////////////////*/

function htaccessMain()
{
	?>
	
	<!DOCTYPE html>
	<html>
	<head>
		<title>Create htaccess</title>
		<meta name="description" content=".htaccess and .htpassword generator" />
		<meta charset="UTF-8" />
	</head>
	<body>

	<?php
		$options = array('options'=>array('default'=>0, 'min_range'=>0, 'max_range'=>2));
		$STATE = filter_input( INPUT_GET, 'etape', FILTER_VALIDATE_INT, $options );
		switch ($STATE) {
			case 0:
				htaccessInit();
				break;
			case 1:
				htaccessUsers();
				break;
			case 2:
				htaccessDisplay();
				break;
		}
	?>

	</body>
	</html>
	
	<?php
	exit ();
}


/*///////////////////////////

		--------------
		STATE I (init)
		--------------

///////////////////////////*/

function htaccessInit()
{
	?>

	<h1>.htaccess and .htpasswd generator</h1>
	<h2>State 1/3</h2>
	
	<form method="post" action="?etape=1">
		Number of users (1<100)<br/>
		<input type="number" min="1" name="nb" max="100" value="1"/><br/>
		
		Home sentence<br/>
		<input type="text" name="mdp" size="50" value="Welcome"/><br/>
		
		Path of the .htpassword file<br/>
		<input type="text" name="path" size="64" value="<?= getcwd() ?>"/><br/>
		
		<input type="submit" value="Continuer" style="background-color:#e0e0e0;color:black;"/>
	</form>

	<?php
}


/*///////////////////////////

		---------------
		STATE II (user)
		---------------

///////////////////////////*/

function htaccessUsers()
{
	$options = array('options'=>array('default'=>1, 'min_range'=>1, 'max_range'=>100));
	$USER_NUMBER = filter_input( INPUT_POST, 'nb', FILTER_VALIDATE_INT, $options );
	$PATH = filter_input( INPUT_POST, 'path', FILTER_SANITIZE_URL );
	$TEXT = filter_input( INPUT_POST, 'mdp', FILTER_SANITIZE_STRING );
	$_SESSION['nb']=$USER_NUMBER;
	$_SESSION['sentence']=$TEXT;
	$_SESSION['path']=$PATH;
	
	?>

	<h1>.htaccess and .htpasswd generator</h1>
	<h2>State 2/3</h2>
	
	<form method="post" action="?etape=2">
	<?php
	$length = $USER_NUMBER + 1;
	for( $i = 1; $i < $length; $i++ ) { ?>
		User <?=$i?><br/>
		<table>
			<tr>
				<td>Nickname</td>
				<td><input type="text" name="pseudo<?=$i?>"><br/></td>
			</tr>
				<td>Password</td>
				<TD><input type="text" name="mdp<?=$i?>" value="<?=passGen(10)?>"><br/></td>
			</tr>
		</table>
		<hr/>
	<?php } ?>

	<input type="submit" value="Continuer" style="background-color:#e0e0e0;color:black;"/></form>


	<?php
}



/*//////////////////////////////

		-------------------
		STATE III (process)
		-------------------

//////////////////////////////*/

function htaccessDisplay()
{
	
	if( !isset($_SESSION['nb']) )
		$_SESSION['nb'] = 1;
	if( !isset($_SESSION['path']) )
		$_SESSION['path'] = getcwd();
	if( !isset($_SESSION['sentence']) )
		$_SESSION['sentence'] = 'Welcome';
	
	$options = array('options'=>array('default'=>1, 'min_range'=>1, 'max_range'=>100));
	$USER_NUMBER = filter_var( $_SESSION['nb'], FILTER_VALIDATE_INT, $options );
	$PATH = filter_var( $_SESSION['path'], FILTER_SANITIZE_URL );
	$TEXT = filter_var( $_SESSION['sentence'], FILTER_SANITIZE_STRING );
	$htaccess = 'AuthName "'.$TEXT.'"
AuthType Basic
AuthUserFile "'.$PATH.'/.htpasswd" 
Require valid-user';
	$htpasswd = '';
	$length = $USER_NUMBER + 1;
	for ($i = 1; $i < $length; $i++)
	{
		$pseudo = 'pseudo'.$i;
		$mdp = 'mdp'.$i;
		
		$USER = filter_input(INPUT_POST, $pseudo, FILTER_SANITIZE_STRING);
		$PASS = filter_input(INPUT_POST, $mdp, FILTER_SANITIZE_STRING);
		
		$crypto = '{SHA}'.base64_encode(sha1($PASS, true))."\r\n";
		$htpasswd .= $USER.':'.$crypto;
	}
	createFile(".htaccess", $htaccess);
	createFile($PATH.'/.htpasswd', $htpasswd);
			
?>

	<h1>.htaccess and .htpasswd generator</h1>
	<h2>State 3/3</h2>
	
	<em>.htaccess - content</em><br>
	<pre><?= $htaccess ?></pre><br>
	<hr>
	
	<br>
	<em>.htpasswd (<?=$PATH?>) - content</em><br>
	<pre><?= $htpasswd ?></pre><br>
	<hr>
	
	<button onclick="location.reload();">Ok</button>
	
<?php
	
	$path = $_SERVER['PHP_SELF']; 
	$file = basename ($path);
	unlink($file);
		
	
	session_destroy();
}


/*/////////////////////////

		----------
		FILE WRITE
		----------

/////////////////////////*/

function createFile($path, $content)
{
	$file = fopen($path, "w+");
	fputs($file, $content);
	fclose($file);
}


/*/////////////////////

		--------
		PASS GEN
		--------

/////////////////////*/

function passGen($length = 15)
{
	$pass = '';
	$range = '012346789abcdfghjkmnpqrtvwxyzABCDFGHJKLMNPQRTVWXYZ!?$%&*()_-+={[}]:;@#\<,>./';
 
	$max = strlen($range);
 
	if ($length > $max)
	{
		$length = $max;
	}
 
	$i = 0;
 	while ($i < $length)
	{
		$char = substr($range, mt_rand(0, $max - 1), 1);
 		if (!strstr($pass, $char))
		{
			$pass .= $char;
			$i++;
		}
	}
	
	return $pass;
}


htaccessMain();
