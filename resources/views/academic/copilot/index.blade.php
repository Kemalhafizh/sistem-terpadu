<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('AI Teacher Copilot') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">
            
            {{-- Large welcome section --}}
            <div class="relative overflow-hidden bg-gradient-to-br from-indigo-900 via-indigo-950 to-violet-950 text-white rounded-[2.5rem] p-8 md:p-12 shadow-2xl border border-indigo-800/40">
                <!-- Animated Background Blobs -->
                <div class="absolute -top-10 -left-10 w-40 h-40 bg-indigo-500 rounded-full mix-blend-screen filter blur-3xl opacity-20 animate-blob"></div>
                <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-violet-500 rounded-full mix-blend-screen filter blur-3xl opacity-25 animate-blob animation-delay-2000"></div>
                
                <div class="relative z-10 max-w-3xl">
                    <span class="px-3.5 py-1.5 bg-indigo-500/20 text-indigo-300 border border-indigo-400/30 rounded-full text-[10px] uppercase font-black tracking-widest inline-block mb-6">
                         AI Powered Assistant
                    </span>
                    <h1 class="text-3xl md:text-5xl font-black tracking-tight leading-tight mb-4 bg-gradient-to-r from-white via-indigo-100 to-violet-200 bg-clip-text text-transparent">
                        AI Teacher Copilot: Asisten Cerdas Guru Kreatif
                    </h1>
                    <p class="text-sm md:text-base text-indigo-200/90 leading-relaxed font-medium">
                        Tingkatkan produktivitas mengajar Anda dengan asisten bertenaga AI. Rancang modul ajar RPP Kurikulum Merdeka secara instan dan buat butir soal CBT berkualitas tinggi langsung dari kutipan materi ajar Anda.
                    </p>
                </div>
            </div>

            {{-- Split grid containing two beautifully crafted interactive cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                {{-- Card 1: RPP Kurikulum Merdeka Writer --}}
                <div class="group relative bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-xl hover:shadow-2xl border border-gray-100 dark:border-gray-700/50 transition-all duration-300 hover:-translate-y-1 flex flex-col justify-between overflow-hidden">
                    <div class="absolute top-0 right-0 -mt-8 -mr-8 w-32 h-32 bg-teal-500/5 dark:bg-teal-500/10 rounded-full blur-2xl transition-all group-hover:scale-125"></div>
                    
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-teal-50 dark:bg-teal-900/30 rounded-2xl flex items-center justify-center text-teal-600 dark:text-teal-400 mb-6 shadow-inner">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-900/30 px-3.5 py-1 rounded-full">
                            RPP Generator
                        </span>
                        <h3 class="text-xl md:text-2xl font-black text-gray-900 dark:text-white mt-4 mb-3">
                            RPP Kurikulum Merdeka Writer
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed mb-6 font-medium">
                            Tulis modul ajar RPP Kurikulum Merdeka secara instan. Dukungan analisis profil pelajar Pancasila, langkah ajar aktif, dan rubrik asesmen.
                        </p>
                    </div>
                    
                    <div class="relative z-10 pt-4">
                        <a href="{{ route('academic.copilot.rpp') }}" class="w-full inline-flex items-center justify-center px-6 py-4 bg-teal-600 hover:bg-teal-700 dark:bg-teal-500 dark:hover:bg-teal-600 text-white rounded-2xl text-xs font-black uppercase tracking-wider shadow-lg shadow-teal-600/20 dark:shadow-none hover:shadow-xl hover:shadow-teal-600/30 transition-all duration-300">
                            <span>Buka RPP Writer</span>
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Card 2: CBT Smart Question Creator --}}
                <div class="group relative bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-xl hover:shadow-2xl border border-gray-100 dark:border-gray-700/50 transition-all duration-300 hover:-translate-y-1 flex flex-col justify-between overflow-hidden">
                    <div class="absolute top-0 right-0 -mt-8 -mr-8 w-32 h-32 bg-indigo-500/5 dark:bg-indigo-500/10 rounded-full blur-2xl transition-all group-hover:scale-125"></div>
                    
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-indigo-50 dark:bg-indigo-900/30 rounded-2xl flex items-center justify-center text-indigo-600 dark:text-indigo-400 mb-6 shadow-inner">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-3.5 py-1 rounded-full">
                            CBT Generator
                        </span>
                        <h3 class="text-xl md:text-2xl font-black text-gray-900 dark:text-white mt-4 mb-3">
                            CBT Smart Question Creator
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed mb-6 font-medium">
                            Rancang butir soal ujian pilihan ganda dan esai langsung dari kutipan materi ajar. Analisis taksonomi Bloom dan kunci penjelasan cerdas.
                        </p>
                    </div>
                    
                    <div class="relative z-10 pt-4">
                        <a href="{{ route('academic.copilot.cbt') }}" class="w-full inline-flex items-center justify-center px-6 py-4 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-2xl text-xs font-black uppercase tracking-wider shadow-lg shadow-indigo-600/20 dark:shadow-none hover:shadow-xl hover:shadow-indigo-600/30 transition-all duration-300">
                            <span>Buka CBT Creator</span>
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
