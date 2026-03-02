<?php

namespace App\Livewire\Trailers;

use Livewire\Component;
use App\Models\Trailer;

class ShowTrailer extends Component
{


    public Trailer $trailer;

    public function mount(Trailer $trailer)
    {
        $this->trailer = $trailer;
    }

    public function render()
    {
        return view('livewire.trailers.show-trailer')
            ->layout('layouts.app', [
                'title' => __('app.trailer.show.title'),
            ]);
    }

    public function destroy()
    {
        if ($this->trailer) {
            $this->trailer->delete();

            session()->flash('success', __('app.trailer.show.deleted_success'));
            return redirect()->route('trailers.index');
        }

        session()->flash('error', __('app.trailer.show.deleted_error'));
        return redirect()->route('trailers.index');
    }
}
