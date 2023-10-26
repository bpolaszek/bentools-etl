<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'global_namespace_import' => [
            'import_functions' => true,
            'import_constants' => true,
        ],
    ])
    ->setFinder($finder)
;
