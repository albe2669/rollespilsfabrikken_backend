<?php

$finder = (new PhpCsFixer\Finder)
    ->in(__DIR__)
    ->exclude([
        'vendor',
        'storage',
        '.direnv',
    ]);

return (new PhpCsFixer\Config)
    ->setRules([
        '@PER-CS2.0' => true,
        'new_with_parentheses' => false,
    ])
    ->setFinder($finder);
