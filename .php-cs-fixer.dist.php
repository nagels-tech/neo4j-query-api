<?php declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setRules([
        '@PSR12' => true,
    ])
    ->setFinder(
        Finder::create()
            ->in(__DIR__)
            ->exclude([
                'vendor',
            ])
    );