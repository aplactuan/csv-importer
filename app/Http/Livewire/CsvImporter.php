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

    public array $columnsToMap = [];

    public array $columnLabels = [];

    public array $requiredColumn = [];

    public $file;

    protected $listeners = [
        'toggle'
    ];

    public function mount()
    {
        $this->columnsToMap = collect($this->columnsToMap)
            ->mapWithKeys(fn ($column) => [$column => ''])
            ->toArray();
    }

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
        $reqColumnsRule = collect($this->requiredColumn)
            ->mapWithKeys(function($column) {
                return ['columnsToMap.' . $column => ['required']];
            })
            ->toArray();

        return array_merge($reqColumnsRule, [
            'file' => ['file', 'mimes:csv', 'max:51200']
        ]);
    }


    //watchers
    public function updatedFile()
    {
        $this->validateOnly('file');

        //read the csv
        $csv = $this->readCsv($this->file->getRealPath());

        $this->fileHeaders = $csv->getHeader();
    }

    public function import()
    {
        $this->validate();

        //create an import model
        $this->makeImport();
    }

    protected function makeImport()
    {
        auth()->user()->imports()->create([
            'model' => $this->model,
            'file_path' => $this->file->getRealPath(),
            'file_name' => $this->file->getClientOriginalName(),
            'total_rows' => 100
        ]);
    }

    protected function readCsv($filePath): Reader
    {
        $stream = fopen($filePath, 'r');
        $csv = Reader::createFromStream($stream);

        return $csv->setHeaderOffset(0);
    }

    public function validationAttributes()
    {
        return collect($this->requiredColumn)
            ->mapWithKeys(function ($column) {
                return ['columnsToMap.' . $column => strtolower($this->columnLabels[$column] ?? $column)];
            })
            ->toArray();
    }
}
