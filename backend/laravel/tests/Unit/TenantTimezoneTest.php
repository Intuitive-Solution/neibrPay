<?php

namespace Tests\Unit;

use App\Support\TenantTimezone;
use PHPUnit\Framework\TestCase;

class TenantTimezoneTest extends TestCase
{
    public function test_normalizes_parenthetical_suffix(): void
    {
        $this->assertSame('America/New_York', TenantTimezone::normalize('America/New_York(EST)'));
    }

    public function test_empty_falls_back(): void
    {
        $this->assertSame('UTC', TenantTimezone::normalize(null));
        $this->assertSame('UTC', TenantTimezone::normalize(''));
    }

    public function test_invalid_falls_back(): void
    {
        $this->assertSame('UTC', TenantTimezone::normalize('Not/A/Zone'));
    }
}
