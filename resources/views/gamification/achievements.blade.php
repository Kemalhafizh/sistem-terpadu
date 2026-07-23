<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
            {{ __('Katalog Lencana Pencapaian') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-10">
            <!-- Header Ringkasan & Aksen Estetis -->
            <div class="bg-gradient-to-r from-indigo-900/90 to-purple-900/90 backdrop-blur-md border border-indigo-500/20 text-white rounded-[2rem] p-8 shadow-xl flex flex-col md:flex-row items-center justify-between gap-6 hover:shadow-indigo-500/5 transition-all duration-300">
                <div class="space-y-2 flex-grow">
                    <h3 class="text-2xl font-black tracking-tight text-indigo-300"> Galeri Lencana Kehormatan</h3>
                    <p class="text-xs text-gray-200 font-medium leading-relaxed max-w-xl">
                        Kumpulkan lencana prestisius dengan menyelesaikan tantangan akademik, disiplin presensi kelas, partisipasi sosial di forum, dan amalan ibadah Islami terpadu.
                    </p>
                </div>
                <div class="w-full md:w-auto flex-shrink-0 bg-white/10 px-6 py-4 rounded-2xl border border-white/10 text-center">
                    <p class="text-[10px] font-black uppercase tracking-widest text-indigo-200">Koleksi Terbuka</p>
                    <p class="text-3xl font-black text-white mt-1">{{ count($earnedIds) }} / {{ $definitions->count() }}</p>
                </div>
            </div>

            <!-- Grid Album Lencana (12 Badges) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-8">
                @foreach($definitions as $def)
                    @php
                        $isEarned = in_array($def->id, $earnedIds);
                        
                        // Set Rarity Styling
                        $rarityStyles = [
                            'common' => 'bg-slate-100 text-slate-800 border-slate-200',
                            'rare' => 'bg-blue-50 text-indigo-700 border-indigo-100',
                            'epic' => 'bg-purple-50 text-purple-700 border-purple-100',
                            'legendary' => 'bg-gradient-to-r from-amber-400 to-yellow-600 text-white border-transparent font-extrabold shadow-md shadow-amber-500/10 animate-pulse',
                        ];
                        
                        $rarityBadge = $rarityStyles[$def->rarity] ?? $rarityStyles['common'];
                    @endphp

                    @if($isEarned)
                        <!-- Card 1: Lencana Terbuka (Unlocked) -->
                        <div class="bg-white rounded-[2rem] p-6 shadow-md border border-indigo-100/50 hover:shadow-indigo-500/10 hover:scale-105 transition-all duration-300 relative overflow-hidden flex flex-col justify-between h-[300px] group">
                            <!-- Glowing background highlights -->
                            <div class="absolute -top-10 -right-10 w-24 h-24 rounded-full opacity-10 blur-xl" style="background-color: {{ $def->color }}"></div>
                            
                            <div>
                                <!-- Rarity & Checkmark -->
                                <div class="flex justify-between items-center mb-4">
                                    <span class="px-2.5 py-1 rounded text-[9px] font-black uppercase tracking-widest border {{ $rarityBadge }}">
                                        {{ $def->rarity }}
                                    </span>
                                    <span class="w-6 h-6 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-xs font-black shadow-inner shadow-emerald-600/10">
                                        
                                    </span>
                                </div>

                                <!-- Icon Lencana -->
                                <div class="w-20 h-20 mx-auto rounded-2xl flex items-center justify-center text-4xl shadow-lg border border-gray-100 transition-transform group-hover:rotate-12 duration-300" style="background-color: {{ $def->color }}15; border-color: {{ $def->color }}30">
                                    {{ $def->icon }}
                                </div>

                                <!-- Detail Lencana -->
                                <div class="text-center mt-4 space-y-1">
                                    <h4 class="text-sm font-black text-gray-900 leading-tight">{{ $def->name }}</h4>
                                    <p class="text-[10px] text-gray-500 font-bold leading-relaxed line-clamp-2 px-2">{{ $def->description }}</p>
                                </div>
                            </div>

                            <!-- Reward XP & Status -->
                            <div class="mt-4 pt-4 border-t border-gray-50 flex justify-between items-center text-xs">
                                <span class="text-emerald-600 font-extrabold">+{{ $def->xp_reward }} XP Reward</span>
                                <span class="font-black text-indigo-600 uppercase tracking-widest text-[9px]">Terbuka</span>
                            </div>
                        </div>
                    @else
                        <!-- Card 2: Lencana Terkunci (Locked) -->
                        <div class="bg-gray-50/80 rounded-[2rem] p-6 border border-gray-200/60 filter grayscale contrast-75 opacity-60 relative overflow-hidden flex flex-col justify-between h-[300px] group">
                            
                            <div>
                                <!-- Lock Status -->
                                <div class="flex justify-between items-center mb-4">
                                    <span class="px-2.5 py-1 rounded text-[9px] font-black uppercase tracking-widest border bg-gray-100 text-gray-400 border-gray-200">
                                        {{ $def->rarity }}
                                    </span>
                                    <span class="text-gray-400 text-xs">
                                        
                                    </span>
                                </div>

                                <!-- Grayscale Lock Icon -->
                                <div class="w-20 h-20 mx-auto bg-gray-200 rounded-2xl flex items-center justify-center text-4xl shadow-inner border border-gray-200">
                                    {{ $def->icon }}
                                </div>

                                <!-- Detail Lencana -->
                                <div class="text-center mt-4 space-y-1">
                                    <h4 class="text-sm font-black text-gray-400 leading-tight">{{ $def->name }}</h4>
                                    <!-- Syarat Kelayakan -->
                                    <p class="text-[10px] text-gray-400 font-bold leading-relaxed px-2 bg-gray-200/50 py-1.5 rounded-lg border border-dashed border-gray-200 mt-2">
                                        Syarat: {{ $def->description }}
                                    </p>
                                </div>
                            </div>

                            <!-- Reward XP & Status -->
                            <div class="mt-4 pt-4 border-t border-gray-200/60 flex justify-between items-center text-xs text-gray-400">
                                <span>+{{ $def->xp_reward }} XP Reward</span>
                                <span class="font-black uppercase tracking-widest text-[9px]">Terkunci</span>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
