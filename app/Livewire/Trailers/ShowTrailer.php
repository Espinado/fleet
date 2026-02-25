<?php

namespace App\Livewire\Trailers;

use Livewire\Component;
use App\Models\Trailer;

class ShowTrailer extends Component
{


    public Trailer $trailer;

    protected $listeners = ['deleteConfirmed' => 'deletetrailer'];

    public function deleteTrailer($id)
    {
          $trailer = Trailer::find($id);

        if ($trailer) {
            $trailer->delete();
            session()->flash('message', 'trailer deleted successfully!');
        }

        return redirect()->route('trailers.list');
    }

    public function mount(Trailer $trailer)
    {
        $this->trailer = $trailer;
    }

    public function render()
    {
        return view('livewire.trailers.show-trailer') ->layout('layouts.app', [
        'title' => 'Trailer info'
    ]);
    }

       public function destroy()
{
    if ($this->trailer) {
        $this->trailer->delete();

        // Можно сбросить поля формы, если остаёмся на этой странице
        $this->reset();

        // Сообщение пользователю
        session()->flash('success', 'Trailer deleted successfully.');

        // При желании — редирект на список водителей
        return redirect()->route('trailers.index');
    }

    session()->flash('error', 'Trailer not found.');
}
}
