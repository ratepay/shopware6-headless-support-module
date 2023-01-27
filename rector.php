<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector;
use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src/'
    ]);

    $rectorConfig->skip([
        \Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector::class,
    ]);

    // register a single rule
    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    $rectorConfig->importNames(true, false);

    // define sets of rules
    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        SetList::TYPE_DECLARATION_STRICT,
        SetList::TYPE_DECLARATION,
        SetList::PSR_4,
        SetList::CODING_STYLE,
        LevelSetList::UP_TO_PHP_74,
    ]);

    $rectorConfig->ruleWithConfiguration(ConsistentPregDelimiterRector::class, [
        ConsistentPregDelimiterRector::DELIMITER => '/',
    ]);
};
