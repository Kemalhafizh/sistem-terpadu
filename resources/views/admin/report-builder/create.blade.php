<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-200 leading-tight">
                <i class="fa-solid fa-wand-magic-sparkles text-indigo-400 mr-2"></i> {{ __('Buat Laporan Baru') }}
            </h2>
            <a href="{{ route('admin.report-builder.index') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 text-sm font-medium rounded-lg transition-all duration-200 shadow-md flex items-center">
                <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">

            <!-- Validation Errors -->
            <div id="error-alert-panel" class="hidden p-4 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-400 text-sm flex items-center">
                <i class="fa-solid fa-triangle-exclamation mr-2 text-lg"></i>
                <span id="error-alert-text">Terjadi kesalahan saat memproses data.</span>
            </div>

            <!-- Interaktif Pembuat Laporan -->
            <form action="{{ route('admin.report-builder.store') }}" method="POST" id="report-builder-form" class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                @csrf
                
                <!-- Left Panel: Configurations -->
                <div class="lg:col-span-1 bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 shadow-xl space-y-6">
                    <h3 class="text-md font-semibold text-slate-200 flex items-center border-b border-slate-700/50 pb-3">
                        <i class="fa-solid fa-sliders text-indigo-400 mr-2"></i> Pengaturan Sumber & Filter
                    </h3>

                    <!-- 1. Source Selection -->
                    <div class="space-y-2">
                        <label for="select-source" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Pilih Sumber Data</label>
                        <select name="source" id="select-source" class="w-full bg-slate-900 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-200 focus:border-indigo-500 focus:ring-indigo-500/20 text-sm" required>
                            @foreach($sources as $key => $source)
                                <option value="{{ $key }}">{{ $source['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 2. Dynamic Filters Container -->
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Filter Data (Opsional)</label>
                            <button type="button" id="btn-add-filter" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold flex items-center transition-colors">
                                <i class="fa-solid fa-circle-plus mr-1"></i> Tambah Filter
                            </button>
                        </div>
                        
                        <div id="filters-wrapper" class="space-y-3">
                            <!-- Filters will be generated dynamically here -->
                        </div>
                    </div>

                    <!-- 3. Sort and Visualization Options -->
                    <div class="space-y-4 border-t border-slate-700/50 pt-4">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Visualisasi & Urutan</h4>
                        
                        <!-- Order By -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="select-orderby" class="block text-xs text-slate-400 mb-1">Urutkan Berdasarkan</label>
                                <select name="orderBy" id="select-orderby" class="w-full bg-slate-900 border border-slate-700/60 rounded-xl px-3 py-2 text-slate-200 focus:border-indigo-500 focus:ring-indigo-500/20 text-xs">
                                    <option value="">Default (Tidak Ada)</option>
                                </select>
                            </div>
                            <div>
                                <label for="select-orderdir" class="block text-xs text-slate-400 mb-1">Arah Urutan</label>
                                <select name="orderDir" id="select-orderdir" class="w-full bg-slate-900 border border-slate-700/60 rounded-xl px-3 py-2 text-slate-200 focus:border-indigo-500 focus:ring-indigo-500/20 text-xs">
                                    <option value="asc">Menaik (ASC)</option>
                                    <option value="desc">Menurun (DESC)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Chart/Visualization Type -->
                        <div class="space-y-2">
                            <label for="select-charttype" class="block text-xs text-slate-400">Tipe Visualisasi Dasbor</label>
                            <select name="chartType" id="select-charttype" class="w-full bg-slate-900 border border-slate-700/60 rounded-xl px-4 py-2 text-slate-200 focus:border-indigo-500 focus:ring-indigo-500/20 text-sm">
                                <option value="table">Tabel Data Mentah saja </option>
                                <option value="bar">Grafik Batang (Bar Chart) </option>
                                <option value="line">Grafik Garis (Line Chart) </option>
                                <option value="pie">Grafik Lingkaran (Pie Chart) </option>
                                <option value="donut">Grafik Donut (Donut Chart) </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Right Panel: Column Selection & Preview -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- A. Column Selection Grid -->
                    <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 shadow-xl">
                        <h3 class="text-md font-semibold text-slate-200 flex items-center border-b border-slate-700/50 pb-3">
                            <i class="fa-solid fa-list-check text-indigo-400 mr-2"></i> Pilih Kolom untuk Ditampilkan
                        </h3>
                        
                        <div id="columns-selection-grid" class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-4">
                            <!-- Columns will render dynamically -->
                        </div>
                    </div>

                    <!-- B. Live Preview & Save Panel -->
                    <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 shadow-xl space-y-6">
                        <div class="flex justify-between items-center border-b border-slate-700/50 pb-3">
                            <h3 class="text-md font-semibold text-slate-200 flex items-center">
                                <i class="fa-solid fa-magnifying-glass text-indigo-400 mr-2"></i> Preview Laporan (Maks. 10 Baris)
                            </h3>
                            <button type="button" id="btn-trigger-preview" class="px-3.5 py-1.5 bg-indigo-500/10 hover:bg-indigo-600 border border-indigo-500/25 text-indigo-300 hover:text-white text-xs font-semibold rounded-lg transition-all duration-200 flex items-center">
                                <i class="fa-solid fa-circle-play mr-1.5"></i> Muat Preview Live
                            </button>
                        </div>

                        <!-- Preview Render Container -->
                        <div class="overflow-x-auto rounded-xl border border-slate-700/50 min-h-[120px] bg-slate-900/20 flex flex-col justify-center">
                            <div id="preview-placeholder" class="text-center text-slate-500 py-8">
                                <i class="fa-solid fa-table-list text-3xl text-slate-700 mb-2 block"></i>
                                <p class="text-sm">Klik tombol "Muat Preview Live" untuk melihat simulasi data laporan kustom.</p>
                            </div>
                            
                            <table id="preview-table" class="min-w-full divide-y divide-slate-700/50 hidden">
                                <thead id="preview-thead" class="bg-slate-900/50">
                                    <!-- Rendered dynamically -->
                                </thead>
                                <tbody id="preview-tbody" class="divide-y divide-slate-700/50 bg-slate-800/20 text-slate-300 text-sm">
                                    <!-- Rendered dynamically -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Final Save Action Details -->
                        <div class="border-t border-slate-700/50 pt-6 space-y-4">
                            <h4 class="text-md font-semibold text-slate-200 flex items-center">
                                <i class="fa-solid fa-floppy-disk text-indigo-400 mr-2"></i> Simpan Konfigurasi Template
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label for="input-name" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Nama Laporan kustom</label>
                                    <input type="text" name="name" id="input-name" placeholder="Contoh: Laporan Tunggakan SPP Kelas X" class="w-full bg-slate-900 border border-slate-700/60 rounded-xl px-4 py-2.5 text-slate-200 focus:border-indigo-500 focus:ring-indigo-500/20 text-sm" required>
                                </div>
                                <div class="space-y-2">
                                    <label for="input-description" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Deskripsi Singkat</label>
                                    <input type="text" name="description" id="input-description" placeholder="Menampilkan data tunggakan SPP siswa kelas X" class="w-full bg-slate-900 border border-slate-700/60 rounded-xl px-4 py-2.5 text-slate-200 focus:border-indigo-500 focus:ring-indigo-500/20 text-sm">
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-2">
                                <label class="flex items-center space-x-3 cursor-pointer select-none">
                                    <input type="checkbox" name="is_shared" id="checkbox-shared" value="1" class="rounded border-slate-700 bg-slate-900 text-indigo-600 focus:ring-indigo-500/20 focus:ring-offset-slate-900 h-4 w-4">
                                    <span class="text-sm text-slate-300">Bagikan template laporan kustom ini dengan staf berwenang lainnya (*Shared Template*)</span>
                                </label>

                                <button type="submit" id="btn-save-report" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/20 flex items-center">
                                    <i class="fa-solid fa-circle-check mr-2 text-md"></i> Simpan Template
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </form>

        </div>
    </div>

    <!-- Interactive Javascript Logic -->
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sources = @json($sources);
            const selectSource = document.getElementById('select-source');
            const columnsGrid = document.getElementById('columns-selection-grid');
            const orderBySelect = document.getElementById('select-orderby');
            const filtersWrapper = document.getElementById('filters-wrapper');
            const btnAddFilter = document.getElementById('btn-add-filter');
            const btnTriggerPreview = document.getElementById('btn-trigger-preview');
            
            const placeholder = document.getElementById('preview-placeholder');
            const table = document.getElementById('preview-table');
            const thead = document.getElementById('preview-thead');
            const tbody = document.getElementById('preview-tbody');
            
            const errorAlertPanel = document.getElementById('error-alert-panel');
            const errorAlertText = document.getElementById('error-alert-text');

            // Render columns and order options dynamically based on chosen source
            function handleSourceChange() {
                const sourceKey = selectSource.value;
                const sourceConfig = sources[sourceKey];
                
                columnsGrid.innerHTML = '';
                orderBySelect.innerHTML = '<option value="">Default (Tidak Ada)</option>';
                filtersWrapper.innerHTML = ''; // reset filters

                if (!sourceConfig) return;

                // 1. Render Column Checkboxes
                Object.entries(sourceConfig.columns).forEach(([key, config]) => {
                    const colDiv = document.createElement('div');
                    colDiv.className = 'bg-slate-900/50 border border-slate-700/40 hover:border-slate-600 rounded-xl p-3 flex items-center space-x-3 cursor-pointer select-none transition-colors';
                    
                    const checkedState = ['nis', 'name', 'classroom_name', 'student_name', 'amount', 'category_name', 'date', 'status', 'subject_name', 'score'].includes(key) ? 'checked' : '';
                    
                    colDiv.innerHTML = `
                        <input type="checkbox" name="columns[]" id="col-${key}" value="${key}" ${checkedState} class="rounded border-slate-700 bg-slate-900 text-indigo-600 focus:ring-indigo-500/20 focus:ring-offset-slate-900 h-4 w-4 column-checkbox">
                        <label for="col-${key}" class="text-sm text-slate-300 font-medium cursor-pointer flex-1">${config.label}</label>
                    `;
                    
                    // Click div triggers check
                    colDiv.addEventListener('click', function(e) {
                        if(e.target.tagName !== 'INPUT' && e.target.tagName !== 'LABEL') {
                            const chk = colDiv.querySelector('input');
                            chk.checked = !chk.checked;
                            handleCheckStateChange(key, chk.checked, config.label);
                        }
                    });
                    
                    columnsGrid.appendChild(colDiv);

                    // Add change listener to select option
                    colDiv.querySelector('input').addEventListener('change', function() {
                        handleCheckStateChange(key, this.checked, config.label);
                    });

                    // Add default checked columns to Order By list
                    if (checkedState) {
                        addOrderByOption(key, config.label);
                    }
                });
            }

            function handleCheckStateChange(key, checked, label) {
                if (checked) {
                    addOrderByOption(key, label);
                } else {
                    removeOrderByOption(key);
                }
            }

            function addOrderByOption(key, label) {
                const opt = document.createElement('option');
                opt.value = key;
                opt.id = `order-opt-${key}`;
                opt.textContent = label;
                orderBySelect.appendChild(opt);
            }

            function removeOrderByOption(key) {
                const opt = document.getElementById(`order-opt-${key}`);
                if (opt) opt.remove();
            }

            // Add dynamic filter row
            function addFilterRow() {
                const sourceKey = selectSource.value;
                const sourceConfig = sources[sourceKey];
                if (!sourceConfig) return;

                const index = filtersWrapper.children.length;
                const row = document.createElement('div');
                row.className = 'grid grid-cols-12 gap-2 bg-slate-900/40 p-3 rounded-xl border border-slate-700/30 items-center relative group';
                
                let optionsHtml = '';
                Object.entries(sourceConfig.columns).forEach(([key, config]) => {
                    optionsHtml += `<option value="${key}">${config.label}</option>`;
                });

                row.innerHTML = `
                    <div class="col-span-5 md:col-span-4">
                        <select name="filters[${index}][column]" class="w-full bg-slate-950 border border-slate-700/60 rounded-lg px-2 py-1 text-xs text-slate-200 focus:ring-indigo-500/20">
                            ${optionsHtml}
                        </select>
                    </div>
                    <div class="col-span-3 md:col-span-3">
                        <select name="filters[${index}][operator]" class="w-full bg-slate-950 border border-slate-700/60 rounded-lg px-2 py-1 text-xs text-slate-200 focus:ring-indigo-500/20">
                            <option value="=">Sama dengan (=)</option>
                            <option value="!=">Beda dengan (!=)</option>
                            <option value=">">Lebih dari (&gt;)</option>
                            <option value="<">Kurang dari (&lt;)</option>
                            <option value="like">Mengandung kata (LIKE)</option>
                            <option value="in">Grup data (IN - pisah koma)</option>
                        </select>
                    </div>
                    <div class="col-span-3 md:col-span-4">
                        <input type="text" name="filters[${index}][value]" placeholder="Nilai..." class="w-full bg-slate-950 border border-slate-700/60 rounded-lg px-2 py-1 text-xs text-slate-200 focus:ring-indigo-500/20">
                    </div>
                    <div class="col-span-1 text-right">
                        <button type="button" class="text-rose-400 hover:text-rose-300 transition-colors btn-remove-filter" title="Hapus Filter">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </button>
                    </div>
                `;

                // Remove logic
                row.querySelector('.btn-remove-filter').addEventListener('click', function() {
                    row.remove();
                });

                filtersWrapper.appendChild(row);
            }

            selectSource.addEventListener('change', handleSourceChange);
            btnAddFilter.addEventListener('click', addFilterRow);

            // Execute Live Preview via AJAX
            btnTriggerPreview.addEventListener('click', function () {
                // Collect checked columns
                const checkedCols = Array.from(document.querySelectorAll('.column-checkbox:checked')).map(el => el.value);
                if (checkedCols.length === 0) {
                    showError("Silakan pilih minimal 1 kolom untuk memuat preview laporan.");
                    return;
                }

                // Compile filters payload
                const filters = [];
                const rows = filtersWrapper.children;
                for (let i = 0; i < rows.length; i++) {
                    const row = rows[i];
                    const col = row.querySelector('select[name^="filters"][name$="[column]"]').value;
                    const op = row.querySelector('select[name^="filters"][name$="[operator]"]').value;
                    const val = row.querySelector('input[name^="filters"][name$="[value]"]').value;
                    filters.push({ column: col, operator: op, value: val });
                }

                // Show loading state
                hideError();
                btnTriggerPreview.disabled = true;
                btnTriggerPreview.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i> Memuat...';

                placeholder.classList.add('hidden');
                table.classList.add('hidden');

                // Shoot POST request
                fetch("{{ route('admin.report-builder.preview') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({
                        source: selectSource.value,
                        columns: checkedCols,
                        filters: filters,
                        orderBy: orderBySelect.value,
                        orderDir: document.getElementById('select-orderdir').value
                    })
                })
                .then(res => res.json())
                .then(res => {
                    btnTriggerPreview.disabled = false;
                    btnTriggerPreview.innerHTML = '<i class="fa-solid fa-circle-play mr-1.5"></i> Muat Preview Live';

                    if (!res.success) {
                        showError(res.message || "Gagal memuat preview data.");
                        placeholder.classList.remove('hidden');
                        return;
                    }

                    if (res.data.length === 0) {
                        showError("Tidak ada kecocokan data dengan filter yang Anda pasang.");
                        placeholder.classList.remove('hidden');
                        return;
                    }

                    // Render headers
                    thead.innerHTML = '';
                    const trHead = document.createElement('tr');
                    Object.values(res.headings).forEach(label => {
                        const th = document.createElement('th');
                        th.className = 'px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider';
                        th.textContent = label;
                        trHead.appendChild(th);
                    });
                    thead.appendChild(trHead);

                    // Render rows
                    tbody.innerHTML = '';
                    res.data.forEach(item => {
                        const trRow = document.createElement('tr');
                        trRow.className = 'hover:bg-slate-700/10 transition-colors';
                        
                        Object.keys(res.headings).forEach(colKey => {
                            const td = document.createElement('td');
                            td.className = 'px-6 py-4 whitespace-nowrap text-sm text-slate-300';
                            
                            let value = item[colKey];
                            // Format readable label
                            if (colKey === 'gender') value = value === 'L' ? 'Laki-laki' : 'Perempuan';
                            if (colKey === 'amount' || colKey === 'remaining_balance') {
                                value = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
                            }
                            
                            td.textContent = value ?? '-';
                            trRow.appendChild(td);
                        });
                        tbody.appendChild(trRow);
                    });

                    table.classList.remove('hidden');
                })
                .catch(err => {
                    btnTriggerPreview.disabled = false;
                    btnTriggerPreview.innerHTML = '<i class="fa-solid fa-circle-play mr-1.5"></i> Muat Preview Live';
                    showError("Koneksi gagal atau SQL error. Harap periksa input filter Anda.");
                    placeholder.classList.remove('hidden');
                });
            });

            function showError(msg) {
                errorAlertText.textContent = msg;
                errorAlertPanel.classList.remove('hidden');
                errorAlertPanel.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            function hideError() {
                errorAlertPanel.classList.add('hidden');
            }

            // Init default
            handleSourceChange();
        });
    </script>
    @endpush
</x-app-layout>
