<?php

$filename = __DIR__ . '/tests/fixtures/landing.zip';

if (!is_dir(dirname($filename))) {
    mkdir(dirname($filename), 0777, true);
}

// Minimal ZIP with index.html containing "Hello"
$hex = '504b03040a000000000000002100000000000000000000000a000000696e6465782e68746d6c48656c6c6f504b010214000a000000000000002100000000000000000000000a000000000000000000000000000000696e6465782e68746d6c504b0506000000000100010038000000300000000000';

file_put_contents($filename, hex2bin($hex));

echo "Created $filename using Hex\n";
