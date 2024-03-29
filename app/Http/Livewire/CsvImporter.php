<?php

namespace App\Http\Livewire;

use App\Helpers\ChunkIterator;
use App\Jobs\ImportCsv;
use Illuminate\Support\Facades\Bus;
use League\Csv\Reader;
use League\Csv\Statement;
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

    public int $fileRowCount = 0;

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
        $csv = $this->readCsv;

        $this->fileHeaders = $csv->getHeader();

        $this->fileRowCount = count($this->csvRecords);

        $this->resetValidation();
    }

    public function getReadCsvProperty(): Reader
    {
        return $this->readCsv($this->file->getRealPath());
    }

    public function getCsvRecordsProperty()
    {
        return Statement::create()->process($this->readCsv);
    }

    public function import()
    {
        $this->validate();

        $import = $this->makeImport();
        $batches = collect(
            (new ChunkIterator($this->csvRecords->getRecords(), 50))
                ->get()
        )->map(function ($chunk) use ($import) {
                return new ImportCsv($import, $this->model, $chunk, $this->columnsToMap);
            })->toArray();

        Bus::batch($batches)
            ->finally(function () use ($import) {
                $import->touch('completed_at');
            })
            ->dispatch();

        $this->resetExcept(['model', 'open', 'columnsToMap', 'columnLabels', 'requiredColumn']);

        $this->emitTo('csv-imports', 'imports.refresh');
    }

    protected function makeImport()
    {
        return auth()->user()->imports()->create([
            'model' => $this->model,
            'file_path' => $this->file->getRealPath(),
            'file_name' => $this->file->getClientOriginalName(),
            'total_rows' => count($this->csvRecords)
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
