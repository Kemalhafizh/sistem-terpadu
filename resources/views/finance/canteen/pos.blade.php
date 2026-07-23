<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <div>
                <span class="text-[10px] font-black uppercase tracking-widest text-teal-600 dark:text-teal-400">Terminal Kasir</span>
                <h2 class="font-black text-3xl text-gray-800 dark:text-gray-100 leading-tight tracking-tight mt-1">
                    Point of Sale (POS)
                </h2>
                <div class="flex items-center space-x-2 mt-1">
                    <span class="w-2 h-2 rounded-full bg-teal-500 animate-ping"></span>
                    <span class="text-xs font-semibold text-gray-400 dark:text-gray-500">
                        Merchant: {{ $merchant->name }}
                    </span>
                </div>
            </div>
            
            <div class="flex space-x-2 p-1 bg-gray-100 dark:bg-gray-900 rounded-2xl border border-gray-200/50 dark:border-gray-800 shrink-0 self-start md:self-auto">
                <a href="{{ route('merchant.canteen.dashboard') }}" class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                    Dashboard
                </a>
                <a href="{{ route('merchant.canteen.pos') }}" class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all bg-white dark:bg-gray-800 text-teal-600 dark:text-teal-400 shadow-sm">
                    POS Terminal
                </a>
                <a href="{{ route('merchant.canteen.settlement') }}" class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                    Settlement
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 w-full sm:px-6 lg:px-8" x-data="{
        activeCategory: 'Semua',
        cart: [],
        nis: '',
        checkoutLoading: false,
        successReceipt: null,
        errorMessage: '',
        showReceiptModal: false,

        addItem(name, price) {
            let existing = this.cart.find(i => i.name === name);
            if (existing) {
                existing.qty++;
            } else {
                this.cart.push({ name: name, price: price, qty: 1 });
            }
            this.errorMessage = '';
        },
        removeItem(index) {
            if (this.cart[index].qty > 1) {
                this.cart[index].qty--;
            } else {
                this.cart.splice(index, 1);
            }
            this.errorMessage = '';
        },
        clearCart() {
            this.cart = [];
            this.errorMessage = '';
        },
        total() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        },
        checkout() {
            if (!this.nis.trim()) {
                this.errorMessage = 'Silakan masukkan atau scan identitas NIS siswa.';
                return;
            }
            if (this.cart.length === 0) {
                this.errorMessage = 'Keranjang belanja masih kosong.';
                return;
            }
            this.checkoutLoading = true;
            this.errorMessage = '';
            this.successReceipt = null;

            axios.post('{{ route('merchant.canteen.payment') }}', {
                student_identifier: this.nis,
                items: this.cart,
                total_amount: this.total()
            })
            .then(response => {
                if (response.data.success) {
                    this.successReceipt = response.data.receipt;
                    this.showReceiptModal = true;
                    this.cart = [];
                    this.nis = '';
                } else {
                    this.errorMessage = response.data.message || 'Terjadi kesalahan sistem.';
                }
            })
            .catch(error => {
                this.errorMessage = error.response?.data?.message || 'Gagal memproses pembayaran. Periksa koneksi atau identitas siswa.';
            })
            .finally(() => {
                this.checkoutLoading = false;
            });
        }
    }">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch min-h-[calc(100vh-280px)]">
            <!-- Left Side: Menu Grid (col-span-8) -->
            <div class="lg:col-span-7 xl:col-span-8 flex flex-col space-y-6">
                <!-- Category Selector Tab -->
                <div class="flex space-x-2 overflow-x-auto pb-2 shrink-0">
                    <template x-for="cat in ['Semua', 'Makanan', 'Camilan', 'Minuman']">
                        <button @click="activeCategory = cat" 
                            :class="activeCategory === cat ? 'bg-teal-600 text-white shadow-lg shadow-teal-600/20' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 border border-gray-100 dark:border-gray-700'"
                            class="px-5 py-2.5 rounded-2xl text-xs font-black uppercase tracking-wider transition-all duration-200 shrink-0"
                            x-text="cat">
                        </button>
                    </template>
                </div>

                <!-- Menu Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4 overflow-y-auto pr-1 grow max-h-[600px] lg:max-h-[calc(100vh-340px)]">
                    @foreach($menuItems as $item)
                        <div x-show="activeCategory === 'Semua' || activeCategory === '{{ $item['category'] }}'"
                            @click="addItem('{{ $item['name'] }}', {{ $item['price'] }})"
                            class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/80 shadow-xs hover:shadow-md transition-all duration-300 transform active:scale-95 cursor-pointer flex flex-col justify-between group relative overflow-hidden">
                            
                            <!-- Glow effect on hover -->
                            <div class="absolute inset-0 bg-gradient-to-br from-teal-500/5 to-emerald-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            
                            <div class="relative">
                                <span class="text-[9px] font-black uppercase tracking-widest text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-950/40 px-2 py-0.5 rounded-md">
                                    {{ $item['category'] }}
                                </span>
                                <h4 class="font-black text-gray-800 dark:text-gray-100 tracking-tight mt-3 text-sm group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">
                                    {{ $item['name'] }}
                                </h4>
                            </div>
                            
                            <div class="mt-4 flex justify-between items-center relative">
                                <span class="font-black text-teal-600 dark:text-teal-400 text-sm">
                                    Rp {{ number_format($item['price'], 0, ',', '.') }}
                                </span>
                                <span class="w-8 h-8 rounded-xl bg-teal-50 dark:bg-teal-950/30 text-teal-600 dark:text-teal-400 flex items-center justify-center group-hover:bg-teal-600 group-hover:text-white transition-all">
                                    +
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Right Side: Cart Summary & Checkout (col-span-4) -->
            <div class="lg:col-span-5 xl:col-span-4 flex flex-col">
                <div class="glass dark:glass-dark rounded-[2.5rem] p-6 border border-white/40 dark:border-white/5 shadow-2xl flex flex-col justify-between grow h-full max-h-[calc(100vh-220px)] overflow-hidden">
                    <div class="flex flex-col overflow-hidden grow">
                        <div class="flex justify-between items-center pb-4 border-b border-gray-100 dark:border-gray-800 shrink-0">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                <h3 class="font-black text-gray-800 dark:text-gray-100 tracking-tight text-lg">Keranjang Belanja</h3>
                            </div>
                            <button @click="clearCart()" class="text-[10px] font-black text-red-500 hover:text-red-700 uppercase tracking-wider transition-colors" x-show="cart.length > 0">
                                Kosongkan
                            </button>
                        </div>

                        <!-- Cart Items List -->
                        <div class="overflow-y-auto grow my-4 pr-1 space-y-3">
                            <template x-if="cart.length === 0">
                                <div class="flex flex-col items-center justify-center py-20 text-center text-gray-400 dark:text-gray-500">
                                    <svg class="w-12 h-12 mb-3 stroke-current opacity-40" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                    <p class="text-sm font-semibold">Keranjang masih kosong</p>
                                    <p class="text-[11px] mt-1">Pilih menu di sebelah kiri untuk memesan.</p>
                                </div>
                            </template>
                            
                            <template x-for="(item, index) in cart" :key="index">
                                <div class="flex justify-between items-center bg-white/40 dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-100/50 dark:border-gray-800/40 shadow-xs">
                                    <div class="grow pr-2">
                                        <h5 class="font-bold text-sm text-gray-800 dark:text-gray-200" x-text="item.name"></h5>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(item.price) + ' × ' + item.qty"></p>
                                    </div>
                                    <div class="flex items-center space-x-2 shrink-0">
                                        <button @click="removeItem(index)" class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-red-50 dark:hover:bg-red-950/20 text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 flex items-center justify-center font-bold transition-all">-</button>
                                        <span class="w-6 text-center font-black text-sm text-gray-800 dark:text-gray-200" x-text="item.qty"></span>
                                        <button @click="addItem(item.name, item.price)" class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-950/20 text-teal-600 dark:text-teal-400 hover:bg-teal-600 hover:text-white flex items-center justify-center font-bold transition-all">+</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Checkout Area (Fixed at bottom of container) -->
                    <div class="border-t border-gray-100 dark:border-gray-800 pt-4 space-y-4 shrink-0">
                        <!-- Summary -->
                        <div class="space-y-2">
                            <div class="flex justify-between text-xs font-semibold text-gray-500">
                                <span>Subtotal</span>
                                <span x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(total())"></span>
                            </div>
                            <div class="flex justify-between items-center text-gray-800 dark:text-gray-100">
                                <span class="font-black text-sm uppercase tracking-wider">Total</span>
                                <span class="font-black text-xl text-teal-600 dark:text-teal-400" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(total())"></span>
                            </div>
                        </div>

                        <!-- Input NIS & Checkout -->
                        <div class="space-y-3">
                            <div>
                                <label for="student_identifier" class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-2 block">NIS Siswa atau Scan QR Code</label>
                                <div class="relative">
                                    <input type="text" x-model="nis" id="student_identifier" placeholder="Scan kartu siswa atau input NIS..."
                                        class="w-full rounded-2xl border border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-900/80 py-3 pl-4 pr-10 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-gray-800 dark:text-gray-100 text-sm transition-all" />
                                    <div class="absolute right-3 top-3.5 text-gray-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-16v3m0 8h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Error Alert -->
                            <template x-if="errorMessage">
                                <div class="bg-red-50 dark:bg-red-950/30 border border-red-200/50 dark:border-red-900/30 p-3.5 rounded-2xl text-red-600 dark:text-red-400 text-xs font-bold flex items-start space-x-2 animate-fade-in">
                                    <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span x-text="errorMessage"></span>
                                </div>
                            </template>

                            <button @click="checkout()" :disabled="checkoutLoading || cart.length === 0 || !nis"
                                class="w-full py-4 bg-teal-600 hover:bg-teal-700 disabled:bg-gray-200 dark:disabled:bg-gray-800 disabled:text-gray-400 text-white font-black rounded-2xl text-xs uppercase tracking-widest shadow-lg shadow-teal-600/20 hover:shadow-teal-600/35 transition-all duration-300 transform active:scale-95 flex items-center justify-center space-x-2">
                                <template x-if="checkoutLoading">
                                    <div class="w-4 h-4 border-2 border-white rounded-full animate-spin border-t-transparent mr-2"></div>
                                </template>
                                <span x-text="checkoutLoading ? 'Memproses...' : 'Bayar Cashless'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Receipt Modal -->
        <div x-show="showReceiptModal" 
            class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[350] flex items-center justify-center p-4"
            style="display: none;"
            x-transition.opacity>
            
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 max-w-sm w-full shadow-2xl relative overflow-hidden border border-gray-100 dark:border-gray-700 animate-fade-in-up"
                @click.away="showReceiptModal = false">
                
                <!-- Premium Ribbon -->
                <div class="absolute top-0 inset-x-0 h-2 bg-gradient-to-r from-teal-500 to-emerald-500"></div>

                <div class="text-center mb-6">
                    <div class="w-14 h-14 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-3 shadow-inner">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h4 class="text-lg font-black text-gray-800 dark:text-gray-100 tracking-tight">Pembayaran Sukses</h4>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1" x-text="successReceipt ? successReceipt.ref_number : ''"></p>
                </div>

                <div class="border-t border-b border-dashed border-gray-200 dark:border-gray-700 py-4 my-4 space-y-3">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-400 font-medium">Siswa:</span>
                        <span class="font-black text-gray-800 dark:text-gray-100" x-text="successReceipt ? successReceipt.student_name : ''"></span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-400 font-medium">NIS:</span>
                        <span class="font-bold text-gray-800 dark:text-gray-100" x-text="successReceipt ? successReceipt.nis : ''"></span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-400 font-medium">Kelas:</span>
                        <span class="font-bold text-gray-800 dark:text-gray-100" x-text="successReceipt ? successReceipt.classroom : ''"></span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-400 font-medium">Waktu:</span>
                        <span class="font-bold text-gray-800 dark:text-gray-100" x-text="successReceipt ? successReceipt.time : ''"></span>
                    </div>
                    <div class="border-t border-dashed border-gray-100 dark:border-gray-700/50 pt-3 flex justify-between text-xs">
                        <span class="text-gray-400 font-black uppercase tracking-wider">Total Belanja:</span>
                        <span class="font-black text-teal-600 dark:text-teal-400 text-sm" x-text="successReceipt ? 'Rp ' + new Intl.NumberFormat('id-ID').format(successReceipt.total) : ''"></span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-400 font-black uppercase tracking-wider">Sisa Saldo:</span>
                        <span class="font-black text-gray-800 dark:text-gray-100" x-text="successReceipt ? 'Rp ' + new Intl.NumberFormat('id-ID').format(successReceipt.balance_remaining) : ''"></span>
                    </div>
                </div>

                <button @click="showReceiptModal = false"
                    class="w-full py-3.5 bg-gray-800 hover:bg-gray-700 text-white font-black rounded-xl text-xs uppercase tracking-widest transition-all">
                    Tutup Struk
                </button>
            </div>
        </div>
    </div>
</x-app-layout>
