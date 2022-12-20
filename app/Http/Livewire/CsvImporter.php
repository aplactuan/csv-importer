<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class CsvImporter extends Component
{
    use WithFileUploads;

    public bool $open = false;

    public string $model;

    public $file;

    protected $listeners = [
        'toggle'
    ];

    public function toggle()
    {
        $this->open = !$this->open;
    }

    public function render()
    {
        return view('livewire.csv-importer');
    }

    public function rules()
    {
        return [
            'file' => ['file', 'mimes:csv', 'max:51200']
        ];
    }


    //watchers
    public function updatedFile()
    {
        $this->validateOnly('file');

        //read the csv

        //grab the data from csv
    }
}
