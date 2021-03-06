(cd doc-base; git pull)
(cd en;       git pull)
(cd pt_BR;    git pull)

php doc-base/configure.php --with-lang=pt_BR --enable-xml-details
php doc-base/scripts/revcheck.php pt_BR > revcheck.html
xdg-open revcheck.html

(cd pt_BR; git status)

