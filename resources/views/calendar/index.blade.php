<x-app-layout>
    <x-slot name="title">Kalender Akademik</x-slot>

    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
            📅 Kalender & Agenda Akademik Terpadu
        </h2>
    </x-slot>

    <div class="grid grid-cols-1 xl:grid-cols-4 gap-8" x-data="calendarApp()">
        <!-- Sidebar Filter & Legenda -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl flex flex-col justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Opsi Filter</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Tipe Acara</label>
                        <select @change="filterType = $event.target.value; reloadEvents()" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2 text-sm text-gray-800 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Semua Tipe</option>
                            <option value="exam">Ujian / Evaluasi</option>
                            <option value="holiday">Hari Libur</option>
                            <option value="meeting">Rapat Guru / Staff</option>
                            <option value="ceremony">Upacara / Seremoni</option>
                            <option value="tryout">Tryout Mandiri</option>
                            <option value="osis">Kegiatan OSIS</option>
                            <option value="other">Lain-lain</option>
                        </select>
                    </div>

                    @if(auth()->user()->isAdmin())
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Kelas Target</label>
                        <select @change="filterClass = $event.target.value; reloadEvents()" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2 text-sm text-gray-800 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Semua Kelas</option>
                            @foreach($classrooms as $classroom)
                                <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>

                <div class="mt-8">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">Legenda Warna</h4>
                    <div class="space-y-2">
                        <div class="flex items-center text-xs font-semibold text-gray-700 dark:text-gray-300">
                            <span class="w-3.5 h-3.5 rounded-md bg-[#ef4444] mr-3 shrink-0"></span> Ujian / Evaluasi
                        </div>
                        <div class="flex items-center text-xs font-semibold text-gray-700 dark:text-gray-300">
                            <span class="w-3.5 h-3.5 rounded-md bg-[#10b981] mr-3 shrink-0"></span> Hari Libur
                        </div>
                        <div class="flex items-center text-xs font-semibold text-gray-700 dark:text-gray-300">
                            <span class="w-3.5 h-3.5 rounded-md bg-[#f59e0b] mr-3 shrink-0"></span> Rapat Guru / Staff
                        </div>
                        <div class="flex items-center text-xs font-semibold text-gray-700 dark:text-gray-300">
                            <span class="w-3.5 h-3.5 rounded-md bg-[#6366f1] mr-3 shrink-0"></span> Upacara / Seremoni
                        </div>
                        <div class="flex items-center text-xs font-semibold text-gray-700 dark:text-gray-300">
                            <span class="w-3.5 h-3.5 rounded-md bg-[#8b5cf6] mr-3 shrink-0"></span> Tryout Mandiri
                        </div>
                        <div class="flex items-center text-xs font-semibold text-gray-700 dark:text-gray-300">
                            <span class="w-3.5 h-3.5 rounded-md bg-[#ec4899] mr-3 shrink-0"></span> Kegiatan OSIS
                        </div>
                        <div class="flex items-center text-xs font-semibold text-gray-700 dark:text-gray-300">
                            <span class="w-3.5 h-3.5 rounded-md bg-[#3b82f6] mr-3 shrink-0"></span> Lain-lain
                        </div>
                    </div>
                </div>
            </div>

            @if(auth()->user()->isAdmin())
            <div class="mt-8 pt-6 border-t border-gray-200/50 dark:border-gray-700/50">
                <button @click="openCreateModal()" class="w-full bg-primary-600 hover:bg-primary-700 text-white rounded-xl py-3 px-4 text-sm font-black uppercase tracking-wider shadow-lg shadow-primary-500/20 transition-all flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Buat Agenda</span>
                </button>
            </div>
            @endif
        </div>

        <!-- Kalender Interaktif -->
        <div class="xl:col-span-3 bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl min-h-[600px]">
            <div id="calendar-el" class="h-full"></div>
        </div>

        <!-- Create & Edit Modal -->
        <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/50 backdrop-blur-sm" style="display: none;" @keydown.escape.window="modalOpen = false">
            <div class="bg-white dark:bg-gray-800 w-full max-w-lg rounded-3xl border border-gray-200 dark:border-gray-700 shadow-2xl p-8 transform transition-all" @click.away="modalOpen = false">
                <h3 class="text-xl font-extrabold text-gray-900 dark:text-white mb-6" x-text="editMode ? 'Edit Agenda Akademik' : 'Buat Agenda Akademik Baru'"></h3>
                
                <form @submit.prevent="saveEvent()" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">Judul Agenda</label>
                        <input type="text" x-model="formData.title" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500" placeholder="Contoh: Penilaian Akhir Semester (PAS)">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">Deskripsi / Detail Acara</label>
                        <textarea x-model="formData.description" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500" rows="3" placeholder="Tambahkan rincian/keterangan acara..."></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">Tipe Acara</label>
                            <select x-model="formData.event_type" required @change="updateDefaultColor()" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2 text-sm text-gray-800 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500">
                                <option value="exam">Ujian / Evaluasi</option>
                                <option value="holiday">Hari Libur</option>
                                <option value="meeting">Rapat Guru / Staff</option>
                                <option value="ceremony">Upacara / Seremoni</option>
                                <option value="tryout">Tryout Mandiri</option>
                                <option value="osis">Kegiatan OSIS</option>
                                <option value="other">Lain-lain</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">Warna Acara</label>
                            <div class="flex items-center space-x-2">
                                <input type="color" x-model="formData.color" class="w-12 h-10 bg-transparent border-0 cursor-pointer rounded-xl">
                                <span class="text-xs text-gray-500 uppercase font-mono font-bold" x-text="formData.color"></span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">Mulai Tanggal</label>
                            <input type="datetime-local" x-model="formData.start_date" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">Selesai Tanggal</label>
                            <input type="datetime-local" x-model="formData.end_date" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>

                    <div class="flex items-center space-x-4 py-2 border-y border-gray-100 dark:border-gray-700">
                        <label class="flex items-center text-xs font-bold uppercase tracking-wider text-gray-500 cursor-pointer">
                            <input type="checkbox" x-model="formData.is_all_day" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                            Sepanjang Hari (All Day)
                        </label>
                        
                        <label class="flex items-center text-xs font-bold uppercase tracking-wider text-gray-500 cursor-pointer" x-show="!editMode">
                            <input type="checkbox" x-model="formData.send_notification" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                            Kirim Notifikasi WA (H-1)
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">Target Peran (Role)</label>
                            <select x-model="formData.target_role" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2 text-sm text-gray-800 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500">
                                <option value="">Semua Role (Publik)</option>
                                <option value="student">Siswa</option>
                                <option value="parent">Orang Tua Wali</option>
                                <option value="guru">Guru</option>
                                <option value="staf_tu">Staf TU</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">Khusus Kelas</label>
                            <select x-model="formData.target_classroom_id" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2 text-sm text-gray-800 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500">
                                <option value="">Semua Kelas</option>
                                @foreach($classrooms as $classroom)
                                    <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-between space-x-3 pt-6">
                        <div>
                            <button type="button" x-show="editMode" @click="deleteEvent()" class="bg-red-100 hover:bg-red-200 text-red-600 dark:bg-red-950/30 dark:text-red-400 border border-red-200 dark:border-red-900/50 px-4 py-2.5 rounded-xl text-sm font-bold uppercase tracking-wider transition-all">
                                Hapus
                            </button>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button type="button" @click="modalOpen = false" class="bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-5 py-2.5 rounded-xl text-sm font-bold uppercase tracking-wider transition-all">
                                Batal
                            </button>
                            <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2.5 rounded-xl text-sm font-black uppercase tracking-wider shadow-lg shadow-primary-500/20 transition-all">
                                Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Detail Viewer Modal -->
        <div x-show="detailOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/50 backdrop-blur-sm" style="display: none;" @keydown.escape.window="detailOpen = false">
            <div class="bg-white dark:bg-gray-800 w-full max-w-md rounded-3xl border border-gray-200 dark:border-gray-700 shadow-2xl p-8 transform transition-all" @click.away="detailOpen = false">
                <div class="flex items-center justify-between mb-4">
                    <span class="px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider" :style="{ backgroundColor: activeEvent.color + '20', color: activeEvent.color }" x-text="formatType(activeEvent.extendedProps?.event_type)"></span>
                    <button @click="detailOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <h3 class="text-xl font-extrabold text-gray-900 dark:text-white mb-2" x-text="activeEvent.title"></h3>
                
                <div class="flex items-center text-xs font-bold text-gray-500 mb-6">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span x-text="formatDateRange(activeEvent.start, activeEvent.end, activeEvent.allDay)"></span>
                </div>

                <div class="space-y-4 text-sm text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/50 p-5 rounded-2xl border border-gray-100 dark:border-gray-700/50 mb-6">
                    <div>
                        <span class="block text-[10px] font-black uppercase tracking-wider text-gray-400">Deskripsi</span>
                        <p class="font-medium mt-1 whitespace-pre-line" x-text="activeEvent.extendedProps?.description || 'Tidak ada deskripsi.'"></p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-3 border-t border-gray-200/50 dark:border-gray-700/50">
                        <div>
                            <span class="block text-[10px] font-black uppercase tracking-wider text-gray-400">Kelas Sasaran</span>
                            <span class="font-bold text-gray-900 dark:text-white mt-1 block" x-text="activeEvent.extendedProps?.classroom"></span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-black uppercase tracking-wider text-gray-400">Penerbit Agenda</span>
                            <span class="font-bold text-gray-900 dark:text-white mt-1 block" x-text="activeEvent.extendedProps?.creator"></span>
                        </div>
                    </div>
                </div>

                @if(auth()->user()->isAdmin())
                <div class="flex space-x-3">
                    <button @click="openEditModal()" class="w-full bg-primary-600 hover:bg-primary-700 text-white rounded-xl py-3 px-4 text-xs font-black uppercase tracking-wider shadow-lg shadow-primary-500/20 transition-all">
                        Ubah Agenda
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Scripts CDN -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales/id.global.min.js"></script>

    <script>
        function calendarApp() {
            return {
                modalOpen: false,
                detailOpen: false,
                editMode: false,
                filterType: '',
                filterClass: '',
                calendar: null,
                activeEvent: {},
                formData: {
                    id: null,
                    title: '',
                    description: '',
                    event_type: 'other',
                    color: '#3b82f6',
                    start_date: '',
                    end_date: '',
                    is_all_day: false,
                    target_role: '',
                    target_classroom_id: '',
                    send_notification: false
                },

                init() {
                    const calendarEl = document.getElementById('calendar-el');
                    this.calendar = new FullCalendar.Calendar(calendarEl, {
                        locale: 'id',
                        initialView: 'dayGridMonth',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,timeGridDay'
                        },
                        themeSystem: 'standard',
                        events: (info, successCallback, failureCallback) => {
                            let url = `/api/academic-events?start=${info.startStr}&end=${info.endStr}`;
                            if (this.filterType) url += `&type=${this.filterType}`;
                            if (this.filterClass) url += `&classroom_id=${this.filterClass}`;
                            
                            fetch(url)
                                .then(res => res.json())
                                .then(data => successCallback(data))
                                .catch(err => {
                                    window.showToast?.('error', 'Gagal memuat agenda kalender.');
                                    failureCallback(err);
                                });
                        },
                        dateClick: (info) => {
                            @if(auth()->user()->isAdmin())
                                this.openCreateModal(info.dateStr);
                            @endif
                        },
                        eventClick: (info) => {
                            this.activeEvent = info.event;
                            this.detailOpen = true;
                        }
                    });
                    this.calendar.render();
                },

                reloadEvents() {
                    this.calendar.refetchEvents();
                },

                formatType(type) {
                    const mapping = {
                        exam: 'Ujian / Evaluasi',
                        holiday: 'Hari Libur',
                        meeting: 'Rapat Guru / Staff',
                        ceremony: 'Upacara / Seremoni',
                        tryout: 'Tryout Mandiri',
                        osis: 'Kegiatan OSIS',
                        other: 'Lain-lain'
                    };
                    return mapping[type] || 'Agenda';
                },

                formatDateRange(start, end, allDay) {
                    if (!start) return '';
                    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
                    const startDate = new Date(start);
                    
                    if (allDay) {
                        const allDayOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                        if (!end) return startDate.toLocaleDateString('id-ID', allDayOptions);
                        const endDate = new Date(end);
                        // FullCalendar ends are exclusive for all-day events, subtract 1 day for presentation
                        endDate.setDate(endDate.getDate() - 1);
                        return `${startDate.toLocaleDateString('id-ID', allDayOptions)} s/d ${endDate.toLocaleDateString('id-ID', allDayOptions)}`;
                    }

                    if (!end) return startDate.toLocaleString('id-ID', options);
                    const endDate = new Date(end);
                    return `${startDate.toLocaleString('id-ID', options)} s/d ${endDate.toLocaleString('id-ID', options)}`;
                },

                updateDefaultColor() {
                    const colorMapping = {
                        exam: '#ef4444',
                        holiday: '#10b981',
                        meeting: '#f59e0b',
                        ceremony: '#6366f1',
                        tryout: '#8b5cf6',
                        osis: '#ec4899',
                        other: '#3b82f6'
                    };
                    this.formData.color = colorMapping[this.formData.event_type] || '#3b82f6';
                },

                openCreateModal(dateStr = '') {
                    this.editMode = false;
                    this.formData = {
                        id: null,
                        title: '',
                        description: '',
                        event_type: 'other',
                        color: '#3b82f6',
                        start_date: dateStr ? `${dateStr}T07:00` : '',
                        end_date: dateStr ? `${dateStr}T17:00` : '',
                        is_all_day: false,
                        target_role: '',
                        target_classroom_id: '',
                        send_notification: false
                    };
                    this.modalOpen = true;
                },

                openEditModal() {
                    this.editMode = true;
                    this.detailOpen = false;
                    
                    const event = this.activeEvent;
                    const formatDatetimeLocal = (date) => {
                        if (!date) return '';
                        const d = new Date(date);
                        return new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
                    };

                    this.formData = {
                        id: event.id,
                        title: event.title,
                        description: event.extendedProps.description,
                        event_type: event.extendedProps.event_type,
                        color: event.backgroundColor || event.color || '#3b82f6',
                        start_date: formatDatetimeLocal(event.start),
                        end_date: formatDatetimeLocal(event.end),
                        is_all_day: event.allDay,
                        target_role: event.extendedProps.target_role || '',
                        target_classroom_id: event.extendedProps.classroom === 'Semua Kelas' ? '' : ClassroomIdFromName(event.extendedProps.classroom)
                    };
                    this.modalOpen = true;
                },

                saveEvent() {
                    const method = this.editMode ? 'PUT' : 'POST';
                    const url = this.editMode ? `/calendar/events/${this.formData.id}` : '/calendar/events';
                    
                    fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(this.formData)
                    })
                    .then(async res => {
                        const data = await res.json();
                        if (res.ok) {
                            window.showToast?.('success', data.message);
                            this.modalOpen = false;
                            this.reloadEvents();
                        } else {
                            window.showToast?.('error', data.message || 'Terjadi kesalahan.');
                        }
                    })
                    .catch(() => {
                        window.showToast?.('error', 'Gagal memproses koneksi.');
                    });
                },

                deleteEvent() {
                    if (!confirm('Apakah Anda yakin ingin menghapus agenda akademik ini?')) return;
                    
                    fetch(`/calendar/events/${this.formData.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(async res => {
                        const data = await res.json();
                        if (res.ok) {
                            window.showToast?.('success', data.message);
                            this.modalOpen = false;
                            this.reloadEvents();
                        } else {
                            window.showToast?.('error', data.message);
                        }
                    })
                    .catch(() => {
                        window.showToast?.('error', 'Gagal memproses penghapusan.');
                    });
                }
            };
        }

        // Helper map classroom name to ID (since fullcalendar payload returned name string)
        function ClassroomIdFromName(name) {
            const select = document.querySelector('select[x-model="formData.target_classroom_id"]');
            if (!select) return '';
            for (let option of select.options) {
                if (option.text === name) return option.value;
            }
            return '';
        }
    </script>
</x-app-layout>
