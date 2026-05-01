<?php

namespace App\Filters;

use App\Models\Contract;
use Illuminate\Database\Eloquent\Builder;

class ContractFilters extends QueryFilters
{
    protected string $entityType = Contract::class;

    public function status(string $value): Builder
    {
        return $this->builder->where('status', $value);
    }

    public function client_id(string $value): Builder
    {
        return $this->builder->where('client_id', $value);
    }

    public function number(string $value): Builder
    {
        return $this->builder->where('contract_number', 'like', '%' . $value . '%');
    }

    public function title(string $value): Builder
    {
        return $this->builder->where('title', 'like', '%' . $value . '%');
    }

    public function expires_after(string $value): Builder
    {
        return $this->builder->where('expires_at', '>=', $value);
    }

    public function expires_before(string $value): Builder
    {
        return $this->builder->where('expires_at', '<=', $value);
    }

    public function created_by(string $value): Builder
    {
        return $this->builder->where('user_id', $value);
    }

    public function invoice_id(string $value): Builder
    {
        return $this->builder->where('invoice_id', $value);
    }

    public function quote_id(string $value): Builder
    {
        return $this->builder->where('quote_id', $value);
    }

    public function signed_after(string $value): Builder
    {
        return $this->builder->where('signed_at', '>=', $value);
    }
}
