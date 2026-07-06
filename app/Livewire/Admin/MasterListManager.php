<?php

namespace App\Livewire\Admin;

use App\Models\MasterListItem;
use App\Models\MasterItemLog;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MasterListManager extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $hardSync = false;
    
    // CSV Import status
    public $tempFilePath = '';
    public $totalRows = 0;
    public $previewRows = [];
    public $importedRowsCount = 0;
    public $importing = false;

    // Inline edit state
    public $editingItemId = null;
    public $editingField = null;
    public $editingValue = '';

    // CSV File Upload binding
    public $file;

    protected $paginationTheme = 'tailwind';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function startEdit($itemId, $field)
    {
        $item = MasterListItem::findOrFail($itemId);
        $this->editingItemId = $itemId;
        $this->editingField = $field;
        $this->editingValue = $item->$field;
    }

    public function cancelEdit()
    {
        $this->editingItemId = null;
        $this->editingField = null;
        $this->editingValue = '';
    }

    public function saveEdit()
    {
        $item = MasterListItem::findOrFail($this->editingItemId);
        $field = $this->editingField;
        
        $rules = [
            'tipe_mesin' => 'nullable|string',
            'standart_packaging_list' => 'nullable|integer|min:0',
            'setup_time_minute' => 'nullable|integer|min:0',
            'pair' => 'nullable|integer|min:0',
            'cavity' => 'nullable|integer|min:0',
            'cycle_time' => 'nullable|integer|min:0',
        ];

        $validated = $this->validate([
            'editingValue' => $rules[$field] ?? 'nullable'
        ]);

        $oldValue = $item->$field;
        $newValue = $this->editingValue;

        if ($oldValue != $newValue) {
            $item->$field = $newValue;
            $item->save();

            // Log the change
            MasterItemLog::create([
                'user_id' => auth()->id(),
                'item_code' => $item->item_code,
                'action' => 'inline_edit',
                'old_values' => [$field => $oldValue],
                'new_values' => [$field => $newValue],
            ]);

            session()->flash('message', "Item {$item->item_code} updated successfully.");
        }

        $this->cancelEdit();
    }

    public function updatedFile()
    {
        $this->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB limit
        ]);

        $path = $this->file->store('temp');
        $this->tempFilePath = Storage::path($path);

        // Read headers and count total rows
        $file = fopen($this->tempFilePath, 'r');
        if (!$file) {
            session()->flash('error', 'Failed to open the uploaded file.');
            return;
        }

        // Detect separator (usually semicolon or comma)
        $firstLine = fgets($file);
        $separator = strpos($firstLine, ';') !== false ? ';' : ',';
        rewind($file);

        // Fetch headers
        $headers = fgetcsv($file, 0, $separator);

        $this->totalRows = 0;
        $this->previewRows = [];
        
        while (($row = fgetcsv($file, 0, $separator)) !== false) {
            $this->totalRows++;
            if (count($this->previewRows) < 5) {
                // Map columns safely
                $mappedRow = [];
                foreach ($headers as $index => $header) {
                    $cleanHeader = trim(str_replace('"', '', $header));
                    $mappedRow[$cleanHeader] = $row[$index] ?? '';
                }
                $this->previewRows[] = $mappedRow;
            }
        }
        fclose($file);
    }

    public function startImport()
    {
        $this->importedRowsCount = 0;
        $this->importing = true;
    }

    public function importChunk()
    {
        if (!$this->tempFilePath || !file_exists($this->tempFilePath)) {
            $this->importing = false;
            return;
        }

        $file = fopen($this->tempFilePath, 'r');
        $firstLine = fgets($file);
        $separator = strpos($firstLine, ';') !== false ? ';' : ',';
        rewind($file);

        // Headers
        $headers = fgetcsv($file, 0, $separator);
        // Clean headers
        $headers = array_map(function($h) {
            return trim(str_replace('"', '', $h));
        }, $headers);

        // Skip to offset
        $offset = $this->importedRowsCount;
        for ($i = 0; $i < $offset; $i++) {
            fgetcsv($file, 0, $separator);
        }

        $chunkSize = 500;
        $processed = 0;

        DB::transaction(function () use ($file, $headers, $separator, $chunkSize, &$processed) {
            while ($processed < $chunkSize && ($row = fgetcsv($file, 0, $separator)) !== false) {
                $data = [];
                foreach ($headers as $index => $header) {
                    $val = isset($row[$index]) ? trim(str_replace('"', '', $row[$index])) : null;
                    $data[$header] = $val;
                }

                if (empty($data['item_code'])) {
                    $processed++;
                    continue;
                }

                $item = MasterListItem::where('item_code', $data['item_code'])->first();

                if ($item) {
                    // Update item (Upsert)
                    $oldValues = $item->only(['item_name', 'tipe_mesin', 'standart_packaging_list', 'setup_time_minute', 'pair', 'cavity', 'customer_code', 'cycle_time']);
                    
                    // SAP Owned fields are always updated
                    $item->item_name = $data['item_name'] ?? $item->item_name;
                    $item->customer_code = $data['customer_code'] ?? $item->customer_code;

                    // MES Owned fields are only updated if hardSync is enabled
                    if ($this->hardSync) {
                        $item->tipe_mesin = isset($data['tipe_mesin']) ? $data['tipe_mesin'] : $item->tipe_mesin;
                        $item->standart_packaging_list = isset($data['standart_packaging_list']) ? (int)$data['standart_packaging_list'] : $item->standart_packaging_list;
                        $item->setup_time_minute = isset($data['setup_time_minute']) ? (int)$data['setup_time_minute'] : $item->setup_time_minute;
                        $item->pair = isset($data['pair']) ? (int)$data['pair'] : $item->pair;
                        $item->cavity = isset($data['cavity']) ? (int)$data['cavity'] : $item->cavity;
                        $item->cycle_time = isset($data['cycle_time']) ? (int)$data['cycle_time'] : $item->cycle_time;
                    }

                    if ($item->isDirty()) {
                        $newValues = $item->only(['item_name', 'tipe_mesin', 'standart_packaging_list', 'setup_time_minute', 'pair', 'cavity', 'customer_code', 'cycle_time']);
                        $item->save();

                        // Log differences
                        $diffOld = [];
                        $diffNew = [];
                        foreach ($newValues as $k => $v) {
                            if ($oldValues[$k] != $v) {
                                $diffOld[$k] = $oldValues[$k];
                                $diffNew[$k] = $v;
                            }
                        }

                        if (!empty($diffNew)) {
                            MasterItemLog::create([
                                'user_id' => auth()->id(),
                                'item_code' => $item->item_code,
                                'action' => 'csv_import_update',
                                'old_values' => $diffOld,
                                'new_values' => $diffNew,
                            ]);
                        }
                    }
                } else {
                    // Create new item
                    $item = new MasterListItem();
                    $item->item_code = $data['item_code'];
                    $item->item_name = $data['item_name'] ?? '';
                    $item->tipe_mesin = $data['tipe_mesin'] ?? '0';
                    $item->standart_packaging_list = (int)($data['standart_packaging_list'] ?? 0);
                    $item->setup_time_minute = (int)($data['setup_time_minute'] ?? 0);
                    $item->pair = (int)($data['pair'] ?? 0);
                    $item->cavity = (int)($data['cavity'] ?? 0);
                    $item->customer_code = $data['customer_code'] ?? '0';
                    $item->cycle_time = (int)($data['cycle_time'] ?? 0);
                    $item->save();

                    MasterItemLog::create([
                        'user_id' => auth()->id(),
                        'item_code' => $item->item_code,
                        'action' => 'csv_import_create',
                        'old_values' => null,
                        'new_values' => $item->only(['item_code', 'item_name', 'tipe_mesin', 'standart_packaging_list', 'setup_time_minute', 'pair', 'cavity', 'customer_code', 'cycle_time']),
                    ]);
                }

                $processed++;
            }
        });

        fclose($file);

        $this->importedRowsCount += $processed;

        if ($this->importedRowsCount >= $this->totalRows) {
            // Clean up file
            if (file_exists($this->tempFilePath)) {
                unlink($this->tempFilePath);
            }
            $this->tempFilePath = '';
            $this->importing = false;
            session()->flash('message', "Successfully synced {$this->totalRows} records from SAP CSV.");
        }
    }

    public function cancelImport()
    {
        if ($this->tempFilePath && file_exists($this->tempFilePath)) {
            unlink($this->tempFilePath);
        }
        $this->tempFilePath = '';
        $this->totalRows = 0;
        $this->previewRows = [];
        $this->importedRowsCount = 0;
        $this->importing = false;
        $this->file = null;
    }

    public function render()
    {
        $items = MasterListItem::where(function ($query) {
                $query->where('item_code', 'like', '%'.$this->search.'%')
                    ->orWhere('item_name', 'like', '%'.$this->search.'%');
            })
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('livewire.admin.master-list-manager', [
            'items' => $items,
        ]);
    }
}
