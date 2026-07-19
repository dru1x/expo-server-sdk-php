<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setRules([
        '@auto' => true,
        '@PER-CS' => true
    ])
    ->setFinder(
        (new Finder())
            ->in(__DIR__)
    )
;
