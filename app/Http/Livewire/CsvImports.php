<?php

namespace App\Http\Livewire;

use Livewire\Component;

class CsvImports extends Component
{
    public string $model;

    protected $listeners = [
        'imports.refresh' => '$refresh'
    ];

    public function getImportsProperty()
    {
        return auth()->user()->imports()->oldest()->notCompleted()->forModel($this->model)->get();
    }

    public function render()
    {
        return view('livewire.csv-imports');
    }
}
