<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paper extends Model
{
  protected $guarded = [];

  public function lecturer()
  {
    return $this->hasOne(User::class);
  }
}
