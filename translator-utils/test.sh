
rm patch.txt
wget "https://patch-diff.githubusercontent.com/raw/php/doc-pt_br/pull/$1.patch" -O patch.txt
(cd pt_BR; git apply ../patch.txt 2>&1 | tee ../merge.txt)

php doc-base/configure.php --with-lang=pt_BR --enable-xml-details
(cd pt_BR; git status)
(cd pt_BR; git diff)

echo "# Check the message bellow. Delete the dot line bellow to make a change." > messg.txt
echo "."                                                                        >> messg.txt
echo "Translations by @$2 - https://github.com/phpdocbrbridge/traducao/pull/$1" >> messg.txt
echo ""                                                                         >> messg.txt
echo "Closes GH-$1."                                                            >> messg.txt

echo "If OK, then:"
echo "    ./push.sh"
echo

