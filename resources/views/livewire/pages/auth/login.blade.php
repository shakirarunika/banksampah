<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

// Kita pake layout 'app' tapi nanti kita kosongin isinya biar nggak gepeng
new #[Layout('layouts.app')] class extends Component {
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();
        $this->form->authenticate();
        Session::regenerate();
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="fixed inset-0 z-[100] bg-white flex flex-col lg:flex-row overflow-y-auto">
    <div
        class="hidden lg:flex lg:w-1/2 bg-emerald-600 p-16 flex-col justify-between relative overflow-hidden text-white">
        <svg class="absolute top-0 right-0 transform translate-x-1/2 -translate-y-1/2 opacity-10 w-full h-full"
            fill="currentColor" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
        </svg>

        <div class="relative z-10">
            <div class="bg-white/20 p-3 rounded-2xl w-fit mb-6 backdrop-blur-md">
                <svg class="w-12 h-12 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <h1 class="text-6xl font-black tracking-tighter uppercase leading-[0.9]">
                Bank<br><span class="text-emerald-200">Sampah</span>
            </h1>
            <div
                class="mt-6 inline-block px-4 py-1.5 bg-emerald-500 rounded-full text-[10px] font-black uppercase tracking-[0.2em]">
                • PT CISARUA MOUNTAIN DAIRY Tbk •
            </div>
        </div>

        <div class="relative z-10">
            <blockquote class="text-3xl font-medium italic border-l-4 border-blue-300 pl-8 leading-snug">
                "Dasi Aya - Tiada Sisa yang Tak Berdaya"
            </blockquote>
            <p class="mt-6 text-xs text-emerald-100 font-black uppercase tracking-[0.3em] opacity-70">
                HRGA Department
            </p>
        </div>
    </div>

    <div class="flex-1 flex flex-col bg-white">
        <div class="flex-1 flex items-center justify-center p-8 sm:p-16">
            <div class="w-full max-w-sm">
                <div class="lg:hidden mb-12 text-center">
                    <h2 class="text-4xl font-black text-gray-800 uppercase tracking-tighter">
                        Bank<span class="text-emerald-600">Sampah</span>
                    </h2>
                </div>

                <div class="mb-12">
                    <h3 class="text-3xl font-black text-gray-800 uppercase tracking-tight">Login</h3>
                    <p class="text-sm text-gray-500 font-bold mt-2">Gunakan Nomor Induk Karyawan untuk akses sistem.</p>
                </div>

                <x-auth-session-status class="mb-6" :status="session('status')" />

                <form wire:submit="login" class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">NIK
                            Karyawan</label>
                        <input wire:model="form.employee_code" type="text" required autofocus
                            class="block w-full px-4 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-emerald-500 focus:bg-white font-mono text-lg tracking-widest transition-all"
                            placeholder="Contoh: 04.1234" />
                        @error('form.employee_code')
                            <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between items-center ml-1">
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Kata
                                Sandi</label>
                        </div>
                        <input wire:model="form.password" type="password" required
                            class="block w-full px-4 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all"
                            placeholder="••••••••" />
                        @error('form.password')
                            <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit"
                        class="w-full bg-emerald-600 text-white py-5 rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-emerald-700 active:scale-[0.98] transition-all shadow-xl shadow-emerald-100 flex justify-center items-center gap-3">
                        <span wire:loading.remove wire:target="login">Masuk Sekarang</span>
                        <span wire:loading wire:target="login" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </span>
                    </button>
                </form>
            </div>
        </div>

        <footer class="p-8 border-t border-gray-100 text-center lg:text-left">
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em]">
                &copy; 2026 HRGA Department • Made with ❤️ by Faishal Muhammad
        </footer>
    </div>
</div>
