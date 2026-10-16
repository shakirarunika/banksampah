<x-app-layout>
    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <div class="mb-8">
                <h2 class="text-3xl font-black text-slate-800 tracking-tighter uppercase">
                    Pengaturan <span class="text-emerald-600">Profil</span>
                </h2>
                <p class="text-xs text-slate-500 font-bold tracking-widest uppercase mt-1">
                    Kelola Informasi Akun & Keamanan Anda
                </p>
            </div>

            @if (auth()->user()->must_change_password)
                <div role="alert"
                    class="p-4 bg-amber-100 border-l-4 border-amber-500 text-amber-800 font-bold text-xs uppercase tracking-widest rounded-r-xl">
                    ⚠️ Password Anda masih default (NIK). Ganti password dulu sebelum memakai aplikasi.
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <div class="p-8 bg-white shadow-sm border border-slate-100 rounded-2xl h-fit">
                    <div class="max-w-xl">
                        @if (auth()->user()->must_change_password)
                            {{-- Fokus ganti password dulu: cukup tampilkan identitas akun, tanpa form email --}}
                            <h2 class="text-lg font-medium text-gray-900">Informasi Akun</h2>
                            <p class="mt-1 text-sm text-gray-600">Gunakan NIK di bawah sebagai username saat login.</p>
                            <dl class="mt-6 space-y-6">
                                <div>
                                    <dt class="text-sm font-medium text-gray-700">Nama</dt>
                                    <dd class="mt-1 text-base font-bold text-slate-800">{{ auth()->user()->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-700">Username (NIK)</dt>
                                    <dd class="mt-1 text-base font-bold text-slate-800 font-mono">{{ auth()->user()->employee_code }}</dd>
                                </div>
                            </dl>
                        @else
                            <livewire:profile.update-profile-information-form />
                        @endif
                    </div>
                </div>

                <div class="p-8 bg-white shadow-sm border border-slate-100 rounded-2xl h-fit">
                    <div class="max-w-xl">
                        <livewire:profile.update-password-form />
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
