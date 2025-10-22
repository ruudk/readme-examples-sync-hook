<?php

declare(strict_types=1);

use PhpCsFixer\Finder;
use Ticketswap\PhpCsFixerConfig\PhpCsFixerConfigFactory;
use Ticketswap\PhpCsFixerConfig\RuleSet\TicketSwapRuleSet;

$finder = Finder::create()
    ->in(__DIR__ . '/src')
    ->append([
        __DIR__ . '/bin/readme-examples-sync',
        __DIR__ . '/.php-cs-fixer.php',
    ]);

return PhpCsFixerConfigFactory::create(TicketSwapRuleSet::create())->setFinder($finder);
