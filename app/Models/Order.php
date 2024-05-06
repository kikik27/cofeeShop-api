<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;


    // Definisikan relasi "one-to-many" antara model ini dengan model OrderDetail.
    // Metode ini memfasilitasi akses ke detail pesanan yang terkait dengan pesanan tertentu.
    // Dengan menggunakan metode ini, kita dapat mengambil semua detail pesanan terkait dengan suatu pesanan tanpa perlu menuliskan query SQL secara manual.
    // Hal ini meningkatkan keterbacaan kode dan memudahkan pengembangan aplikasi karena mengikuti pola konvensi yang dikenal.

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'id');
    }
}
