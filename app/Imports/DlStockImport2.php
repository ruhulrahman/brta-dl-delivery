<?php

namespace App\Imports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class DlStockImport2 implements ToModel, SkipsEmptyRows, SkipsOnError, WithHeadingRow, WithChunkReading, WithBatchInserts
{
    use Importable, RemembersRowNumber;
    private $imported_list = [];


    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row){

        if(isset($row['reference_number'])){
            $dlStock = model('DlStock')::where('reference_number', $row['reference_number'])->first();
        }else{
            $dlStock=NULL;
        }

        $insertArray = [
            'reference_number' => $row['reference_number'],
            'serial_number' => $row['serial_number'],
            'entry_box_number' => $row['entry_box_number'],
            'receiving_box_number' => $row['receiving_box_number'],
            'delivery_date' => $row['delivery_date'] ? new Carbon($row['delivery_date']) : NULL,
            'comment' => $row['comment'],
            'receive_date' => $row['receive_date'] ? new Carbon($row['receive_date']) : NULL,
            'is_duplicate' =>$dlStock ? true : false,
            'match_reference_number' => $dlStock ? $dlStock->reference_number : 0,
        ];

        // model('DlStock')::create($insertArray);

        array_push($this->imported_list, $insertArray);

    }

    public function getImportedRows()
    {
        return $this->imported_list;
    }

    public function onError(\Throwable $e)
    {
        return res_msg('Excel file import failed!', 403);
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
