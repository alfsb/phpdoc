#!/bin/bash

linha=$1

linha=${linha/'phpdoc/'/''}
linha=${linha/'/trunk/'/'/'}
linha=${linha/'./'/''}
linha=${linha/'en/'/''}
linha=${linha/'pt_BR/'/''}

en="en/${linha}";
br="pt_BR/${linha}";

#echo $en;
#echo $br;

gedit > /dev/null 2>&1 &
gedit $en $br

atom $en $br

# nohup atom $en $br &

