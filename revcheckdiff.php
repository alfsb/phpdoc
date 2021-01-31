<?php

require './simplediff.php';

$f  = $_GET['f'];
$h1 = $_GET['h1'];
$h2 = $_GET['h2'];
$mode = isset( $_GET['mode'] ) ? $_GET['mode'] : 'text';

//$f='appendices/about.xml';
//$h1='bcd51bdf1d04d8b18ed058f8de03cfa2a4c2fb9b';
//$h2='86e6094e86b84a51d00ab217ac50ce8dde33d82a';
//$mode = "html";

$old = "http://git.php.net/?p=doc/en.git;a=blob_plain;f=$f;hb=$h1";
$new = "http://git.php.net/?p=doc/en.git;a=blob_plain;f=$f;hb=$h2";

$old = file_get_contents( $old );
$new = file_get_contents( $new );

$old = splitRaw( $old );
$new = splitRaw( $new );
$diff = diff( $old , $new );

unset( $old );
unset( $new );

switch ( $mode )
{
    case "html":
    case "html2":
        include "revcheckdiffhtml.php";
        break;
    default:
        include "revcheckdifftext.php";
        break;
}

exit;

function splitRaw( $text )
{
    if ( strpos( $text , "\r\n" ) !== false )
        return explode( "\r\n" , $text );
    if ( strpos( $text , "\n" ) !== false )
        return explode( "\n" , $text );
    return explode( "\r" , $text );
}
