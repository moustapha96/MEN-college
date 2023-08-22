<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var');

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
    ])
    ->setFinder($finder);
return PhpCsFixer\Config::create()
    ->setRules([
        'twig/whitespace_after_comma_in_array' => true,
        // Autres règles Twig ou PHP
    ]);