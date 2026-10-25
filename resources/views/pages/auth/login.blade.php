<x-layouts.auth>
    <div class="w-11/12 max-w-5xl mt-8 px-6 py-16 bg-white shadow-sm overflow-hidden rounded-sm border border-gray-200 text-center">
        
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Masuk ke Akun</h1>
        <p class="text-gray-500 text-sm mb-8">Akses fitur lengkap MirrorCam</p>

        <!-- Tombol Login Google -->
        <a href="{{ route('google.redirect') }}" class="inline-flex items-center justify-center w-full px-4 py-3 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="w-5 h-5 mr-2">
            Lanjutkan dengan Google
        </a>
        
        <div class="mt-4 text-sm">
            <a href="#" class="text-gray-500 hover:underline">Butuh bantuan akses akun?</a>
        </div>

    </div>
</x-layouts.auth>