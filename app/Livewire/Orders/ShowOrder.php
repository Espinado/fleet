<?php

namespace App\Livewire\Orders;

use App\Models\TransportOrder;
use Livewire\Component;

class ShowOrder extends Component
{
    public TransportOrder $transportOrder;

    public function mount(TransportOrder $transportOrder): void
    {
        $this->transportOrder = $transportOrder->load([
            'expeditor:id,name,reg_nr',
            'customer:id,company_name',
            'steps',
            'cargos.customer:id,company_name',
            'cargos.shipper:id,company_name',
            'cargos.consignee:id,company_name',
            'trip:id',
        ]);
    }

    public function render()
    {
        return view('livewire.orders.show-order')
            ->layout('layouts.app', [
                'title' => $this->transportOrder->number . ' — ' . __('app.orders.show.title'),
            ]);
    }
}
