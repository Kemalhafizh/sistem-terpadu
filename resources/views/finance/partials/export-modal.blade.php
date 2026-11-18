<!-- Modal Export & Print Hub -->
<div x-show="open" 
     class="fixed inset-0 bg-slate-950/80 backdrop-blur-xl z-[100] flex items-center justify-center p-4 md:p-6 overflow-y-auto animate-fade-in"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 scale-95"
     style="display: none;"
     @keydown.escape.window="closeHub()">
     
    <div class="bg-slate-900 rounded-[2.5rem] shadow-2xl border border-slate-800 flex flex-col lg:flex-row w-full max-w-7xl h-[90vh] overflow-hidden">
        
        <!-- SIDEBAR CONFIGURATION (Left Side) -->
        <div class="w-full lg:w-[380px] bg-slate-900 border-b lg:border-b-0 lg:border-r border-slate-800 flex flex-col justify-between overflow-y-auto p-6 md:p-8">
            <div class="space-y-6">
                <!-- Title -->
                <div>
                    <span class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.2em]">Visual Hub</span>
                    <h3 class="text-xl font-black text-white tracking-tight">Cetak & Ekspor</h3>
                    <p class="text-xs text-slate-400 mt-1">Sesuaikan tampilan laporan fisik Anda secara real-time.</p>
                </div>
                
                <hr class="border-slate-800">
                
                <!-- Settings Group 1: Text -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Judul Laporan</label>
                        <input type="text" x-model="title" class="w-full border-slate-700 bg-slate-950/50 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 text-sm text-slate-200 placeholder-slate-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Sub-Judul Laporan</label>
                        <input type="text" x-model="subtitle" class="w-full border-slate-700 bg-slate-950/50 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 text-sm text-slate-200 placeholder-slate-500">
                    </div>
                </div>

                <hr class="border-slate-800">
                
                <!-- Settings Group 2: Visual Themes & Orientation -->
                <div class="space-y-4">
                    <!-- Orientation -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Orientasi Halaman</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" 
                                    @click="orientation = 'portrait'" 
                                    :class="orientation === 'portrait' ? 'bg-emerald-600 text-white border-emerald-500' : 'bg-slate-950/30 text-slate-400 border-slate-800 hover:bg-slate-950/50'"
                                    class="py-2.5 px-3 rounded-xl border text-xs font-bold transition duration-150 flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span>Portrait (A4)</span>
                            </button>
                            <button type="button" 
                                    @click="orientation = 'landscape'" 
                                    :class="orientation === 'landscape' ? 'bg-emerald-600 text-white border-emerald-500' : 'bg-slate-950/30 text-slate-400 border-slate-800 hover:bg-slate-950/50'"
                                    class="py-2.5 px-3 rounded-xl border text-xs font-bold transition duration-150 flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4 rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span>Landscape (A4)</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Color Themes -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Tema & Warna Tabel</label>
                        <div class="flex items-center space-x-3">
                            <button type="button" @click="theme = 'emerald'" 
                                    :class="theme === 'emerald' ? 'ring-2 ring-emerald-500 scale-110' : 'hover:scale-105'"
                                    class="w-6 h-6 rounded-full bg-emerald-600 border border-slate-700 transition duration-150" title="Emerald Green"></button>
                            <button type="button" @click="theme = 'navy'" 
                                    :class="theme === 'navy' ? 'ring-2 ring-blue-500 scale-110' : 'hover:scale-105'"
                                    class="w-6 h-6 rounded-full bg-slate-800 border border-slate-700 transition duration-150" title="Navy Blue"></button>
                            <button type="button" @click="theme = 'slate'" 
                                    :class="theme === 'slate' ? 'ring-2 ring-slate-400 scale-110' : 'hover:scale-105'"
                                    class="w-6 h-6 rounded-full bg-slate-600 border border-slate-700 transition duration-150" title="Slate Gray"></button>
                        </div>
                    </div>
                </div>

                <hr class="border-slate-800">

                <!-- Settings Group 3: Column Visibility -->
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Kolom yang Ditampilkan</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center space-x-2 text-xs text-slate-300 cursor-pointer">
                            <input type="checkbox" x-model="columns.no" class="rounded border-slate-800 bg-slate-950/50 text-emerald-500 focus:ring-emerald-500">
                            <span>No</span>
                        </label>
                        <label class="flex items-center space-x-2 text-xs text-slate-300 cursor-pointer">
                            <input type="checkbox" x-model="columns.siswa" class="rounded border-slate-800 bg-slate-950/50 text-emerald-500 focus:ring-emerald-500">
                            <span>Siswa</span>
                        </label>
                        <label class="flex items-center space-x-2 text-xs text-slate-300 cursor-pointer">
                            <input type="checkbox" x-model="columns.kategori" class="rounded border-slate-800 bg-slate-950/50 text-emerald-500 focus:ring-emerald-500">
                            <span>Kategori</span>
                        </label>
                        <label class="flex items-center space-x-2 text-xs text-slate-300 cursor-pointer">
                            <input type="checkbox" x-model="columns.tagihan" class="rounded border-slate-800 bg-slate-950/50 text-emerald-500 focus:ring-emerald-500">
                            <span>Total Tagihan</span>
                        </label>
                        <label class="flex items-center space-x-2 text-xs text-slate-300 cursor-pointer">
                            <input type="checkbox" x-model="columns.hutang" class="rounded border-slate-800 bg-slate-950/50 text-emerald-500 focus:ring-emerald-500">
                            <span>Sisa Hutang</span>
                        </label>
                        <label class="flex items-center space-x-2 text-xs text-slate-300 cursor-pointer">
                            <input type="checkbox" x-model="columns.status" class="rounded border-slate-800 bg-slate-950/50 text-emerald-500 focus:ring-emerald-500">
                            <span>Status</span>
                        </label>
                    </div>
                </div>

                <hr class="border-slate-800">

                <!-- Settings Group 4: Signature -->
                <div class="space-y-4">
                    <label class="flex items-center space-x-2 text-xs text-slate-300 cursor-pointer">
                        <input type="checkbox" x-model="showSignature" class="rounded border-slate-800 bg-slate-950/50 text-emerald-500 focus:ring-emerald-500">
                        <span class="font-bold">Tampilkan Tanda Tangan</span>
                    </label>
                    
                    <div x-show="showSignature" class="space-y-3 pl-6" x-transition>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Jabatan Penandatangan</label>
                            <input type="text" x-model="signatureRole" class="w-full border-slate-700 bg-slate-950/50 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 text-xs text-slate-200 placeholder-slate-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Nama Lengkap</label>
                            <input type="text" x-model="signatureName" class="w-full border-slate-700 bg-slate-950/50 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 text-xs text-slate-200 placeholder-slate-500">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="space-y-2 mt-8 lg:mt-0">
                <button type="button" 
                        @click="printReport()" 
                        class="flex items-center justify-center space-x-2 py-3.5 bg-primary-600 hover:bg-primary-700 text-white font-black rounded-xl text-sm transition duration-150 w-full text-center shadow-lg shadow-primary-900/20 hover:scale-[1.02] transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 012-2H5a2 2 0 012 2v4a2 2 0 002 2m8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Cetak Laporan / PDF</span>
                </button>
                
                <a :href="'{{ route('finance.billings.export') }}?' + new URLSearchParams(window.location.search).toString()" 
                   class="flex items-center justify-center space-x-2 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition duration-150 w-full text-center shadow-lg shadow-emerald-900/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>Download Excel</span>
                </a>
                
                <button type="button" 
                        @click="closeHub()" 
                        class="py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-xl text-xs transition duration-150 w-full text-center">
                    Tutup
                </button>
            </div>
        </div>
        
        <!-- LIVE A4 PREVIEW WINDOW (Right Side) -->
        <div class="flex-1 bg-slate-950/40 p-6 md:p-10 overflow-y-auto flex items-start justify-center max-h-[90vh] lg:max-h-none shadow-inner">
            
            <div class="relative w-full max-w-full overflow-x-auto flex justify-center">
                <!-- A4 Sheet Container -->
                <div id="printable-paper" 
                     :class="{ 
                        'w-[210mm] min-h-[297mm]': orientation === 'portrait', 
                        'w-[297mm] min-h-[210mm] print-landscape': orientation === 'landscape' 
                     }" 
                     class="bg-white p-12 text-slate-800 shadow-2xl relative transition-all duration-300 origin-top flex flex-col justify-between shrink-0 mb-8 border border-gray-100 text-left">
                    
                    <div>
                        <!-- Document Kop Surat / Header -->
                        <div class="flex justify-between items-center border-b-4 border-double border-slate-900 pb-5 mb-8">
                            <div class="flex items-center space-x-4">
                                <img src="{{ asset('images/logo-kotak.png') }}" alt="{{ school_short_name() }}" class="w-12 h-12 rounded-xl object-contain">
                                <div class="text-left">
                                    <h4 class="font-black text-base tracking-tight leading-none text-slate-950">{{ mb_strtoupper(school_short_name()) }}</h4>
                                    <p class="text-[9px] text-slate-500 font-bold mt-1 uppercase tracking-wider">Sulaone — SISTA {{ school_short_name() }}</p>
                                    <p class="text-[8px] text-slate-400 mt-0.5 leading-none">{{ school_address() }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-block px-2.5 py-0.5 bg-slate-100 rounded text-[8px] font-black text-slate-600 uppercase tracking-widest" x-text="orientation"></span>
                                <p class="text-[8px] text-slate-400 font-bold mt-1 uppercase" x-text="'Tanggal: ' + new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })"></p>
                            </div>
                        </div>
                        
                        <!-- Laporan Title Section -->
                        <div class="text-center mb-8">
                            <h2 class="text-lg font-black text-slate-900 tracking-tight uppercase leading-snug" x-text="title"></h2>
                            <p class="text-xs text-slate-500 font-medium tracking-tight mt-1" x-text="subtitle"></p>
                        </div>
                        
                        <!-- Table List -->
                        <table class="w-full text-left text-xs border-collapse border border-slate-200">
                            <thead>
                                <tr class="text-[9px] font-black uppercase tracking-wider border-b border-slate-200">
                                    <th x-show="columns.no" class="p-3 border border-slate-200 text-center w-12" :class="{
                                        'bg-emerald-600 text-white border-emerald-700': theme === 'emerald',
                                        'bg-slate-900 text-white border-slate-950': theme === 'navy',
                                        'bg-slate-600 text-white border-slate-700': theme === 'slate'
                                    }">No</th>
                                    <th x-show="columns.siswa" class="p-3 border border-slate-200" :class="{
                                        'bg-emerald-600 text-white border-emerald-700': theme === 'emerald',
                                        'bg-slate-900 text-white border-slate-950': theme === 'navy',
                                        'bg-slate-600 text-white border-slate-700': theme === 'slate'
                                    }">Siswa</th>
                                    <th x-show="columns.kategori" class="p-3 border border-slate-200" :class="{
                                        'bg-emerald-600 text-white border-emerald-700': theme === 'emerald',
                                        'bg-slate-900 text-white border-slate-950': theme === 'navy',
                                        'bg-slate-600 text-white border-slate-700': theme === 'slate'
                                    }">Kategori</th>
                                    <th x-show="columns.tagihan" class="p-3 border border-slate-200 text-right" :class="{
                                        'bg-emerald-600 text-white border-emerald-700': theme === 'emerald',
                                        'bg-slate-900 text-white border-slate-950': theme === 'navy',
                                        'bg-slate-600 text-white border-slate-700': theme === 'slate'
                                    }">Total Tagihan</th>
                                    <th x-show="columns.hutang" class="p-3 border border-slate-200 text-right" :class="{
                                        'bg-emerald-600 text-white border-emerald-700': theme === 'emerald',
                                        'bg-slate-900 text-white border-slate-950': theme === 'navy',
                                        'bg-slate-600 text-white border-slate-700': theme === 'slate'
                                    }">Sisa Hutang</th>
                                    <th x-show="columns.status" class="p-3 border border-slate-200 text-center" :class="{
                                        'bg-emerald-600 text-white border-emerald-700': theme === 'emerald',
                                        'bg-slate-900 text-white border-slate-950': theme === 'navy',
                                        'bg-slate-600 text-white border-slate-700': theme === 'slate'
                                    }">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(b, idx) in billings" :key="b.id">
                                    <tr class="hover:bg-slate-50/50 transition-colors border-b border-slate-200 text-slate-800">
                                        <td x-show="columns.no" class="p-3 border border-slate-200 text-center font-bold" x-text="idx + 1"></td>
                                        <td x-show="columns.siswa" class="p-3 border border-slate-200">
                                            <p class="font-black text-slate-900" x-text="b.student?.user?.name"></p>
                                            <p class="text-[9px] text-slate-400 font-bold" x-text="b.student?.nis + ' • ' + (b.student?.classroom?.name || 'N/A')"></p>
                                        </td>
                                        <td x-show="columns.kategori" class="p-3 border border-slate-200">
                                            <span class="inline-block text-[10px] font-bold text-slate-600" x-text="b.category?.name + (b.month ? ' - ' + b.month : '')"></span>
                                        </td>
                                        <td x-show="columns.tagihan" class="p-3 border border-slate-200 text-right font-bold text-slate-700" x-text="formatCurrency(b.amount)"></td>
                                        <td x-show="columns.hutang" class="p-3 border border-slate-200 text-right font-black" :class="parseFloat(b.remaining_balance) > 0 ? 'text-red-600' : 'text-emerald-700'" x-text="formatCurrency(b.remaining_balance)"></td>
                                        <td x-show="columns.status" class="p-3 border border-slate-200 text-center">
                                            <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider"
                                                  :class="{
                                                    'bg-emerald-100 text-emerald-800': b.status === 'paid',
                                                    'bg-amber-100 text-amber-800': b.status === 'partial',
                                                    'bg-red-100 text-red-800': b.status === 'unpaid'
                                                  }"
                                                  x-text="b.status === 'paid' ? 'Lunas' : (b.status === 'partial' ? 'Cicilan' : 'Belum Bayar')">
                                            </span>
                                        </td>
                                    </tr>
                                </template>
                                
                                <!-- Total Row Summary -->
                                <tr class="bg-slate-50 font-black text-slate-900 border-t-2 border-slate-400">
                                    <td :colspan="(columns.no ? 1 : 0) + (columns.siswa ? 1 : 0) + (columns.kategori ? 1 : 0)" class="p-3 text-right">TOTAL LAPORAN:</td>
                                    <td x-show="columns.tagihan" class="p-3 text-right text-slate-800" x-text="formatCurrency(billings.reduce((sum, b) => sum + parseFloat(b.amount), 0))"></td>
                                    <td x-show="columns.hutang" class="p-3 text-right text-red-600" x-text="formatCurrency(billings.reduce((sum, b) => sum + parseFloat(b.remaining_balance), 0))"></td>
                                    <td x-show="columns.status" class="p-3"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Signature Section -->
                    <div x-show="showSignature" class="flex justify-end mt-16" x-transition>
                        <div class="w-64 text-center text-xs">
                            <p class="text-slate-500 font-bold uppercase tracking-tight text-[9px]" x-text="'Semarang, ' + new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })"></p>
                            <p class="font-black text-slate-900 mt-1 uppercase" x-text="signatureRole"></p>
                            <div class="h-20 flex items-center justify-center my-2">
                                <div class="w-32 border-b border-dashed border-slate-300"></div>
                            </div>
                            <p class="font-black text-slate-950 underline uppercase" x-text="signatureName"></p>
                            <p class="text-[9px] text-slate-400 font-bold mt-0.5 uppercase tracking-wide">{{ mb_strtoupper(school_short_name()) }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<style>
    @media print {
        /* Hide absolutely everything in body */
        body * {
            visibility: hidden !important;
        }
        /* Show only our printable paper container and its children */
        #printable-paper, #printable-paper * {
            visibility: visible !important;
        }
        /* Style A4 sheet perfectly for standard printer paper */
        #printable-paper {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 210mm !important;
            height: auto !important;
            min-height: 297mm !important;
            box-shadow: none !important;
            border: none !important;
            padding: 20mm !important;
            background: white !important;
            color: black !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            margin: 0 !important;
            transform: none !important;
        }
        .print-landscape {
            width: 297mm !important;
            min-height: 210mm !important;
        }
        /* Chrome/Edge styling tweaks */
        th {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        @page {
            size: auto;
            margin: 10mm;
        }
    }
</style>
