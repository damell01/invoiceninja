<?php

namespace Tests\Unit\Contracts;

use App\Models\Client;
use App\Models\Invoice;
use App\Services\Client\ClientService;
use Modules\Contracts\app\Models\Contract;
use Modules\Contracts\app\Models\ContractPaymentSchedule;
use Modules\Contracts\app\Services\ContractPaymentMethodService;
use Modules\Contracts\app\Services\ContractPaymentScheduleService;
use Tests\TestCase;

class ContractPaymentMethodServiceTest extends TestCase
{
    public function test_it_uses_invoice_ninja_client_payment_methods_for_contract_amount(): void
    {
        $expected = [['label' => 'Credit Card', 'gateway_type_id' => 1]];

        $client = $this->createMock(Client::class);
        $clientService = $this->createMock(ClientService::class);
        $clientService->expects($this->once())
            ->method('getPaymentMethods')
            ->with(125.0)
            ->willReturn($expected);
        $client->method('service')->willReturn($clientService);

        $invoice = new Invoice();
        $invoice->amount = 125;
        $invoice->balance = 0;

        $contract = new Contract();
        $contract->setRelation('client', $client);
        $contract->setRelation('invoice', $invoice);

        $scheduleService = $this->createMock(ContractPaymentScheduleService::class);
        $service = new ContractPaymentMethodService($scheduleService);

        $this->assertSame($expected, $service->availableMethodsForContract($contract));
    }

    public function test_it_uses_schedule_amount_for_schedule_payment_methods(): void
    {
        $expected = [['label' => 'ACH', 'gateway_type_id' => 2]];

        $client = $this->createMock(Client::class);
        $clientService = $this->createMock(ClientService::class);
        $clientService->expects($this->once())
            ->method('getPaymentMethods')
            ->with(250.0)
            ->willReturn($expected);
        $client->method('service')->willReturn($clientService);

        $contract = new Contract();
        $contract->setRelation('client', $client);

        $schedule = new ContractPaymentSchedule();
        $schedule->amount = 25;
        $schedule->amount_type = 'percentage';

        $scheduleService = $this->createMock(ContractPaymentScheduleService::class);
        $scheduleService->expects($this->once())
            ->method('resolveInvoiceAmount')
            ->with($contract, $schedule)
            ->willReturn(250.0);

        $service = new ContractPaymentMethodService($scheduleService);

        $this->assertSame($expected, $service->availableMethodsForSchedule($contract, $schedule));
    }
}
