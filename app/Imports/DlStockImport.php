<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\DlStock;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
// use Maatwebsite\Excel\Concerns\RemembersRowNumber;

class DlStockImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts, SkipsOnError, ShouldQueue
// class DlStockImport implements ToModel, WithHeadingRow
{
    // use Importable, RemembersRowNumber;
    use SkipsErrors, Importable;
    private $imported_list = [], $user_id;


    public $lastEntryBox = '';
    public $lastReceivingBox = '';

    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        // info($row);
        if(isset($row['reference_number'])){
            $dlStock = model('DlStock')::where('reference_number', $row['reference_number'])->first();
        }else{
            $dlStock=NULL;
        }

        if ($row['entry_box_number']) {
            $this->lastEntryBox = $row['entry_box_number'];
        }

        if ($row['receiving_box_number']) {
            $this->lastReceivingBox = $row['receiving_box_number'];
        }

        // $insertArray = [
        //     'reference_number' => $row['reference_number'],
        //     'serial_number' => $row['serial_number'],
        //     // 'entry_box_number' => $row['entry_box_number'],
        //     'entry_box_number' => $this->lastEntryBox,
        //     'receiving_box_number' => $this->lastReceivingBox,
        //     // 'delivery_date' => $row['delivery_date'] ? new Carbon($row['delivery_date']) : NULL,
        //     'delivery_date' => $row['delivery_date'] ? $row['delivery_date'] : NULL,
        //     'comment' => $row['comment'] ? $row['comment'] : NULL,
        //     // 'receive_date' => $row['receive_date'] ? new Carbon($row['receive_date']) : NULL,
        //     'receive_date' => Carbon::now()->subDays(120),
        //     'is_duplicate' =>$dlStock ? true : false,
        //     'match_reference_number' => $dlStock ? $dlStock->reference_number : 0,
        // ];

        return new DlStock([
            'id' => random_int((int) 10000000000000000, (int) 99999999999999999999),
            'reference_number' => $row['reference_number'] ? $row['reference_number'] : NULL,
            'serial_number' => $row['serial_number'] ? $row['serial_number'] : NULL,
            'entry_box_number' => $this->lastEntryBox,
            'receiving_box_number' => $this->lastReceivingBox,
            'receive_date' => $row['receive_date'] ? new Carbon($row['receive_date']) : NULL,
            'delivery_date' => $row['delivery_date'] ? $row['delivery_date'] : NULL,
            'comment' => $row['comment'] ? $row['comment'] : NULL,
            'creator_id' => $this->user_id,
            'created_at' => Carbon::now(),
        ]);

        // model('DlStock')::create($insertArray);

        // array_push($this->imported_list, $insertArray);
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
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
