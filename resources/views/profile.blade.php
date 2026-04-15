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

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <div class="p-8 bg-white shadow-sm border border-slate-100 rounded-2xl h-fit">
                    <div class="max-w-xl">
                        <livewire:profile.update-profile-information-form />
                    </div>
                </div>

                <div class="p-8 bg-white shadow-sm border border-slate-100 rounded-2xl h-fit">
                    <div class="max-w-xl">
                        <livewire:profile.update-password-form />
                    </div>
                </div>

            </div>

            <div class="p-8 bg-white shadow-sm border border-red-50 rounded-2xl">
                <div class="max-w-xl border-l-4 border-red-500 pl-6">
                    <livewire:profile.delete-user-form />
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
