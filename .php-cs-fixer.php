<?php declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setRiskyAllowed(true) // Allow risky fixers
    ->setRules([
        '@PSR12' => true,
        'strict_param' => true, // This is a risky rule
    ])
    ->setFinder(
        Finder::create()
            ->in(__DIR__)
            ->exclude([
                'vendor',
            ])
    );
