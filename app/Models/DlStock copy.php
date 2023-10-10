<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DlStock extends Model{

    use SoftDeletes;

    protected $table = 'dl_stocks';

	protected $guarded=['id'];

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
