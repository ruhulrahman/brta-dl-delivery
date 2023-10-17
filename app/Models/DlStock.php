<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DlStock extends Model{

    use SoftDeletes;

    protected $table = 'dl_stocks';

    // protected $primaryKey = NULL; // or null
    protected $primaryKey = 'id';

    // protected $guarded = ['id'];

    public $incrementing = false;

    protected $fillable = [
        'id',
        'reference_number',
        'serial_number',
        'dl_number',
        'name',
        'father_name',
        'dob',
        'blood',
        'entry_box_number',
        'receiving_box_number',
        'receive_date',
        'delivery_date',
        'comment',
        'delivered_id',
        'creator_id',
        'editor_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

	// protected $guarded=['id'];

	public function creator(){
		return $this->belongsTo(User::class, 'creator_id');
	}

	public function editor(){
		return $this->belongsTo(User::class, 'editor_id');
	}

	public function delivered(){
		return $this->belongsTo(User::class, 'delivered_id');
	}

}
