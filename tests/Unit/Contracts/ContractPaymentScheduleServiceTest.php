<?php

namespace Tests\Unit\Contracts;

use App\Models\Quote;
use App\Repositories\InvoiceRepository;
use Modules\Contracts\app\Models\Contract;
use Modules\Contracts\app\Models\ContractPaymentSchedule;
use Modules\Contracts\app\Services\ContractAuditService;
use Modules\Contracts\app\Services\ContractPaymentScheduleService;
use Tests\TestCase;

class ContractPaymentScheduleServiceTest extends TestCase
{
    public function test_it_resolves_percentage_amounts_from_linked_quote(): void
    {
        $contract = new Contract();
        $quote = new Quote();
        $quote->amount = 2000;
        $contract->setRelation('quote', $quote);

        $schedule = new ContractPaymentSchedule();
        $schedule->amount_type = 'percentage';
        $schedule->amount = 25;

        $service = new ContractPaymentScheduleService(
            $this->createMock(ContractAuditService::class),
            $this->createMock(InvoiceRepository::class),
        );

        $this->assertSame(500.0, $service->resolveInvoiceAmount($contract, $schedule));
    }

    public function test_it_resolves_fixed_amounts_without_conversion(): void
    {
        $contract = new Contract();
        $schedule = new ContractPaymentSchedule();
        $schedule->amount_type = 'fixed';
        $schedule->amount = 799.95;

        $service = new ContractPaymentScheduleService(
            $this->createMock(ContractAuditService::class),
            $this->createMock(InvoiceRepository::class),
        );

        $this->assertSame(799.95, $service->resolveInvoiceAmount($contract, $schedule));
    }
}
