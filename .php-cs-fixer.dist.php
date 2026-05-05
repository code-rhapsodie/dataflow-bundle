<?php

$finder = PhpCsFixer\Finder::create()
    ->in('src')
    //->in('tests')
    ->files()->name('*.php');

$config = new PhpCsFixer\Config();
$config->setRules([
    '@Symfony' => true,
    '@Symfony:risky' => true,
    '@PHP8x2Migration:risky' => true,
    '@PSR12' => true,
    'array_syntax' => [
        'syntax' => 'short',
    ],
    'combine_consecutive_unsets' => true,
    'native_function_invocation' => [
        'include' => [
            '@compiler_optimized',
        ],
    ],
    'no_extra_blank_lines' => [
        'tokens' => [
            'break',
            'continue',
            'extra',
            'return',
            'throw',
            'use',
            'parenthesis_brace_block',
            'square_brace_block',
            'curly_brace_block',
        ],
    ],
    'ordered_class_elements' => true,
    'ordered_imports' => true,
    'yoda_style' => [
        'equal' => false,
        'identical' => false,
        'less_and_greater' => false,
        'always_move_variable' => false,
    ],
])
    ->setRiskyAllowed(true)
    ->setFinder(
        $finder
    );

return $config;
