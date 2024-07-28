<?php

// TODO: Remove after complete the implementation.

use Phalcon\Volt\Compiler;

include 'vendor/autoload.php';

$compiler = new Compiler();

$source = '{{ str_replace("a", "b", "aabb") }}';
$actual = $compiler->compileString($source);
var_dump($actual);
