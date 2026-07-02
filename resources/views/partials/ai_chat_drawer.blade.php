<div 
    x-data="aiChat()"
    x-init="initChat()"
    @toggle-chat.window="toggleDrawer()"
    class="relative z-50"
    style="display: none;"
    x-show="open"
>
    <!-- Backdrop -->
    <div 
        x-show="open"
        x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-ink-black/20 backdrop-blur-sm"
        @click="closeDrawer()"
    ></div>

    <!-- Drawer Panel -->
    <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
        <div 
            x-show="open"
            x-transition:enter="transform transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in duration-250"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="w-screen max-w-md"
        >
            <div class="h-full flex flex-col bg-white border-l border-sand shadow-2xl relative">

                <!-- Blue top accent line -->
                <div class="absolute top-0 right-0 left-0 h-[3px] bg-electric-blue" style="background:#5196fe;"></div>

                <!-- Header -->
                <div class="px-6 py-5 border-b border-sand flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white"
                             style="background:#5196fe;">
                            <i class="fa-solid fa-robot text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-ink-black leading-tight">Asisten AI Fintrac</h2>
                            <div class="flex items-center gap-1 mt-0.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#2e7d32] animate-ping"></span>
                                <span class="text-[10px] font-medium text-[#2e7d32]">DeepSeek v3 Aktif</span>
                            </div>
                        </div>
                    </div>
                    <button 
                        @click="closeDrawer()"
                        class="text-fog hover:text-ink-black p-1.5 rounded-lg hover:bg-parchment transition-colors"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Messages -->
                <div 
                    id="chat-messages-container"
                    class="flex-1 overflow-y-auto px-5 py-5 space-y-3"
                    style="background:#fafaf9;"
                >
                    <template x-for="(msg, index) in messages" :key="index">
                        <div 
                            class="flex"
                            :class="msg.sender === 'user' ? 'justify-end' : 'justify-start'"
                        >
                            <!-- AI Avatar -->
                            <template x-if="msg.sender === 'ai'">
                                <div class="w-7 h-7 rounded-xl flex items-center justify-center text-white flex-shrink-0 mr-2 mt-0.5 text-[11px]"
                                     style="background:#5196fe; align-self:flex-start;">
                                    <i class="fa-solid fa-robot"></i>
                                </div>
                            </template>
                            <div 
                                class="max-w-[82%] rounded-2xl px-4 py-3 text-sm leading-relaxed"
                                :class="msg.sender === 'user' 
                                    ? 'text-white rounded-br-none font-medium' 
                                    : 'bg-white text-ink-black rounded-bl-none border border-sand'"
                                :style="msg.sender === 'user' ? 'background:#5196fe;' : ''"
                            >
                                <div x-text="msg.text" class="whitespace-pre-line"></div>
                            </div>
                        </div>
                    </template>

                    <!-- Typing Indicator -->
                    <div x-show="loading" class="flex justify-start items-center gap-2" style="display: none;">
                        <div class="w-7 h-7 rounded-xl flex items-center justify-center text-white flex-shrink-0 text-[11px]"
                             style="background:#5196fe;">
                            <i class="fa-solid fa-robot"></i>
                        </div>
                        <div class="bg-white border border-sand rounded-2xl rounded-bl-none px-4 py-3 flex items-center gap-1">
                            <span class="text-xs text-steel mr-1">Berpikir</span>
                            <span class="w-1.5 h-1.5 bg-fog rounded-full animate-bounce" style="animation-delay:0ms"></span>
                            <span class="w-1.5 h-1.5 bg-fog rounded-full animate-bounce" style="animation-delay:150ms"></span>
                            <span class="w-1.5 h-1.5 bg-fog rounded-full animate-bounce" style="animation-delay:300ms"></span>
                        </div>
                    </div>
                </div>

                <!-- Input Bar -->
                <div class="px-5 py-4 border-t border-sand bg-white">
                    <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                        <input 
                            type="text" 
                            x-model="input" 
                            required
                            :disabled="loading"
                            class="flex-1 text-sm text-ink-black placeholder-fog bg-parchment border border-sand rounded-pill px-4 py-3 outline-none transition-all focus:border-electric-blue focus:bg-white disabled:opacity-50"
                            placeholder="Ketik pesan atau catat transaksi..."
                        >
                        <button 
                            type="submit" 
                            :disabled="loading || !input.trim()"
                            class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-pill text-white transition-all disabled:opacity-40 active:scale-95"
                            style="background:#5196fe;"
                        >
                            <i class="fa-solid fa-paper-plane text-sm"></i>
                        </button>
                    </form>
                    <p class="text-[10px] text-fog text-center mt-2">
                        Contoh: "makan siang 45rb" atau "hapus transaksi bensin tadi"
                    </p>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('aiChat', () => ({
            open: false,
            input: '',
            loading: false,
            messages: [],

            initChat() {
                const savedMessages = sessionStorage.getItem('fintrac_chat_messages');
                if (savedMessages) {
                    this.messages = JSON.parse(savedMessages);
                } else {
                    this.messages = [{ 
                        sender: 'ai', 
                        text: 'Halo! Saya Asisten Fintrac.AI 👋\n\nSaya bisa membantu Anda:\n• Catat transaksi otomatis — "Beli kopi 20rb"\n• Hapus transaksi — "Hapus bensin tadi"\n• Buat kategori baru secara otomatis\n\nAda yang ingin dicatat hari ini?' 
                    }];
                    this.saveMessages();
                }

                const savedOpen = sessionStorage.getItem('fintrac_chat_open');
                if (savedOpen === 'true') {
                    this.open = true;
                    this.scrollChat();
                }
            },

            toggleDrawer() {
                this.open = !this.open;
                sessionStorage.setItem('fintrac_chat_open', this.open);
                if (this.open) this.scrollChat();
            },

            closeDrawer() {
                this.open = false;
                sessionStorage.setItem('fintrac_chat_open', 'false');
            },

            sendMessage() {
                if (!this.input.trim() || this.loading) return;

                const userMsg = this.input.trim();
                this.messages.push({ sender: 'user', text: userMsg });
                this.input = '';
                this.loading = true;
                this.saveMessages();
                this.scrollChat();

                fetch('{{ route("ai.chat") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ message: userMsg })
                })
                .then(res => res.json())
                .then(data => {
                    this.loading = false;
                    if (data.success) {
                        this.messages.push({ sender: 'ai', text: data.message });
                        this.saveMessages();
                        this.scrollChat();

                        // Auto-reload for data-changing actions
                        const needsReload = data.transaction_logged
                            || data.action === 'delete_transaction'
                            || data.action === 'log_transactions';

                        if (needsReload) {
                            sessionStorage.setItem('fintrac_chat_open', 'true');
                            this.messages.push({ sender: 'ai', text: '🔄 Memperbarui halaman...' });
                            this.saveMessages();
                            this.scrollChat();
                            setTimeout(() => { window.location.reload(); }, 1200);
                        }
                    } else {
                        this.messages.push({ sender: 'ai', text: 'Maaf, terjadi kesalahan. Silakan coba lagi.' });
                        this.saveMessages();
                        this.scrollChat();
                    }
                })
                .catch(err => {
                    this.loading = false;
                    this.messages.push({ sender: 'ai', text: 'Koneksi gagal. Periksa jaringan Anda.' });
                    this.saveMessages();
                    this.scrollChat();
                    console.error('Chat Error:', err);
                });
            },

            saveMessages() {
                sessionStorage.setItem('fintrac_chat_messages', JSON.stringify(this.messages));
            },

            scrollChat() {
                this.$nextTick(() => {
                    const c = document.getElementById('chat-messages-container');
                    if (c) c.scrollTop = c.scrollHeight;
                });
            }
        }));
    });
</script>
