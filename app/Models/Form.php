<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
protected $fillable = [
    "name",
    "form",
    "uuid",
    "type_form_id"
];
}
