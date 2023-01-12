<?php

namespace App\Http\Livewire;

use Livewire\Component;

class CsvImports extends Component
{
    public function getImportsProperty()
    {
        return auth()->user()->imports()->oldest()->get();
    }

    public function render()
    {
        return view('livewire.csv-imports');
    }
}
