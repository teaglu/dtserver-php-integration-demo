<?php

// The internal address of the dtserver host
$dtserverHost= '1.1.1.1';

// The port to connect to.  Since we're creating a dynamic port, this
// should be configured as a listener with "api":true so it expects the
// name of the resource before sending actual data.
$dtserverPort= '9200';

// The dynamic junction we're using.  Normally this would be made up on
// the fly and kept in a session variable or something.  Obviously two
// sessions can't use the same one.
$junction= 'label1';

try {
	// This is just a simple ZPL label
	$zpl=
		'^XA' .
		'^FX' .
		'^CFA,30' .
		'^FO50,50^FDHomer Simpson^FS' .
		'^FO50,90^FD742 Evergreen Terrace^FS' .
		'^FO50,170^FDSpringfield, OR 97403^FS' .
		'^FO50,220^BY3' .
		'^BZN,40,N,N^FD97403-0000^FS' .
		'^FO500,50^FD${DATE}^FS' .
		'^FO500,90^FD${TIME}^FS' .
		'^XZ';
	
	// Swap in inserts
	$now= time();
	$zpl= str_replace('${DATE}', strftime('%m/%d/%Y', $now), $zpl);
	$zpl= str_replace('${TIME}', strftime('%H:%I:%S', $now), $zpl);

	// The API isn't complicated - it's just the name of the junction we
	// want to use and a newline, then start sending data.
	$data= $junction . "\n" . $zpl;

	$fd= fsockopen($dtserverHost, $dtserverPort, $errno, $errstr, 30);
	if (!$fd) {
		throw new Exception("Failed to connect to dtserver: $errstr");
	}
	fwrite($fd, $data);
	fclose($fd);
	
	http_response_code(200);	
} catch (Exception $e) {
	http_response_code(500);
	print("Exception: " . $e->getMessage());
}
?>