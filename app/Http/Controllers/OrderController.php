<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function create(Request $request)
    {
        // Validasi payload
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string',
            'order_type' => 'required|string',
            'order_date' => 'required|date',
            'order_detail' => 'required|array',
            'order_detail.*.coffes_id' => 'required|exists:coffes,id',
            'order_detail.*.quantity' => 'required|integer|min:1',
        ]);

        // Jika validasi gagal, kembalikan respons dengan status 422 (Unprocessable Entity)
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Inputan yang anda masukkan tidak valid', 'errors' => $validator->errors()], 422);
        }

        // Memulai transaksi database
        try {
            \DB::beginTransaction();

            // Membuat pesanan baru
            $order = new Order();
            $order->customer_name = $request->input('customer_name');
            $order->order_type = $request->input('order_type');
            $order->order_date = $request->input('order_date');
            $order->save();

            // Membuat detail pesanan untuk setiap item dalam payload
            foreach ($request->input('order_detail') as $detail) {
                $orderDetail = new OrderDetail();
                $orderDetail->orders_id = $order->id;
                $orderDetail->coffes_id = $detail['coffes_id'];
                $orderDetail->quantity = $detail['quantity'];
                $orderDetail->save();
            }

            // Commit transaksi database
            \DB::commit();

            // Mengembalikan respons sukses
            return response()->json(['status' => true, 'message' => 'Order berhasil ditambahkan']);
        } catch (\Exception $e) {
            // Rollback transaksi database jika terjadi kesalahan
            \DB::rollback();

            // Mengembalikan respons dengan pesan kesalahan
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function get(Request $request)
    {
        try {
            // Membuat query untuk model Order beserta dengan relasi model orderDetails, lalu orderDetails memanggil relasi ke model coffe
            $query = Order::with('orderDetails', 'orderDetails.coffe');

            // Menambahkan kondisi pencarian berdasarkan nama pelanggan jika parameter 'customer_name' diberikan dalam request
            if ($request->has('customer_name')) {
                $query->where('customer_name', 'like', '%' . $request->input('customer_name') . '%');
            }

            // Menambahkan kondisi pencarian berdasarkan jenis pesanan jika parameter 'order_type' diberikan dalam request
            if ($request->has('order_type')) {
                $query->where('order_type', $request->input('order_type'));
            }

            // Menambahkan kondisi pencarian berdasarkan tanggal pesanan jika parameter 'order_date' diberikan dalam request
            if ($request->has('order_date')) {
                $query->whereDate('order_date', $request->input('order_date'));
            }

            // Mengatur batasan jumlah data yang akan ditampilkan per halaman
            // Jika parameter 'limit' ada dalam request, jika tidak maka default 10
            $limit = $request->has('limit') ? intval($request->input('limit')) : 10;

            // Melakukan query dengan paginasi sesuai batasan yang ditentukan
            $orders = $query->paginate($limit);

            // Jika hasil query kosong, kembalikan respons JSON dengan status 404 dan pesan
            if ($orders->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak ada data pesanan yang ditemukan'
                ], 404);
            }

            // Jika berhasil, kembalikan respons JSON dengan status 200 dan data pesanan
            return response()->json(['status' => true, 'data' => $orders]);
        } catch (\Exception $e) {
            // Jika terjadi kesalahan, kembalikan respons JSON dengan status 500 dan pesan kesalahan
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
