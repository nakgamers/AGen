<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah RM Manager') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" style="padding:1.5rem; max-width:40rem;">

                <form action="{{ route('rm-managers.store') }}" method="POST">
                    @csrf

                    <div style="margin-bottom:1rem;">
                        <label for="name" class="block text-sm font-medium text-gray-700" style="margin-bottom:0.25rem;">Nama RM</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="255"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name')
                            <p style="color:#dc2626; font-size:0.875rem; margin-top:0.25rem;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div style="margin-bottom:1.5rem;">
                        <label for="phone_number" class="block text-sm font-medium text-gray-700" style="margin-bottom:0.25rem;">Nomor Telepon</label>
                        <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" required maxlength="30"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('phone_number')
                            <p style="color:#dc2626; font-size:0.875rem; margin-top:0.25rem;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 text-white rounded-md" style="background-color:#1f2937;">Simpan</button>
                        <a href="{{ route('rm-managers.index') }}" class="px-4 py-2 rounded-md" style="background-color:#e5e7eb; color:#374151; text-decoration:none;">Batal</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>