<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public bool $saved = false;

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        $this->saved = false;

        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                // NIK = password default, gampang ditebak
                'password' => ['required', 'string', Password::defaults(), 'confirmed', Rule::notIn([Auth::user()->employee_code])],
            ], [
                'current_password.required' => 'Password saat ini wajib diisi.',
                'current_password.current_password' => 'Password saat ini salah.',
                'password.required' => 'Password baru wajib diisi.',
                'password.min' => 'Password baru minimal :min karakter.',
                'password.confirmed' => 'Ulangi password baru tidak cocok.',
                'password.not_in' => 'Password baru tidak boleh sama dengan NIK.',
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        $user = Auth::user();
        $wasForced = $user->must_change_password;
        $user->password = Hash::make($validated['password']);
        $user->must_change_password = false;
        $user->save();

        if ($wasForced) {
            session()->flash('flash_success', 'Password berhasil diganti. Silakan gunakan password baru saat login berikutnya.');
            $this->redirect(route('dashboard', absolute: false), navigate: true);

            return;
        }

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->saved = true;
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Ganti Password
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Gunakan password minimal 8 karakter yang tidak mudah ditebak, dan jangan pakai NIK.
        </p>
    </header>

    <form wire:submit="updatePassword" class="mt-6 space-y-6">
        <div>
            <x-input-label for="update_password_current_password" value="Password Saat Ini" />
            <x-text-input wire:model="current_password" id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Password Baru" />
            <x-text-input wire:model="password" id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Ulangi Password Baru" />
            <x-text-input wire:model="password_confirmation" id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button wire:loading.attr="disabled" wire:target="updatePassword">Simpan</x-primary-button>
            <span wire:loading wire:target="updatePassword" class="text-sm text-gray-600">Menyimpan...</span>
        </div>

        @if ($saved)
            <div role="status" class="p-4 bg-emerald-50 border-l-4 border-emerald-600 text-emerald-800 text-sm font-bold rounded-r-xl">
                ✅ Password berhasil diganti. Gunakan password baru saat login berikutnya.
            </div>
        @endif
    </form>
</section>
