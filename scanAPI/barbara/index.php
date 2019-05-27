<pre>
<?php

require_once "barcode.php";

$bc = new barcode;
$scanner = new BarScan;

$bc->load("barcode.js");
echo "Dictionary Loaded..";

$scanner->Codecs = $bc;

//Manually set code type
$scanner->CodeType = $bc->code39;

$img = new Imagick("test/code-25.gif");
echo "Image Loaded...";

echo "<br />Decoded: " . $scanner->Scan($img, 0, 20, 2048, 0);

?>
</pre>
