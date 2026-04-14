<?php
header('Content-Type: text/plain; charset=UTF-8');

echo "WebsiteScan diagnostics\n";
echo "=======================\n";
echo 'PHP_VERSION=' . PHP_VERSION . "\n";
echo 'PHP_SAPI=' . PHP_SAPI . "\n";
echo 'DOCUMENT_ROOT=' . ($_SERVER['DOCUMENT_ROOT'] ?? '') . "\n";
echo 'SCRIPT_FILENAME=' . ($_SERVER['SCRIPT_FILENAME'] ?? '') . "\n";
echo 'REQUEST_URI=' . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
echo 'APP_ENV=' . (getenv('APP_ENV') ?: '') . "\n";
echo 'APP_DEBUG=' . (getenv('APP_DEBUG') ?: '') . "\n";
echo 'curl=' . (extension_loaded('curl') ? 'yes' : 'no') . "\n";
echo 'dom=' . (extension_loaded('dom') ? 'yes' : 'no') . "\n";
echo 'simplexml=' . (extension_loaded('simplexml') ? 'yes' : 'no') . "\n";
echo 'mbstring=' . (extension_loaded('mbstring') ? 'yes' : 'no') . "\n";
echo 'openssl=' . (extension_loaded('openssl') ? 'yes' : 'no') . "\n";
