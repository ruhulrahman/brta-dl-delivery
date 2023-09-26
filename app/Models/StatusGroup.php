<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusGroup extends Model{

	protected $guarded=['id'];

	public function statuses(){
		return $this->hasMany('App\Models\Status', 'status_group_id')->where('active', 1);
	}

}
