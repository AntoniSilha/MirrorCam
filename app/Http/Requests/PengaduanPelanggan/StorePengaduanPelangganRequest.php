<?php

namespace App\Http\Requests\PengaduanPelanggan;

use Illuminate\Foundation\Http\FormRequest;

class StorePengaduanPelangganRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_pemesanan' => ['required', 'integer', 'exists:detail_pemesanan_tenants,id_pemesanan'],
            'kategori_pelanggaran' => ['required', 'string', 'max:255'],
            'waktu_kejadian' => ['required', 'date'],
            'kronologi_kejadian' => ['required', 'string', 'min:10', 'max:5000'],
            'bukti_dokumen' => ['nullable'],
            'bukti_dokumen.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,mp4,mov,pdf,raw', 'max:25600'],
            'estimasi_klaim' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Custom validation messages in Indonesian.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id_pemesanan.required' => 'Silakan pilih nomor pesanan / gear yang bermasalah.',
            'id_pemesanan.exists' => 'Nomor pesanan yang dipilih tidak valid.',
            'kategori_pelanggaran.required' => 'Pilih kategori kendala yang Anda alami.',
            'waktu_kejadian.required' => 'Tentukan tenggat waktu kejadian kendala.',
            'kronologi_kejadian.required' => 'Jelaskan rincian kronologi masalah secara jelas.',
            'kronologi_kejadian.min' => 'Rincian kronologi masalah minimal 10 karakter.',
            'bukti_dokumen.*.file' => 'Berkas bukti harus berupa file yang valid.',
            'bukti_dokumen.*.mimes' => 'Format file bukti harus berupa JPG, PNG, WEBP, MP4, MOV, atau PDF.',
            'bukti_dokumen.*.max' => 'Ukuran file bukti maksimal 25MB per berkas.',
        ];
    }
}
