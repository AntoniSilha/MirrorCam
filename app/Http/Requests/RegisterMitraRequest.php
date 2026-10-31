<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterMitraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Toko / Workshop
            'nama_toko' => ['required', 'string', 'max:255'],
            'alamat_lengkap' => ['required', 'string'],
            'kota_operasional' => ['required', 'string', 'max:100'],
            'whatsapp_toko' => ['required', 'string', 'max:20'],
            'jam_buka' => ['required', 'date_format:H:i'],
            'jam_tutup' => ['required', 'date_format:H:i'],

            // Pengelola / Legalitas
            'nama_pengelola' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'digits:16'],
            'jabatan_pengelola' => ['required', 'string', 'max:100'],
            'foto_ktp' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // Max 5MB

            // Payout / Bank
            'bank_name' => ['required', 'string', 'in:BCA,Mandiri,BNI,BRI,BSI'],
            'nomor_rekening' => ['required', 'string', 'max:50'],
            'nama_pemilik_rekening' => ['required', 'string', 'max:255'],
            'terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'nik.digits' => 'NIK harus tepat 16 digit.',
            'foto_ktp.max' => 'Ukuran foto KTP tidak boleh lebih dari 5MB.',
            'terms.accepted' => 'Anda harus menyetujui Perjanjian Kerja Sama Kemitraan.',
            'bank_name.in' => 'Pilih salah satu bank yang tersedia.',
        ];
    }
}
