<?php

require './Diff.php';

$f  = $_GET['f'];
$h1 = $_GET['h1'];
$h2 = $_GET['h2'];
$m = isset( $_GET['mode'] ) ? $_GET['mode'] : 'text';

$url1 = "http://git.php.net/?p=doc/en.git;a=blob_plain;f=$f;hb=$h1";
$url2 = "http://git.php.net/?p=doc/en.git;a=blob_plain;f=$f;hb=$h2";

$contents1 = file_get_contents( $url1 );
$contents2 = file_get_contents( $url2 );

$diff = Diff::compare( $contents1 , $contents2 );

if ( $m == 'html' )
{
    header( "Content-Type: text/html" );
    print <<<HTML
<html>
<head>
<style>
.diff td { vertical-align: top; white-space: pre; white-space: pre-wrap; font-family: monospace; }
.diffDeleted { border: 1px solid red; background: MistyRose; }
.diffInserted { border: 1px solid green; background: HoneyDew; }
.diffUnmodified { color: DimGray; }
</style>
</head>
<body>
HTML;
    print Diff::toTable( $diff );
    print '</body></html>';
}
else
{
    header( "Content-Type: text/plain" );
    print Diff::toString( $diff );
}

