<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PHP82Migration' => true,
        'array_syntax' => ['syntax' => 'short'],
        'yoda_style' => false,
        'class_attributes_separation' => [
            'elements' => ['method' => 'one', 'property' => 'one', 'trait_import' => 'one'],
        ],
        'no_useless_concat_operator' => true,
        'no_useless_return' => true,
    ])
    ->setFinder($finder)
;
