<?php

namespace App\Http\Controllers;

use App\Models\Coffe;
use Dotenv\Exception\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CoffeController extends Controller
{
    public function create(Request $request)
    {

        try {
            //proses validasi inputan
            $validator = Validator::make($request->all(), [
                'name' => 'required|string', // name harus diisi dan berupa string.
                'size' => 'required|string', // size harus diisi dan berupa string.
                'price' => 'required|numeric', // price harus diisi dan berupa angka.
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // image harus diisi, berupa file gambar dengan jenis file jpeg, png, jpg, atau gif, dan ukuran maksimum 2MB.
            ]);

            //ketika validasi di atas tidak terpenuhi, maka response akan seperti di bawah dengan status code http 422
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => 'Inputan yang anda masukkan tidak valid', 'erros' => $validator->errors()], 422);
            }

            // Menyimpan data yang telah divalidasi kedalam database
            $payload = $validator->validated();

            // Mengambil file gambar yang diunggah
            $image = $request->file('image');

            // Menyimpan file gambar ke direktori penyimpanan (misalnya: public/images)
            $storedImage = $image->store('images', 'public');

            // Menambahkan nama file gambar ke payload sebelum menyimpannya ke database
            $payload['image'] = $storedImage;

            // Membuat entri baru dalam database dengan data yang divalidasi
            Coffe::create($payload);

            // Mengembalikan respons sukses
            return response()->json(['status' => true, 'message' => 'Coffe berhasil ditambahkan']);
        } catch (\Exception $e) {
            //jika ada kesalahan didalam blok try&catch maka blok ini akan terreturn sebagai response
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function get(Request $req)
    {
        try {
            // Membuat query untuk model Coffee
            $query = Coffe::query();

            // Memeriksa apakah parameter 'name' telah diberikan dalam request
            // Jika iya, tambahkan kondisi pencarian berdasarkan nama
            if ($req->has('name')) {
                $query->where('name', 'like', '%' . $req->input('name') . '%');
            }

            // Mengatur batasan jumlah data yang akan ditampilkan per halaman
            // Jika parameter 'limit' ada dalam request, jika tidak maka default 10
            $limit = $req->has('limit') ? intval($req->input('limit')) : 10;

            // Melakukan query dengan paginasi sesuai batasan yang ditentukan
            $coffees = $query->paginate($limit);

            // Jika hasil query kosong, kembalikan respons JSON dengan status 404 dan pesan
            if ($coffees->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak ada data Coffe yang ditemukan'
                ], 404);
            }

            // Jika berhasil, kembalikan respons JSON dengan status 200 dan data Coffee
            return response()->json(['status' => true, 'data' => $coffees]);
        } catch (\Exception $e) {
            //jika ada kesalahan didalam blok try&catch maka blok ini akan terreturn sebagai response
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }

    }

    public function update(Request $request, $id)
    {
        try {
            // Proses validasi inputan
            $validator = Validator::make($request->all(), [
                'name' => 'required|string', // name harus diisi dan berupa string.
                'size' => 'required|string', // size harus diisi dan berupa string.
                'price' => 'required|numeric', // price harus diisi dan berupa angka.
                'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // image opsional, jika diisi harus berupa file gambar dengan jenis file jpeg, png, jpg, atau gif, dan ukuran maksimum 2MB.
            ]);

            // Ketika validasi di atas tidak terpenuhi, maka response akan seperti di bawah dengan status code http 422
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => 'Inputan yang anda masukkan tidak valid', 'errors' => $validator->errors()], 422);
            }

            // Temukan berdasarkan ID
            $coffe = Coffe::find($id);

            // Jika  tidak ditemukan, kembalikan respons dengan status 404
            if (!$coffe) {
                return response()->json(['status' => false, 'message' => 'Coffe tidak ditemukan'], 404);
            }

            // Mengupdate data  yang diperbarui
            $coffe->name = $request->input('name');
            $coffe->size = $request->input('size');
            $coffe->price = $request->input('price');

            // Jika ada file gambar yang diunggah, simpan gambar yang baru
            if ($request->hasFile('image')) {
                $oldImagePath = $coffe->image; // Simpan path foto lama
                $image = $request->file('image');
                $storedImage = $image->store('images', 'public');
                $coffe->image = $storedImage;

                // Hapus foto lama dari penyimpanan
                if ($oldImagePath && Storage::disk('public')->exists($oldImagePath)) {
                    Storage::disk('public')->delete($oldImagePath);
                }
            }

            // Simpan perubahan dalam database
            $coffe->save();

            // Mengembalikan respons sukses
            return response()->json(['status' => true, 'message' => 'Coffe berhasil diperbarui']);
        } catch (\Exception $e) {
            // Jika ada kesalahan didalam blok try&catch maka blok ini akan terreturn sebagai response
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }



    public function delete($id)
    {
        try {
            // Temukan berdasarkan ID
            $coffe = Coffe::findOrFail($id);

            // Hapus foto dari penyimpanan
            if ($coffe->image && Storage::disk('public')->exists($coffe->image)) {
                Storage::disk('public')->delete($coffe->image);
            }

            // Hapus data
            $coffe->delete();

            // Mengembalikan respons jika penghapusan berhasil
            return response()->json(['status' => true, 'message' => 'Berhasil Hapus Coffe']);
        } catch (ModelNotFoundException $e) {
            // Tangani jika kopi tidak ditemukan
            return response()->json(['status' => false, 'message' => 'Coffe tidak ditemukan'], 404);
        } catch (\Exception $e) {
            // Jika ada kesalahan didalam blok try&catch maka blok ini akan terreturn sebagai response
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }


}
