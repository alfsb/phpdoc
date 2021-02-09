<?php

error_reporting( E_ALL) ;
ini_set( 'display_errors' , '1' );

require './simplediff.php';

$f  = $_GET['f'];
$h1 = $_GET['h1'];
$h2 = $_GET['h2'];
$mode = isset( $_GET['mode'] ) ? $_GET['mode'] : 'text';

/*
$f='appendices/about.xml';
$h1='86e6094e86b84a51d00ab217ac50ce8dde33d82a';
$h2='bcd51bdf1d04d8b18ed058f8de03cfa2a4c2fb9b';
$mode = "html";

$f='appendices/debugger.xml';
$h1='56591e6754bc29c9cc36b5c901a47699f7bb421b';
$h2='027f187b3ed8cdec60db6f83baf8f62b536d1101';
$mode = "html";
*/


$old = "http://git.php.net/?p=doc/en.git;a=blob_plain;f=$f;hb=$h1";
$new = "http://git.php.net/?p=doc/en.git;a=blob_plain;f=$f;hb=$h2";

$old = file_get_contents( $old );
$new = file_get_contents( $new );

$old = newlineNormalizeExplode( $old );
$new = newlineNormalizeExplode( $new );
$diff = diff( $old , $new );

unset( $old );
unset( $new );

switch ( $mode )
{
    case "html":
    case "html2":
        print_html( $diff , $mode );
        break;
    default:
        include "revcheckdifftext.php";
        break;
}

exit;

function newlineNormalizeExplode( $text )
{
    $text = str_replace( "\r\n" , "\n" , $text );
    $text = str_replace( "\r" , "\n" , $text );
    return explode( "\n" , $text );
}

function arrayToHtmlLines( $array )
{
    $ret = [];
    foreach( $array as $line )
        $ret[] = htmlspecialchars( $line );
    return $ret;
}

function charDiffToHtml( $diff , $side )
{
    $text = '';
    foreach( $diff as $char )
    {
        if ( is_array( $char ) )
        {
            if ( count( $char[$side] ) == 0 )
                continue;
            $tag = $side == 'd' ? 'del' : 'ins';
            $body = htmlspecialchars( implode( '', $char[$side] ) );
            $text .= "<$tag>$body</$tag>";
        }
        else
            $text .= htmlspecialchars( $char );
    }
    $text = explode( "\n" , $text );
    foreach ( $text as & $line )
    {
        preg_match_all( "/<(.*?)>/" , $line , $tags ); // /<(.*?)>/  /<\[.+?\]>/
        $tags = $tags[ 0 ];
        if ( count( $tags ) > 0 )
        {
            $zero = 0;
            $last = count( $tags ) - 1;
            if ( $tags[ $zero ] == '</ins>' ) $line = "<ins>$line";
            if ( $tags[ $zero ] == '</del>' ) $line = "<del>$line";
            if ( $tags[ $last ] == '<ins>' ) $line = "$line</ins>";
            if ( $tags[ $last ] == '<del>' ) $line = "$line</del>";
        }
    }
    return $text;
}

function print_html( $diff , $mode )
{
    $lcount = 0;
    $rcount = 0;
    $newDiff = [];
    foreach( $diff as $block )
    {
        if ( is_array( $block ) )
        {
            $newBlock = [];
            $dcount = count( $block['d'] );
            $icount = count( $block['i'] );
            if ( $dcount == 0 && $icount == 0 )
                continue;
            if ( $dcount == 0 || $icount == 0 )
            {
                $newBlock['l'] = $lcount;
                $newBlock['r'] = $rcount;
                $newBlock['d'] = arrayToHtmlLines( $block['d'] );
                $newBlock['i'] = arrayToHtmlLines( $block['i'] );
            }
            else
            {
                $old = str_split( implode( "\n" , $block['d'] ) );
                $new = str_split( implode( "\n" , $block['i'] ) );
                $charDiff = diff( $old , $new );
                $newBlock['l'] = $lcount;
                $newBlock['r'] = $rcount;
                $newBlock['d'] = charDiffToHtml( $charDiff , 'd' );
                $newBlock['i'] = charDiffToHtml( $charDiff , 'i' );
            }
            $newDiff[] = $newBlock;
            $lcount += $dcount;
            $rcount += $icount;
        }
        else
        {
            $newBlock = [];
            $newBlock['l'] = $lcount;
            $newBlock['r'] = $rcount;
            $newBlock['u'] = htmlspecialchars( $block );
            $newDiff[] = $newBlock;
            $lcount++;
            $rcount++;
        }
    }
    global $f, $h1, $h2;
    $f = htmlspecialchars( $f );
    $h1 = htmlspecialchars( $h1 );
    $h2 = htmlspecialchars( $h2 );
    $diff = $newDiff;
    print <<<HTML
<html>
<head>
<style>
table { border-spacing: 1px; line-height: 1.2rem; }
td { vertical-align: top; white-space: pre; white-space: pre-wrap; font-family: monospace;; }
ins { background: #acf2bd; text-decoration: none; }
del { background: #fdb8c0; text-decoration: none; }
.n { text-align: right; padding: 0 1em; color: gray; }
.d { background: #ffeef0; border: 1px solid salmon; }
.i { background: #e6ffed; border: 1px solid darkseagreen; }
.u { color: DimGray; }
.diffUnmodified { color: DimGray; }

</style>
</head>
<body onload="hideShow();">
<script>
function hideShow()
{
    var tags = document.getElementsByTagName("tr");
    for (var i = 0 ; i < tags.length ; i++ )
    {
        if ( tags[i].classList.contains('hide') )
        {
            if ( tags[i].style.display == '' || tags[i].style.display == 'table-row' )
                tags[i].style.display = 'none';
            else
                tags[i].style.display = 'table-row';
        }
    }
}
</script>
<button onclick="hideShow()">Hide / Show unchandeg</button>
<table border="0">
<tr><td>&nbsp;</td></tr>
<tr><td></td><td>$f</td></tr>
<tr><td></td><td>$h1</td><td></td><td>$h2</td></tr>
<tr><td>&nbsp;</td></tr>\n
HTML;
    // Context marking
    $context = 0;
    for ( $index = 0 ; isset( $diff[$index] ) ; $index++ )
    {
        if ( isset( $diff[$index]['u'] ) )
        {
            if ( $context > 0 )
                $diff[$index]['c'] = true;
            $context--;
        }
        else
        {
            $context = 5;
            continue;
        }
    }
    $context = 0;
    for ( $index = count( $diff ) - 1 ; isset( $diff[$index] ) ; $index-- )
    {
        if ( isset( $diff[$index]['u'] ) )
        {
            if ( $context > 0 )
                $diff[$index]['c'] = true;
            $context--;
        }
        else
        {
            $context = 5;
            continue;
        }
    }
    // Table output
    $lcount = 0;
    $rcount = 0;
    foreach ( $diff as $block )
    {
        if ( isset( $block['u'] ) )
        {
            $h = isset( $block['c'] ) ? '' : 'hide';
            print "<tr class='$h'>";
            print "<td class='n'>{$block['l']}</td>";
            print "<td class='u'>{$block['u']}</td>";
            print "<td class='n'>{$block['r']}</td>";
            print "<td class='u'>{$block['u']}</td>";
            print "</tr>\n";
        }
        else
        {
            $dn = []; // del line
            $dt = []; // del text
            $in = []; // ins line
            $it = []; // ins text
            $dcount = count( $block['d'] );
            $icount = count( $block['i'] );
            $mcount = max( $dcount , $icount );
            for ( $shift = 0 ; $shift < $mcount ; $shift++ )
            {
                if ( $shift < $dcount )
                {
                    $dn[] = $block['l'] + $shift;
                    $dt[] = $block['d'][$shift];
                }
                else
                {
                    $dn[] = '';
                    $dt[] = '';
                }
                if ( $shift < $icount )
                {
                    $in[] = $block['r'] + $shift;
                    $it[] = $block['i'][$shift];
                }
                else
                {
                    $in[] = '';
                    $it[] = '';
                }
            }
            for ( $m = 0 ; $m < $mcount ; $m++ )
            {
                print "<tr>";
                if ( $dn[$m] == '' )
                {
                    print "<td></td>";
                    print "<td></td>";
                }
                else
                {
                    print "<td class='n'>{$dn[$m]}</td>";
                    print "<td class='d'>{$dt[$m]}</td>";
                }
                if ( $in[$m] == '' )
                {
                    print "<td></td>";
                    print "<td></td>";
                }
                else
                {
                    print "<td class='n'>{$in[$m]}</td>";
                    print "<td class='i'>{$it[$m]}</td>";
                }
                print "</tr>\n";
            }
        }
    }

    print '<table></body></html>';
}

