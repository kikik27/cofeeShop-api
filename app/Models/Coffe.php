<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coffe extends Model
{
    use HasFactory;

    //$fillable adalah properti yang digunakan dalam model Eloquent untuk menentukan kolom mana yang diperbolehkan untuk diisi secara massal (mass assignment).
    protected $fillable = ['name','size','price','image'];
}
