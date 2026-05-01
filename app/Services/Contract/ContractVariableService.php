<?php

namespace App\Services\Contract;

use App\Models\Client;
use App\Models\Company;
use App\Models\Contract;

class ContractVariableService
{
    private array $variables = [];

    public function render(string $content, Contract $contract): string
    {
        $this->buildVariables($contract);
        return $this->replaceVariables($content);
    }

    private function buildVariables(Contract $contract): void
    {
        $company = $contract->company;
        $client  = $contract->client;
        $contact = $contract->contact;
        $invoice = $contract->invoice;
        $quote   = $contract->quote;
        $project = $contract->project;
        $user    = $contract->user;

        // Company
        $settings = $company->settings ?? new \stdClass();
        $this->variables['company.name']       = $company->present()->name() ?? '';
        $this->variables['company.email']      = $settings->email ?? '';
        $this->variables['company.phone']      = $settings->phone ?? '';
        $this->variables['company.address']    = $this->formatCompanyAddress($settings);
        $this->variables['company.website']    = $settings->website ?? '';
        $this->variables['company.vat_number'] = $settings->vat_number ?? '';
        $this->variables['company.id_number']  = $settings->id_number ?? '';

        // Client
        if ($client) {
            $this->variables['client.name']        = $client->present()->name() ?? '';
            $this->variables['client.email']       = $client->present()->email() ?? '';
            $this->variables['client.phone']       = $client->phone ?? '';
            $this->variables['client.address']     = $this->formatClientAddress($client);
            $this->variables['client.city']        = $client->city ?? '';
            $this->variables['client.state']       = $client->state ?? '';
            $this->variables['client.postal_code'] = $client->postal_code ?? '';
            $this->variables['client.country']     = $client->country?->name ?? '';
            $this->variables['client.vat_number']  = $client->vat_number ?? '';
            $this->variables['client.id_number']   = $client->id_number ?? '';
            $this->variables['client.website']     = $client->website ?? '';
            $terms = $client->payment_terms ? $client->payment_terms . ' days' : ($settings->payment_terms ?? '');
            $this->variables['payment.terms']      = $terms;
        }

        // Contact
        if ($contact) {
            $this->variables['contact.first_name'] = $contact->first_name ?? '';
            $this->variables['contact.last_name']  = $contact->last_name ?? '';
            $this->variables['contact.full_name']  = trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? ''));
            $this->variables['contact.email']      = $contact->email ?? '';
            $this->variables['contact.phone']      = $contact->phone ?? '';
        }

        // Invoice
        if ($invoice) {
            $this->variables['invoice.number']     = $invoice->number ?? '';
            $this->variables['invoice.total']      = number_format((float)($invoice->amount ?? 0), 2);
            $this->variables['invoice.balance']    = number_format((float)($invoice->balance ?? 0), 2);
            $this->variables['invoice.due_date']   = $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('F j, Y') : '';
            $this->variables['invoice.date']       = $invoice->date ? \Carbon\Carbon::parse($invoice->date)->format('F j, Y') : '';
            $this->variables['invoice.po_number']  = $invoice->po_number ?? '';
        }

        // Quote
        if ($quote) {
            $this->variables['quote.number']      = $quote->number ?? '';
            $this->variables['quote.total']       = number_format((float)($quote->amount ?? 0), 2);
            $this->variables['quote.date']        = $quote->date ? \Carbon\Carbon::parse($quote->date)->format('F j, Y') : '';
            $this->variables['quote.valid_until'] = $quote->due_date ? \Carbon\Carbon::parse($quote->due_date)->format('F j, Y') : '';
        }

        // Project
        if ($project) {
            $this->variables['project.name']        = $project->name ?? '';
            $this->variables['project.due_date']    = $project->due_date ? \Carbon\Carbon::parse($project->due_date)->format('F j, Y') : '';
            $this->variables['project.description'] = $project->description ?? '';
        }

        // User / sales rep
        if ($user) {
            $this->variables['user.first_name'] = $user->first_name ?? '';
            $this->variables['user.last_name']  = $user->last_name ?? '';
            $this->variables['user.full_name']  = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
            $this->variables['user.email']      = $user->email ?? '';
            $this->variables['user.phone']      = $user->phone ?? '';
        }

        // Contract
        $this->variables['contract.title']      = $contract->title ?? '';
        $this->variables['contract.number']     = $contract->contract_number ?? '';
        $this->variables['contract.start_date'] = $contract->created_at ? $contract->created_at->format('F j, Y') : now()->format('F j, Y');
        $this->variables['contract.end_date']   = $contract->expires_at ? $contract->expires_at->format('F j, Y') : '';
        $this->variables['contract.date']       = now()->format('F j, Y');
        $this->variables['contract.amount']     = $invoice ? number_format((float)($invoice->amount ?? 0), 2) : ($quote ? number_format((float)($quote->amount ?? 0), 2) : '');

        // Line items
        $lineItems = null;
        $entity    = null;
        if ($invoice && $invoice->line_items) {
            $lineItems = $invoice->line_items;
            $entity    = $invoice;
        } elseif ($quote && $quote->line_items) {
            $lineItems = $quote->line_items;
            $entity    = $quote;
        }
        if ($lineItems) {
            $this->variables['line_items.table'] = $this->renderLineItemsTable((array) $lineItems);
        }
    }

    private function replaceVariables(string $content): string
    {
        foreach ($this->variables as $key => $value) {
            $safe = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
            $content = str_replace('{{' . $key . '}}', $safe, $content);
        }
        return $content;
    }

    private function formatCompanyAddress(\stdClass $settings): string
    {
        $parts = array_filter([
            $settings->address1 ?? null,
            $settings->address2 ?? null,
            $settings->city ?? null,
            $settings->state ?? null,
            $settings->postal_code ?? null,
        ]);
        return implode(', ', $parts);
    }

    private function formatClientAddress(Client $client): string
    {
        $parts = array_filter([
            $client->address1,
            $client->address2,
            $client->city,
            $client->state,
            $client->postal_code,
        ]);
        return implode(', ', $parts);
    }

    private function renderLineItemsTable(array $lineItems): string
    {
        $html  = '<table class="line-items-table" style="width:100%;border-collapse:collapse;margin:12px 0;">';
        $html .= '<thead><tr style="background:#f5f5f5;">';
        $html .= '<th style="border:1px solid #ddd;padding:8px;text-align:left;">Item</th>';
        $html .= '<th style="border:1px solid #ddd;padding:8px;text-align:left;">Description</th>';
        $html .= '<th style="border:1px solid #ddd;padding:8px;text-align:right;">Qty</th>';
        $html .= '<th style="border:1px solid #ddd;padding:8px;text-align:right;">Unit Price</th>';
        $html .= '<th style="border:1px solid #ddd;padding:8px;text-align:right;">Total</th>';
        $html .= '</tr></thead><tbody>';

        $grandTotal = 0;
        foreach ($lineItems as $item) {
            $item       = (array) $item;
            $qty        = (float) ($item['quantity'] ?? 1);
            $price      = (float) ($item['unit_cost'] ?? 0);
            $lineTotal  = $qty * $price;
            $grandTotal += $lineTotal;

            $html .= '<tr>';
            $html .= '<td style="border:1px solid #ddd;padding:8px;">' . htmlspecialchars($item['product_key'] ?? '', ENT_QUOTES) . '</td>';
            $html .= '<td style="border:1px solid #ddd;padding:8px;">' . htmlspecialchars($item['notes'] ?? '', ENT_QUOTES) . '</td>';
            $html .= '<td style="border:1px solid #ddd;padding:8px;text-align:right;">' . number_format($qty, 2) . '</td>';
            $html .= '<td style="border:1px solid #ddd;padding:8px;text-align:right;">' . number_format($price, 2) . '</td>';
            $html .= '<td style="border:1px solid #ddd;padding:8px;text-align:right;">' . number_format($lineTotal, 2) . '</td>';
            $html .= '</tr>';
        }

        $html .= '<tr style="font-weight:bold;background:#f9f9f9;">';
        $html .= '<td colspan="4" style="border:1px solid #ddd;padding:8px;text-align:right;">Total</td>';
        $html .= '<td style="border:1px solid #ddd;padding:8px;text-align:right;">' . number_format($grandTotal, 2) . '</td>';
        $html .= '</tr>';
        $html .= '</tbody></table>';

        return $html;
    }

    public function getAvailableVariables(): array
    {
        return [
            'Company'    => ['company.name', 'company.email', 'company.phone', 'company.address', 'company.website', 'company.vat_number', 'company.id_number'],
            'Client'     => ['client.name', 'client.email', 'client.phone', 'client.address', 'client.city', 'client.state', 'client.postal_code', 'client.country', 'client.vat_number', 'client.id_number'],
            'Contact'    => ['contact.first_name', 'contact.last_name', 'contact.full_name', 'contact.email', 'contact.phone'],
            'Invoice'    => ['invoice.number', 'invoice.total', 'invoice.balance', 'invoice.due_date', 'invoice.date', 'invoice.po_number'],
            'Quote'      => ['quote.number', 'quote.total', 'quote.date', 'quote.valid_until'],
            'Project'    => ['project.name', 'project.due_date', 'project.description'],
            'User'       => ['user.first_name', 'user.last_name', 'user.full_name', 'user.email', 'user.phone'],
            'Contract'   => ['contract.title', 'contract.number', 'contract.amount', 'contract.start_date', 'contract.end_date', 'contract.date'],
            'Payment'    => ['payment.terms'],
            'Line Items' => ['line_items.table'],
        ];
    }
}
