EC技研版の日本語版を使用するにあたって、以下の修正をMagentoインストールディレクトリのトップに配置されている .htaccess に追加する必要があります。

############################################
## mjv
    php_value mbstring.internal_encoding UTF-8
    php_value default_charset UTF-8
    php_value date.timezone Asia/Tokyo

.htaccess 以外の場所(php.ini など)で設定しても構いませんので、かならずこの設定がmagentoを稼動させているサーバに反映されるようにしてください。
