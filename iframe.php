<?php

header('Cache-Control: no-store, no-cache, must-revalidate');

// base64-url uses a slightly different alphabet than plain base64.
// PHP doens't have a build-in, so use the regular encoder and then
// translate for the differences.
function base64url_encode(string $input) : string {
	return str_replace(
		['+', '/', '='],
		['-', '_', ''], base64_encode($input));
}

// Function to create a JSON Web Token applicable for passing
// authentication to dtserver.
function create_jwt(string $junction, string $presharedSecret) : string {
	$header= ['typ'=>'JWT', 'alg'=>'HS256'];
	$body= [ 'iat'=>time(), 'junction'=>$junction ];

	$b64Header= base64url_encode(json_encode($header));
	$b64Body= base64url_encode(json_encode($body));

	$signature= hash_hmac('sha256',
		$b64Header . '.' . $b64Body, $presharedSecret, true);

	$b64Signature = base64url_encode($signature);
	
	return $b64Header . '.' . $b64Body . '.' . $b64Signature;
}

// This is the dynamic junction we're going to create
$junction= 'label1';

// This is the pre-shared secret configured on the dtserver
$presharedSecret= 'abcdeLMNOP';

$jwt= create_jwt($junction, $presharedSecret);
?>
<html>
<body onload="document.forms[0].submit();">
	<form method="POST" action="https://print.mycompany.com/framed">
		<input type="hidden" name="junction" value="<?=htmlspecialchars($junction)?>"/>
		<input type="hidden" name="authentication" value="<?=htmlspecialchars($jwt)?>"/>
		<input type="hidden" name="dynamic" value="true"/>
	</form>
</body>
</html>
