<?php
header( "Content-Type: text/html" );

foreach ( $diff as &$d )
{
    if ( is_array( $d ) )
    {
        if ( count( $d['d'] ) == 0 && count( $d['i'] ) == 0 )
            continue;
        $del = str_split( implode( "\n" , $d['d'] ) );
        $ins = str_split( implode( "\n" , $d['i'] ) );
        $char_diff = diff( $del , $ins );
        $d['dh'] = charDiffToHtmlLines( $char_diff , 'd' );
        $d['ih'] = charDiffToHtmlLines( $char_diff , 'i' );
        //var_dump( $d['dh'] );
        //var_dump( $d['ih'] );
    }
}

function charDiffToHtmlLines( $diff , $side )
{
    $buff = '';
    $mode = 'u';
    $ret = [];
    foreach( $diff as $char )
    {
        if ( is_array( $char ) )
        {
            foreach ( $char[$side] as $c )
            {
                charDiffToHtmlLinesPush( $buff , $mode , $ret , $c , $side );
            }
            continue;
        }
        charDiffToHtmlLinesPush( $buff , $mode , $ret , $char , 'u' );
    }
    charDiffToHtmlLinesPush( $buff , $mode , $ret , null , 'u' );
    return $ret;
}

function charDiffToHtmlLinesPush( & $buff , & $mode , & $ret , $char , $char_mode )
{
    // Close
    if ( $mode != $char_mode || $char == '\n' || $char == null )
    {
        if ( $mode == 'd' )
            $buff .= "</del>";
        if ( $mode == 'i' )
            $buff .= "</ins>";
    }
    // Append
    if ( $char == "\n" || $char == null )
    {
        $ret[] = $buff;
        $buff = '';
        $mode = 'u';
        return;
    }
    // Open
    if ( $mode != $char_mode )
    {
        if ( $char_mode == 'd' )
            $buff .= "<del>";
        if ( $char_mode == 'i' )
            $buff .= "<ins>";
    }
    if ( $char == '<' )
        $char = '&lt;';
    $buff .= $char;
    $mode = $char_mode;
}

$filename = htmlspecialchars( $f );

print <<<HTML
<html>
<head>
<style>
table { border-spacing: 1px; line-height: 1.2rem; }
td { vertical-align: top; white-space: pre; white-space: pre-wrap; font-family: monospace;; }
ins { background: #acf2bd; text-decoration: none; }
del { background: #fdb8c0; text-decoration: none; }
.n { text-align: right; padding: 0 1em; color: gray; }
.dt { background: #ffeef0; border: 1px solid red; background: MistyRose; }
.it { background: #ffeef0; border: 1px solid green; background: HoneyDew; }
.ut { color: DimGray; }
.diffDeleted { border: 1px solid red; background: MistyRose; }
.diffInserted { border: 1px solid green; background: HoneyDew; }
.diffUnmodified { color: DimGray; }

</style>
</head>
<body>
<table border="0">
<tr><td>&nbsp;</td></tr>
<tr><td></td><td>$filename</td></tr>
<tr><td></td><td>$h1</td><td></td><td>$h2</td></tr>
<tr><td>&nbsp;</td></tr>
HTML;

$lcount = 0;
$rcount = 0;
foreach ( $diff as $d )
{
    if ( is_array( $d ) )
    {
        $dcount = count( $d['d'] );
        $icount = count( $d['i'] );
        if ( $dcount == 0 && $icount == 0 )
            continue;
        print "<tr>";
        if ( $dcount == 0 )
        {
            print "<td></td><td></td>";
        }
        else
        {
            // 1
            print "<td class='n'>";
            $br = false;
            for ( $i = 0 ; $i < $dcount ; $i++ )
            {
                if ( $br )
                    print "<br/>";
                $br = true;
                print $lcount++;
            }
            print "</td>";
            // 2
            print "<td class='dt'>";
            $br = false;
            for ( $i = 0 ; $i < $dcount ; $i++ )
            {
                if ( $br )
                    print "<br/>";
                $br = true;
                if ( $mode == 'html2' )
                    print $d['dh'][$i];
                else
                    print htmlspecialchars( $d['d'][$i] );
            }
            print "</td>";
        }
        if ( $icount == 0 )
        {
            print "<td></td><td></td>";
        }
        else
        {
            // 3
            print "<td class='n'>";
            $br = false;
            for ( $i = 0 ; $i < $icount ; $i++ )
            {
                if ( $br )
                    print "<br/>";
                $br = true;
                print $rcount++;
            }
            print "</td>";
            // 4
            print "<td class='it'>";
            $br = false;
            for ( $i = 0 ; $i < $icount ; $i++ )
            {
                if ( $br )
                    print "<br/>";
                $br = true;
                if ( $mode == 'html2' )
                    print $d['ih'][$i];
                else
                    print htmlspecialchars( $d['i'][$i] );
            }
            print "</td>";
        }
        print "</tr>";
    }
    else
    {
        print "<tr>";
        // 1
        print "<td class='n'>";
        print $lcount++;
        print "</td>";
        // 2
        print "<td class='ut'>";
        print htmlspecialchars( $d );
        print "</td>";
        // 3
        print "<td class='n'>";
        print $rcount++;
        print "</td>";
        // 4
        print "<td class='ut'>";
        print htmlspecialchars( $d );
        print "</td>";
        print "</tr>";
    }
}

print '<table></body></html>';
