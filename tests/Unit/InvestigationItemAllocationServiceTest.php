<?php

namespace Tests\Unit;

use App\Services\Billing\InvestigationItemAllocationService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class InvestigationItemAllocationServiceTest extends TestCase
{
    public function test_split_keeps_the_exact_total(): void
    {
        $service = new InvestigationItemAllocationService();
        $method = new \ReflectionMethod($service, 'split');

        $amounts = $method->invoke($service, new Collection([
            (object) ['ID' => 1, 'Amount' => 333],
            (object) ['ID' => 2, 'Amount' => 333],
            (object) ['ID' => 3, 'Amount' => 334],
        ]), 500, 'Amount');

        $this->assertSame(500.0, array_sum($amounts));
    }
}
