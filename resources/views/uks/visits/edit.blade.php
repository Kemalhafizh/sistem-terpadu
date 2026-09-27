<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('uks.visits.index') }}" class="hover:text-teal-600 transition-colors">
                <span class="w-8 h-8 bg-teal-100 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </span>
            </a>
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
                    {{ __('Edit Kunjungan UKS') }}
                </h2>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wider">{{ mb_strtoupper(school_short_name()) }} • UPDATE MEDIS SISWA</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{
        studentId: '{{ old('student_id', $visit->student_id) }}',
        studentsList: {{ $students->map(function($s) {
            return [
                'id' => $s->id,
                'name' => $s->user->name ?? '',
                'classroom' => $s->classroom->name ?? '-',
                'blood_type' => $s->blood_type ?? '',
                'allergies' => $s->allergies ?? ''
            ];
        })->toJson() }},
        selectedStudent: null,
        bloodType: '{{ old('blood_type', $visit->student->blood_type) }}',
        allergies: '{{ old('allergies', $visit->student->allergies) }}',
        
        // AlpineJS Medicine rows
        rows: {{ Js::from(old('medicines', $visit->medicines->isNotEmpty() ? $visit->medicines->map(fn($m) => ['id' => $m->id, 'quantity' => $m->pivot->quantity]) : [['id' => '', 'quantity' => 1]])) }},
        medicinesList: {{ $medicines->toJson() }},
        
        getAvailableStock(medId) {
            const med = this.medicinesList.find(m => m.id == medId);
            return med ? med.stock : 0;
        },
        updateStudent() {
            this.selectedStudent = this.studentsList.find(s => s.id == this.studentId);
            if (this.selectedStudent) {
                this.bloodType = this.selectedStudent.blood_type || '';
                this.allergies = this.selectedStudent.allergies || '';
            } else {
                this.bloodType = '';
                this.allergies = '';
            }
        },
        init() {
            this.updateStudent();
        }
    }">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            
            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="mb-6 rounded-2xl p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 text-red-800 dark:text-red-300">
                    <div class="font-bold text-sm">Ada kesalahan dalam pengisian form:</div>
                    <ul class="mt-2 list-disc pl-5 text-xs font-semibold space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('uks.visits.update', $visit->id) }}" class="space-y-8">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- LEFT COLUMN: Student Profile & Vital Info (1/3) -->
                    <div class="space-y-6">
                        <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50">
                            <h4 class="text-base font-black text-gray-900 dark:text-white flex items-center mb-6">
                                <span class="w-2 h-5 bg-teal-600 rounded-full mr-3"></span>
                                1. Identitas Siswa Sakit
                            </h4>

                            <div class="space-y-6">
                                <!-- Student Selection -->
                                <div class="space-y-2">
                                    <x-input-label for="student_id" value="Pilih Siswa *" class="text-xs font-black uppercase text-gray-400 dark:text-gray-500 tracking-wider" />
                                    <select name="student_id" id="student_id" x-model="studentId" @change="updateStudent" required
                                            class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">
                                        <option value="">-- Pilih Siswa Aktif --</option>
                                        @foreach($students as $student)
                                            <option value="{{ $student->id }}">
                                                {{ $student->user->name ?? '-' }} ({{ $student->classroom->name ?? 'Tanpa Kelas' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Dynamic Student Meta (Prefilled via Alpine) -->
                                <template x-if="selectedStudent">
                                    <div class="p-4 bg-teal-50/50 dark:bg-teal-950/20 border border-teal-100 dark:border-teal-900/30 rounded-2xl space-y-3">
                                        <div class="flex justify-between items-center text-xs font-bold">
                                            <span class="text-gray-500">Kelas</span>
                                            <span class="text-teal-700 dark:text-teal-400" x-text="selectedStudent.classroom"></span>
                                        </div>
                                        <hr class="border-teal-100 dark:border-teal-900/30">
                                        <p class="text-[10px] font-bold text-teal-600 dark:text-teal-500 uppercase tracking-widest leading-relaxed">
                                            Data golongan darah dan alergi di bawah ini dapat diperbarui dan akan disimpan permanen ke database siswa.
                                        </p>
                                    </div>
                                </template>

                                <!-- Blood Type (Stops to edit) -->
                                <div class="space-y-2">
                                    <x-input-label for="blood_type" value="Golongan Darah" class="text-xs font-black uppercase text-gray-400 dark:text-gray-500 tracking-wider" />
                                    <select name="blood_type" id="blood_type" x-model="bloodType" disabled
                                            class="w-full bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-sm dark:text-gray-500">
                                        <option value="">Belum Diketahui</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="AB">AB</option>
                                        <option value="O">O</option>
                                    </select>
                                </div>

                                <!-- Allergies (Stops to edit) -->
                                <div class="space-y-2">
                                    <x-input-label for="allergies" value="Riwayat Alergi Siswa" class="text-xs font-black uppercase text-gray-400 dark:text-gray-500 tracking-wider" />
                                    <textarea name="allergies" id="allergies" x-model="allergies" rows="3" placeholder="Tulis alergi obat, makanan, dll..." readonly
                                              class="w-full bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm dark:text-gray-500"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Medical Records & Treatment (2/3) -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50 space-y-6">
                            <h4 class="text-base font-black text-gray-900 dark:text-white flex items-center">
                                <span class="w-2 h-5 bg-teal-600 rounded-full mr-3"></span>
                                2. Keluhan & Tindakan Medis
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Visit Time -->
                                <div class="space-y-2">
                                    <x-input-label for="visit_time" value="Waktu Masuk UKS *" class="text-xs font-black uppercase text-gray-400 dark:text-gray-500 tracking-wider" />
                                    <input type="datetime-local" name="visit_time" id="visit_time" required
                                           value="{{ old('visit_time', \Carbon\Carbon::parse($visit->visit_time)->format('Y-m-d\TH:i')) }}"
                                           class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">
                                </div>

                                <!-- Leave Time -->
                                <div class="space-y-2">
                                    <x-input-label for="leave_time" value="Waktu Keluar UKS" class="text-xs font-black uppercase text-gray-400 dark:text-gray-500 tracking-wider" />
                                    <input type="datetime-local" name="leave_time" id="leave_time"
                                           value="{{ old('leave_time', $visit->leave_time ? \Carbon\Carbon::parse($visit->leave_time)->format('Y-m-d\TH:i') : '') }}"
                                           class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">
                                </div>

                                <!-- Severity -->
                                <div class="space-y-2">
                                    <x-input-label for="severity" value="Tingkat Keparahan *" class="text-xs font-black uppercase text-gray-400 dark:text-gray-500 tracking-wider" />
                                    <select name="severity" id="severity" required
                                            class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">
                                        <option value="ringan" {{ old('severity', $visit->severity) === 'ringan' ? 'selected' : '' }}>Ringan (Bisa segera kembali belajar)</option>
                                        <option value="sedang" {{ old('severity', $visit->severity) === 'sedang' ? 'selected' : '' }}>Sedang (Butuh istirahat di UKS)</option>
                                        <option value="berat" {{ old('severity', $visit->severity) === 'berat' ? 'selected' : '' }}>Berat (Pemicu WA Notifikasi Darurat Ortu)</option>
                                    </select>
                                </div>

                                <!-- Disposition -->
                                <div class="space-y-2">
                                    <x-input-label for="disposition" value="Tindakan / Disposisi *" class="text-xs font-black uppercase text-gray-400 dark:text-gray-500 tracking-wider" />
                                    <select name="disposition" id="disposition" required
                                            class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">
                                        <option value="istirahat_uks" {{ old('disposition', $visit->disposition) === 'istirahat_uks' ? 'selected' : '' }}>Istirahat di UKS</option>
                                        <option value="kembali_kelas" {{ old('disposition', $visit->disposition) === 'kembali_kelas' ? 'selected' : '' }}>Kembali ke Kelas</option>
                                        <option value="pulang" {{ old('disposition', $visit->disposition) === 'pulang' ? 'selected' : '' }}>Dipulangkan / Ortu Menjemput (Pemicu WA Ortu)</option>
                                        <option value="rujuk_rs" {{ old('disposition', $visit->disposition) === 'rujuk_rs' ? 'selected' : '' }}>Dirujuk ke Rumah Sakit (Pemicu WA Ortu)</option>
                                    </select>
                                </div>

                                <!-- Complaint -->
                                <div class="md:col-span-2 space-y-2">
                                    <x-input-label for="complaint" value="Keluhan Utama *" class="text-xs font-black uppercase text-gray-400 dark:text-gray-500 tracking-wider" />
                                    <input type="text" name="complaint" id="complaint" required placeholder="Contoh: Sakit perut melilit, Demam tinggi, Pusing"
                                           value="{{ old('complaint', $visit->complaint) }}"
                                           class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">
                                </div>

                                <!-- Diagnosis -->
                                <div class="md:col-span-2 space-y-2">
                                    <x-input-label for="diagnosis" value="Diagnosis Sementara" class="text-xs font-black uppercase text-gray-400 dark:text-gray-500 tracking-wider" />
                                    <textarea name="diagnosis" id="diagnosis" rows="2" placeholder="Contoh: Dispepsia (sakit maag), Kelelahan ekstrem, Febris hari pertama" 
                                              class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">{{ old('diagnosis', $visit->diagnosis) }}</textarea>
                                </div>

                                <!-- Treatment -->
                                <div class="md:col-span-2 space-y-2">
                                    <x-input-label for="treatment" value="Tindakan yang Diberikan" class="text-xs font-black uppercase text-gray-400 dark:text-gray-500 tracking-wider" />
                                    <textarea name="treatment" id="treatment" rows="2" placeholder="Contoh: Kompres dahi dengan es, Istirahat berbaring, Minum teh hangat" 
                                              class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">{{ old('treatment', $visit->treatment) }}</textarea>
                                </div>

                                <!-- Notes -->
                                <div class="md:col-span-2 space-y-2">
                                    <x-input-label for="notes" value="Catatan Tambahan Petugas" class="text-xs font-black uppercase text-gray-400 dark:text-gray-500 tracking-wider" />
                                    <textarea name="notes" id="notes" rows="2" placeholder="Tulis pesan internal atau koordinasi guru piket..." 
                                              class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">{{ old('notes', $visit->notes) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- MEDICINES SECTION: Multi-row selector (AlpineJS) -->
                        <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50 space-y-6">
                            <div class="flex justify-between items-center">
                                <h4 class="text-base font-black text-gray-900 dark:text-white flex items-center">
                                    <span class="w-2 h-5 bg-teal-600 rounded-full mr-3"></span>
                                    3. Pemberian Obat Inventaris (Opsional)
                                </h4>
                                <button type="button" @click="rows.push({id: '', quantity: 1})" class="inline-flex items-center px-3 py-1.5 bg-teal-50 hover:bg-teal-100 dark:bg-teal-950/40 dark:hover:bg-teal-900/60 text-teal-600 dark:text-teal-400 border border-teal-200/50 dark:border-teal-900/30 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Tambah Baris
                                </button>
                            </div>

                            <p class="text-xs text-gray-400 dark:text-gray-500 font-bold leading-relaxed uppercase tracking-wider">
                                Stok obat di inventaris akan berkurang secara otomatis ketika form disimpan.
                            </p>

                            <div class="space-y-3">
                                <template x-for="(row, index) in rows" :key="index">
                                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-850">
                                        
                                        <!-- Medicine dropdown -->
                                        <div class="flex-grow space-y-1">
                                            <select :name="`medicines[${index}][id]`" x-model="row.id"
                                                    class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">
                                                <option value="">-- Pilih Obat --</option>
                                                <template x-for="med in medicinesList" :key="med.id">
                                                    <option :value="med.id" x-text="`${med.name} (Stok: ${med.stock})`"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <!-- Quantity -->
                                        <div class="w-full sm:w-32 flex items-center gap-2">
                                            <input type="number" :name="`medicines[${index}][quantity]`" x-model.number="row.quantity" min="1" placeholder="Qty"
                                                   class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300 text-center">
                                            <span class="text-xs text-gray-400 dark:text-gray-500 font-bold shrink-0 uppercase tracking-widest">Pcs</span>
                                        </div>

                                        <!-- Stock warning indicator -->
                                        <div class="flex items-center gap-2 shrink-0">
                                            <template x-if="row.id && row.quantity > getAvailableStock(row.id)">
                                                <span class="text-[10px] font-black text-red-500 dark:text-red-400 uppercase tracking-wider bg-red-50 dark:bg-red-950/30 border border-red-200/50 px-2 py-1 rounded">Stok Tidak Cukup!</span>
                                            </template>
                                            
                                            <button type="button" @click="rows.splice(index, 1)" class="p-2 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-red-50 dark:hover:bg-red-950/20 hover:border-red-200 dark:hover:border-red-900 text-red-500 transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Form submission bar -->
                        <div class="flex justify-end gap-3 pt-4">
                            <a href="{{ route('uks.visits.index') }}" class="px-6 py-3 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                                Batal
                            </a>
                            <button type="submit" class="px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-teal-600/15">
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
</x-app-layout>
