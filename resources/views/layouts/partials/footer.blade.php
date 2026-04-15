<footer class="bg-white border-t border-gray-100 py-8 mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-center gap-6">

            <div class="text-center md:text-left">
                <div class="flex items-center justify-center md:justify-start gap-2 mb-2">
                    <x-application-logo class="w-6 h-6 fill-current text-emerald-600" />
                    <span class="font-black text-sm tracking-tighter text-gray-800 uppercase">
                        Bank<span class="text-emerald-600">Sampah</span>
                    </span>
                </div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">
                    "Dasi Aya - Tiada Sisa yang Tak Berdaya"
                </p>
            </div>

            <div class="flex gap-8 text-center uppercase tracking-widest">
                <div>
                    <p class="text-[9px] font-black text-gray-400 mb-1">Status Sistem</p>
                    <div class="flex items-center gap-1.5 justify-center">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                        <span class="text-[10px] font-bold text-green-600">Optimal</span>
                    </div>
                </div>
                <div class="border-x border-gray-100 px-8">
                    <p class="text-[9px] font-black text-gray-400 mb-1">Operational</p>
                    <p class="text-[10px] font-bold text-slate-700">24 / 7</p>
                </div>
            </div>

            <div class="text-center md:text-right">
                <p class="text-[10px] font-black text-slate-800 uppercase tracking-widest">
                    &copy; {{ date('Y') }} HRGA DEPARTMENT
                </p>
                <p class="text-[9px] font-bold text-gray-400 uppercase mt-1">
                    Made with ❤️ by Faishal Muhammad
                </p>
            </div>

        </div>
    </div>
</footer>
