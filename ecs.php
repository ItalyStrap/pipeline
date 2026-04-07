<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/ecs.php',
    ])
    ->withSkip([
        NotOperatorWithSuccessorSpaceFixer::class,
    ])
    ->withSets([
        SetList::COMMON,
        SetList::CLEAN_CODE,
        SetList::PSR_12,
    ]);
