#!/usr/bin/env php
<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Jcupitt\Vips;

function printMetadata($im)
{
    foreach ($im->getFields() as &$name) {
        $value = $im->get($name);
        if (str_ends_with($name, "-data")) {
            $len = strlen($value);
            $value = "<$len bytes of binary data>";
        }
        echo "   $name: $value\n";
    }
}

$im = Vips\Image::newFromFile($argv[1]);
printMetadata($im);

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: expandtab sw=4 ts=4 fdm=marker
 * vim<600: expandtab sw=4 ts=4
 */
