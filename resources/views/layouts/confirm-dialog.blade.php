<div
    x-data="{
        open: false,
        title: 'Konfirmasi',
        message: 'Apakah Anda yakin ingin melanjutkan?',
        confirmText: 'OK',
        cancelText: 'Cancel',
        onConfirm: null,
        init() {
            window.showAppConfirm = (options = {}) => {
                this.title = options.title || 'Konfirmasi';
                this.message = options.message || 'Apakah Anda yakin ingin melanjutkan?';
                this.confirmText = options.confirmText || 'OK';
                this.cancelText = options.cancelText || 'Cancel';
                this.onConfirm = typeof options.onConfirm === 'function' ? options.onConfirm : null;
                this.open = true;
                document.body.classList.add('overflow-hidden');
            };

            window.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && this.open) {
                    this.cancel();
                }
            });

            document.addEventListener('submit', (event) => {
                const form = event.target.closest('form[data-confirm]');
                if (!form || form.dataset.confirmBypassed === 'true') {
                    return;
                }

                event.preventDefault();

                window.showAppConfirm({
                    title: form.dataset.confirmTitle || 'Konfirmasi',
                    message: form.dataset.confirm || 'Apakah Anda yakin ingin melanjutkan?',
                    confirmText: form.dataset.confirmText || 'OK',
                    cancelText: form.dataset.cancelText || 'Cancel',
                    onConfirm: () => {
                        form.dataset.confirmBypassed = 'true';
                        form.submit();
                    },
                });
            });
        },
        cancel() {
            this.open = false;
            this.onConfirm = null;
            document.body.classList.remove('overflow-hidden');
        },
        confirm() {
            const callback = this.onConfirm;
            this.cancel();
            if (callback) {
                setTimeout(() => callback(), 40);
            }
        },
    }"
    x-cloak
>
    <template x-teleport="body">
        <div
            x-show="open"
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
            aria-modal="true"
            role="dialog"
        >
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-250"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute inset-0 bg-slate-950/70 backdrop-blur-[3px]"
                @click="cancel()"
            ></div>

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-3 scale-95"
                class="relative w-full max-w-md overflow-hidden rounded-[1.6rem] border border-slate-700/80 bg-[#1b1f24] text-white shadow-[0_30px_80px_-25px_rgba(0,0,0,0.85)]"
            >
                <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-slate-400/40 to-transparent"></div>

                <div class="px-6 pt-6">
                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-400" x-text="title"></p>
                </div>

                <div class="px-6 pb-6 pt-4">
                    <p class="text-xl font-medium tracking-tight text-slate-100" x-text="message"></p>

                    <div class="mt-8 flex items-center justify-end gap-3">
                        <button
                            type="button"
                            @click="confirm()"
                            class="min-w-[6.5rem] rounded-full border-2 border-sky-300/90 bg-slate-200 px-5 py-2.5 text-sm font-bold text-slate-800 shadow-[inset_0_0_0_3px_rgba(37,99,235,0.55)] transition-all hover:-translate-y-0.5 hover:bg-white"
                            x-text="confirmText"
                        ></button>
                        <button
                            type="button"
                            @click="cancel()"
                            class="min-w-[6.5rem] rounded-full bg-[#214f92] px-5 py-2.5 text-sm font-bold text-slate-100 transition-all hover:-translate-y-0.5 hover:bg-[#2961b3]"
                            x-text="cancelText"
                        ></button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
