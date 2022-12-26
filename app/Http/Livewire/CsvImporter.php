<?php

namespace App\Http\Livewire;

use League\Csv\Reader;
use Livewire\Component;
use Livewire\WithFileUploads;

class CsvImporter extends Component
{
    use WithFileUploads;

    public bool $open = false;

    public string $model;

    public array $fileHeaders = [];

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
        $csv = $this->readCsv($this->file->getRealPath());

        $this->fileHeaders = $csv->getHeader();
    }

    protected function readCsv($filePath): Reader
    {
        $stream = fopen($filePath, 'r');
        $csv = Reader::createFromStream($stream);

        return $csv->setHeaderOffset(0);
    }
}
