<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('uks.health-records.index') }}" class="hover:text-teal-600 transition-colors">
                <span class="w-8 h-8 bg-teal-100 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </span>
            </a>
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
                    {{ __('Detail Rekam Medis: ' . $student->user->name) }}
                </h2>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wider">{{ mb_strtoupper(school_short_name()) }} • EDIT KARTU MEDIS</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            
            <form method="POST" action="{{ route('uks.health-records.update') }}" class="space-y-8">
                @csrf
                <input type="hidden" name="student_id" value="{{ $student->id }}">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- LEFT PANEL: Student Profile Summary Card -->
                    <div class="space-y-6">
                        <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50 text-center flex flex-col items-center">
                            <div class="w-24 h-24 bg-gradient-to-br from-teal-500 to-emerald-600 text-white rounded-full flex items-center justify-center font-black text-3xl shadow-lg shadow-teal-500/20 mb-4">
                                {{ substr($student->user->name, 0, 1) }}
                            </div>
                            <h3 class="text-lg font-black text-gray-900 dark:text-white leading-tight">{{ $student->user->name }}</h3>
                            <p class="text-xs text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider mt-1">NIS: {{ $student->nis ?? '-' }} • Kelas: {{ $student->classroom->name ?? '-' }}</p>
                            
                            <div class="w-full grid grid-cols-2 gap-4 mt-6">
                                <div class="p-4 bg-gray-50 dark:bg-gray-900/55 rounded-2xl border border-gray-100 dark:border-gray-800">
                                    <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block">Tinggi Badan</span>
                                    <span class="text-lg font-extrabold text-teal-700 dark:text-teal-400 mt-1 block">{{ $record->height_cm ?? '-' }} <span class="text-xs font-semibold text-gray-400">cm</span></span>
                                </div>
                                <div class="p-4 bg-gray-50 dark:bg-gray-900/55 rounded-2xl border border-gray-100 dark:border-gray-800">
                                    <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block">Berat Badan</span>
                                    <span class="text-lg font-extrabold text-teal-700 dark:text-teal-400 mt-1 block">{{ $record->weight_kg ?? '-' }} <span class="text-xs font-semibold text-gray-400">kg</span></span>
                                </div>
                            </div>

                            <hr class="w-full my-6 border-gray-100 dark:border-gray-700/50">

                            <!-- Emergency Contact -->
                            <div class="w-full text-left space-y-3">
                                <span class="text-[10px] font-black text-gray-500 dark:text-gray-500 uppercase tracking-widest">Kontak Darurat</span>
                                @if($record->emergency_contact_name)
                                    <div>
                                        <p class="text-sm font-extrabold text-gray-900 dark:text-gray-200">{{ $record->emergency_contact_name }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $record->emergency_contact_phone }}</p>
                                    </div>
                                @else
                                    <p class="text-xs text-gray-400 italic">Belum diatur</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT PANEL: Medical Record Form (2/3) -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50 space-y-6">
                            <h4 class="text-base font-black text-gray-900 dark:text-white flex items-center">
                                <span class="w-2 h-5 bg-teal-600 rounded-full mr-3"></span>
                                Form Isian Rekam Medis
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Blood Type -->
                                <div class="space-y-2">
                                    <x-input-label for="blood_type" value="Golongan Darah *" class="text-xs font-black uppercase text-gray-500 dark:text-gray-500 tracking-wider" />
                                    <select name="blood_type" id="blood_type" required
                                            class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">
                                        <option value="unknown" {{ old('blood_type', $record->blood_type) === 'unknown' ? 'selected' : '' }}>Pilih Golongan Darah</option>
                                        <option value="A" {{ old('blood_type', $record->blood_type) === 'A' ? 'selected' : '' }}>A</option>
                                        <option value="B" {{ old('blood_type', $record->blood_type) === 'B' ? 'selected' : '' }}>B</option>
                                        <option value="AB" {{ old('blood_type', $record->blood_type) === 'AB' ? 'selected' : '' }}>AB</option>
                                        <option value="O" {{ old('blood_type', $record->blood_type) === 'O' ? 'selected' : '' }}>O</option>
                                    </select>
                                </div>

                                <!-- Vision Status -->
                                <div class="space-y-2">
                                    <x-input-label for="vision_status" value="Kondisi Penglihatan (Mata) *" class="text-xs font-black uppercase text-gray-500 dark:text-gray-500 tracking-wider" />
                                    <select name="vision_status" id="vision_status" required
                                            class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">
                                        <option value="unknown" {{ old('vision_status', $record->vision_status) === 'unknown' ? 'selected' : '' }}>Pilih Kondisi Mata</option>
                                        <option value="normal" {{ old('vision_status', $record->vision_status) === 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="minus" {{ old('vision_status', $record->vision_status) === 'minus' ? 'selected' : '' }}>Miopi (Minus)</option>
                                        <option value="plus" {{ old('vision_status', $record->vision_status) === 'plus' ? 'selected' : '' }}>Hipermetropi (Plus)</option>
                                        <option value="silinder" {{ old('vision_status', $record->vision_status) === 'silinder' ? 'selected' : '' }}>Silinder (Astigmatisme)</option>
                                    </select>
                                </div>

                                <!-- Height & Weight -->
                                <div class="space-y-2">
                                    <x-input-label for="height_cm" value="Tinggi Badan (cm)" class="text-xs font-black uppercase text-gray-500 dark:text-gray-500 tracking-wider" />
                                    <input type="number" step="0.1" name="height_cm" id="height_cm" value="{{ old('height_cm', $record->height_cm) }}" placeholder="Contoh: 165.5"
                                           class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">
                                </div>

                                <div class="space-y-2">
                                    <x-input-label for="weight_kg" value="Berat Badan (kg)" class="text-xs font-black uppercase text-gray-500 dark:text-gray-500 tracking-wider" />
                                    <input type="number" step="0.1" name="weight_kg" id="weight_kg" value="{{ old('weight_kg', $record->weight_kg) }}" placeholder="Contoh: 55.4"
                                           class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">
                                </div>

                                <!-- Allergies (Comma Separated) -->
                                <div class="md:col-span-2 space-y-2">
                                    <x-input-label for="allergies" value="Alergi (Pisahkan dengan koma)" class="text-xs font-black uppercase text-gray-500 dark:text-gray-500 tracking-wider" />
                                    <input type="text" name="allergies" id="allergies" placeholder="Contoh: Kacang, Udang, Debu, Penisilin"
                                           value="{{ old('allergies', !empty($record->allergies) ? implode(', ', $record->allergies) : '') }}"
                                           class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">
                                </div>

                                <!-- Chronic Conditions (Comma Separated) -->
                                <div class="md:col-span-2 space-y-2">
                                    <x-input-label for="chronic_conditions" value="Penyakit Kronis / Bawaan (Pisahkan dengan koma)" class="text-xs font-black uppercase text-gray-500 dark:text-gray-500 tracking-wider" />
                                    <input type="text" name="chronic_conditions" id="chronic_conditions" placeholder="Contoh: Asma, Diabetes, Maag Kronis"
                                           value="{{ old('chronic_conditions', !empty($record->chronic_conditions) ? implode(', ', $record->chronic_conditions) : '') }}"
                                           class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">
                                </div>

                                <!-- Medications -->
                                <div class="md:col-span-2 space-y-2">
                                    <x-input-label for="medications" value="Obat-obatan Rutin (Pisahkan dengan koma)" class="text-xs font-black uppercase text-gray-500 dark:text-gray-500 tracking-wider" />
                                    <input type="text" name="medications" id="medications" placeholder="Contoh: Inhaler Albuterol, Insulin"
                                           value="{{ old('medications', !empty($record->medications) ? implode(', ', $record->medications) : '') }}"
                                           class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">
                                </div>

                                <!-- Insurance / BPJS -->
                                <div class="md:col-span-2 space-y-2">
                                    <x-input-label for="insurance_number" value="Nomor Asuransi / BPJS" class="text-xs font-black uppercase text-gray-500 dark:text-gray-500 tracking-wider" />
                                    <input type="text" name="insurance_number" id="insurance_number" value="{{ old('insurance_number', $record->insurance_number) }}" placeholder="Contoh: 0002047814421"
                                           class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">
                                </div>

                                <!-- Emergency Contact Name -->
                                <div class="space-y-2">
                                    <x-input-label for="emergency_contact_name" value="Nama Kontak Darurat (Orang Tua / Wali)" class="text-xs font-black uppercase text-gray-500 dark:text-gray-500 tracking-wider" />
                                    <input type="text" name="emergency_contact_name" id="emergency_contact_name" value="{{ old('emergency_contact_name', $record->emergency_contact_name) }}" placeholder="Contoh: Budi Santoso"
                                           class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">
                                </div>

                                <!-- Emergency Contact Phone -->
                                <div class="space-y-2">
                                    <x-input-label for="emergency_contact_phone" value="No. Telepon Kontak Darurat" class="text-xs font-black uppercase text-gray-500 dark:text-gray-500 tracking-wider" />
                                    <input type="text" name="emergency_contact_phone" id="emergency_contact_phone" value="{{ old('emergency_contact_phone', $record->emergency_contact_phone) }}" placeholder="Contoh: 081234567890"
                                           class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">
                                </div>

                                <!-- Medical Notes -->
                                <div class="md:col-span-2 space-y-2">
                                    <x-input-label for="medical_notes" value="Catatan Medis Khusus" class="text-xs font-black uppercase text-gray-500 dark:text-gray-500 tracking-wider" />
                                    <textarea name="medical_notes" id="medical_notes" rows="4" placeholder="Tulis instruksi khusus jika siswa mengalami asma kambuh atau penanganan medis mendesak lainnya..." 
                                              class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">{{ old('medical_notes', $record->medical_notes) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Form submission bar -->
                        <div class="flex justify-end gap-3 pt-4">
                            <a href="{{ route('uks.health-records.index') }}" class="px-6 py-3 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                                Kembali
                            </a>
                            <button type="submit" class="px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-teal-600/15">
                                Simpan Rekam Medis
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
</x-app-layout>
