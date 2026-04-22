<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <x-application-logo class="block h-9 w-auto fill-current text-emerald-600" />
                    </a>
                    <span class="ml-3 font-black text-xl tracking-tighter text-gray-800 uppercase">
                        Bank<span class="text-emerald-600">Sampah</span>
                    </span>
                </div>

                @auth
                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                            <span class="font-black text-[10px] uppercase tracking-widest">{{ __('Dashboard') }}</span>
                        </x-nav-link>

                        @can('access-petugas')
                            <x-nav-link :href="route('transactions.index')" :active="request()->routeIs('transactions.*')" wire:navigate>
                                <span class="font-black text-[10px] uppercase tracking-widest">{{ __('Data Transaksi') }}</span>
                            </x-nav-link>

                            <div class="hidden sm:flex sm:items-center sm:ms-2">
                                <x-dropdown align="right" width="48">
                                    <x-slot name="trigger">
                                        <button
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-[10px] leading-4 font-black uppercase tracking-widest rounded-md text-emerald-600 bg-white hover:text-emerald-700 focus:outline-none transition ease-in-out duration-150">
                                            <div>Keuangan</div>
                                            <div class="ms-1">
                                                <svg class="fill-current h-4 w-4" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </button>
                                    </x-slot>
                                    <x-slot name="content">
                                        <x-dropdown-link :href="route('withdrawals.index')" wire:navigate class="text-red-600 font-bold">
                                            {{ __('Pencairan Saldo') }}
                                        </x-dropdown-link>

                                        @can('access-admin')
                                            <x-dropdown-link :href="route('vendor-sales.index')" wire:navigate class="text-orange-600 font-bold">
                                                {{ __('Penjualan Vendor') }}
                                            </x-dropdown-link>
                                            <x-dropdown-link :href="route('reconciliation.index')" wire:navigate class="text-blue-600 font-bold">
                                                {{ __('Rekonsiliasi Bulanan') }}
                                            </x-dropdown-link>
                                        @endcan
                                    </x-slot>
                                </x-dropdown>
                            </div>
                        @endcan

                        @can('access-admin')

                            <div class="hidden sm:flex sm:items-center sm:ms-2">
                                <x-dropdown align="right" width="48">
                                    <x-slot name="trigger">
                                        <button
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-[10px] leading-4 font-black uppercase tracking-widest rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                            <div>Master Data</div>
                                            <div class="ms-1">
                                                <svg class="fill-current h-4 w-4" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </button>
                                    </x-slot>
                                    <x-slot name="content">
                                        <x-dropdown-link :href="route('master.waste')"
                                            wire:navigate>{{ __('Jenis Sampah') }}</x-dropdown-link>
                                        <x-dropdown-link :href="route('master.division')"
                                            wire:navigate>{{ __('Data Divisi') }}</x-dropdown-link>
                                        <x-dropdown-link :href="route('master.users')"
                                            wire:navigate>{{ __('Data Karyawan') }}</x-dropdown-link>
                                    </x-slot>
                                </x-dropdown>
                            </div>
                        @endcan
                    </div>
                @endauth
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    <div class="mr-3 text-right">
                        <div class="text-xs font-bold text-gray-800 uppercase leading-none">{{ auth()->user()->name }}</div>
                        <div class="text-[10px] text-emerald-600 font-bold uppercase tracking-widest">
                            {{ auth()->user()->role }}</div>
                    </div>

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                <div class="p-1 bg-gray-100 rounded-full">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile')" wire:navigate>{{ __('Profile') }}</x-dropdown-link>
                            <button wire:click="logout" class="w-full text-start">
                                <x-dropdown-link>{{ __('Log Out') }}</x-dropdown-link>
                            </button>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}"
                        class="text-sm font-bold text-gray-500 hover:text-emerald-600 uppercase tracking-widest"
                        wire:navigate>Log In</a>
                @endauth
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden bg-white border-t border-gray-100">
        @auth
            <div class="pt-2 pb-3 space-y-1">
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                @can('access-petugas')
                    <x-responsive-nav-link :href="route('transactions.index')" :active="request()->routeIs('transactions.*')" wire:navigate>
                        {{ __('Data Transaksi') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('withdrawals.index')" :active="request()->routeIs('withdrawals.*')" wire:navigate class="text-red-600 font-bold">
                        {{ __('Pencairan Saldo') }}
                    </x-responsive-nav-link>
                @endcan
                @can('access-admin')
                    <x-responsive-nav-link :href="route('vendor-sales.index')" :active="request()->routeIs('vendor-sales.*')" wire:navigate class="text-orange-600 font-bold">
                        {{ __('Penjualan Vendor') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('reconciliation.index')" :active="request()->routeIs('reconciliation.*')" wire:navigate class="text-blue-600 font-bold">
                        {{ __('Rekonsiliasi Bulanan') }}
                    </x-responsive-nav-link>
                @endcan
            </div>
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="px-4">
                    <div class="font-bold text-base text-gray-800 uppercase">{{ auth()->user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
                </div>
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile')" wire:navigate>{{ __('Profile') }}</x-responsive-nav-link>
                    <button wire:click="logout" class="w-full text-start">
                        <x-responsive-nav-link>{{ __('Log Out') }}</x-responsive-nav-link>
                    </button>
                </div>
            </div>
        @endauth
    </div>
</nav>
