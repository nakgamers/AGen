<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola RM Manager') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" style="padding:1.5rem;">

                @if(session('success'))
                    <div style="background-color:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; border-radius:0.375rem; padding:0.75rem 1rem; margin-bottom:1rem;">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div style="background-color:#fef2f2; color:#991b1b; border:1px solid #fecaca; border-radius:0.375rem; padding:0.75rem 1rem; margin-bottom:1rem;">{{ session('error') }}</div>
                @endif

                        <div class="flex gap-2" style="margin-bottom:1rem; justify-content:space-between; flex-wrap:wrap;">
            <form action="{{ route('rm-managers.index') }}" method="GET" class="flex gap-2" style="flex:1; min-width:16rem;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama RM..." class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md" style="background-color:#1f2937;">Cari</button>
                @if(request('search'))
                    <a href="{{ route('rm-managers.index') }}" class="px-4 py-2 rounded-md" style="background-color:#e5e7eb; color:#374151; text-decoration:none;">Reset</a>
                @endif
            </form>

            <a href="{{ route('rm-managers.create') }}" class="px-4 py-2 text-white rounded-md" style="background-color:#4f46e5; text-decoration:none;">+ Tambah RM</a>
        </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600 border">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b">
                            <tr>
                                <th class="px-4 py-3">Nama RM</th>
                                <th class="px-4 py-3">Nomor Telepon</th>
                                <th class="px-4 py-3" style="text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rmManagers as $rm)
                                <tr class="border-b">
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $rm->name }}</td>
                                    <td class="px-4 py-3">{{ $rm->phone_number }}</td>
                                    <td class="px-4 py-3" style="text-align:center;">
                                        <a href="{{ route('rm-managers.edit', $rm) }}" style="color:#4f46e5; font-weight:500; text-decoration:none;">Ubah</a>

                                        <form action="{{ route('rm-managers.destroy', $rm) }}" method="POST"
                                              class="form-hapus" data-nama="{{ $rm->name }}"
                                              style="display:inline; margin-left:0.75rem;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="color:#dc2626; font-weight:500; background:none; border:none; cursor:pointer;">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-4 py-4 text-center text-gray-400">Tidak ada data RM.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:1rem;">
                    {{ $rmManagers->links() }}
                </div>

            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.form-hapus').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                var nama = form.dataset.nama;
                if (!confirm('Hapus RM ' + nama + '? Data akan disembunyikan dari daftar.')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</x-app-layout>