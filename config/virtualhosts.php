<?php

return [
    'apache_vhosts_file' => env('APACHE_VHOSTS_FILE', 'C:/Apache24/conf/extra/httpd-vhosts.conf'),
    'apache_bin' => env('APACHE_BIN', 'C:/Apache24/bin/httpd.exe'),
    'apache_service' => env('APACHE_SERVICE', 'Apache2.4'),
    'hosts_file' => env('HOSTS_FILE', 'C:/Windows/System32/drivers/etc/hosts'),
    'mkcert_bin' => env('MKCERT_BIN', 'C:/mkcert/mkcert.exe'),
    'mkcert_dir' => env('MKCERT_DIR', 'C:/mkcert'),
    'default_document_root' => env('DEFAULT_DOCUMENT_ROOT', 'D:/www/'),
];
