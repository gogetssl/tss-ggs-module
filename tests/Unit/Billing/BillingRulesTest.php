<?php

namespace Tests\Unit\Billing;

use TSSGGSModule\App\Libs\Billing\BalanceMonitor;
use TSSGGSModule\App\Libs\Billing\FinancePolicy;
use TSSGGSModule\App\Libs\Billing\Markups;
use PHPUnit\Framework\TestCase;

/** Money rules that do not need the database */
class BillingRulesTest extends TestCase
{
    public function testMarkupIsAPercentageRoundedToCents(): void
    {
        $this->assertSame(110.0, Markups::apply(100, 10));
        $this->assertSame(33.33, Markups::apply(33.333, 0));
        $this->assertSame(90.0, Markups::apply(100, -10));
    }

    public function testBalanceErrorsAreRecognised(): void
    {
        foreach(['Insufficient funds', 'Not enough balance on the account', 'Your balance is too low', 'Please top up'] as $message)
        {
            $this->assertTrue(FinancePolicy::isBalanceError($message), $message);
        }
        $this->assertFalse(FinancePolicy::isBalanceError('Domain is not valid'));
    }

    public function testMoneyLeftByAccountTerms(): void
    {
        $this->assertSame(120.5, BalanceMonitor::left(['terms' => 'prepaid', 'balance' => '120.50']));
        $this->assertSame(150.0, BalanceMonitor::left(['terms' => 'CREDIT', 'balance' => -50, 'credit_limit' => 200]));
        $this->assertNull(BalanceMonitor::left(['terms' => 'credit', 'balance' => 10, 'credit_limit' => null]));
        $this->assertNull(BalanceMonitor::left(['terms' => 'invoice', 'balance' => 0]));
        $this->assertSame(0.0, BalanceMonitor::left([]));
    }

    public function testMoneyFormat(): void
    {
        $this->assertSame('1,234.50 USD', BalanceMonitor::money(1234.5, 'USD'));
        $this->assertSame('-3.00 EUR', BalanceMonitor::money('-3', 'EUR'));
    }
}
