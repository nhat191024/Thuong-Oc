<div x-data="{
    downloading: false,
    progress: 0,
    failedTables: [],
    total: {{ count($records) }},
    captureRoot: null,
    ensureScript(src, globalKey) {
        if (window[globalKey]) {
            return Promise.resolve();
        }

        const existing = document.querySelector(`script[data-lib=\"${globalKey}\"]`);

        if (existing) {
            return new Promise((resolve, reject) => {
                existing.addEventListener('load', () => resolve(), { once: true });
                existing.addEventListener('error', () => reject(new Error(`Không thể tải ${globalKey}`)), { once: true });
            });
        }

        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.async = true;
            script.dataset.lib = globalKey;
            script.onload = () => resolve();
            script.onerror = () => reject(new Error(`Không thể tải ${globalKey}`));
            document.head.appendChild(script);
        });
    },
    async loadScripts() {
        await Promise.all([
            this.ensureScript('https://cdn.jsdelivr.net/npm/dom-to-image-more@3.4.0/dist/dom-to-image-more.min.js', 'domtoimage'),
            this.ensureScript('https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js', 'JSZip'),
        ]);
    },
    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    },
    async preloadImages(container) {
        const images = Array.from(container.querySelectorAll('img'));

        await Promise.all(
            images.map((img) => {
                if (img.complete && img.naturalWidth > 0) {
                    return Promise.resolve();
                }

                return new Promise((resolve) => {
                    img.addEventListener('load', () => resolve(), { once: true });
                    img.addEventListener('error', () => resolve(), { once: true });
                });
            })
        );
    },
    getCaptureRoot() {
        if (this.captureRoot && document.body.contains(this.captureRoot)) {
            return this.captureRoot;
        }

        this.captureRoot = document.createElement('div');
        this.captureRoot.id = 'qr-capture-root';
        this.captureRoot.style.position = 'fixed';
        this.captureRoot.style.left = '-10000px';
        this.captureRoot.style.top = '0';
        this.captureRoot.style.pointerEvents = 'none';
        this.captureRoot.style.zIndex = '-9999';
        this.captureRoot.style.background = '#ffffff';
        document.body.appendChild(this.captureRoot);

        return this.captureRoot;
    },
    async captureCard(card) {
        const root = this.getCaptureRoot();
        const clone = card.cloneNode(true);
        clone.style.display = 'block';
        clone.style.visibility = 'visible';
        root.appendChild(clone);

        try {
            await this.sleep(80);
            await this.preloadImages(clone);
            await this.sleep(40);

            const width = clone.scrollWidth || clone.offsetWidth;
            const height = clone.scrollHeight || clone.offsetHeight;

            return await window.domtoimage.toPng(clone, {
                scale: 3,
                cacheBust: true,
                bgcolor: '#ffffff',
                width,
                height,
            });
        } finally {
            root.removeChild(clone);
        }
    },
    sanitizeFileName(fileName) {
        return String(fileName).replace(/[\\/:*?\"<>|]/g, '_');
    },
    cleanupCaptureRoot() {
        if (this.captureRoot && document.body.contains(this.captureRoot)) {
            this.captureRoot.remove();
        }

        this.captureRoot = null;
    },
    async downloadAll() {
        if (this.downloading) {
            return;
        }

        this.downloading = true;
        this.progress = 0;
        this.failedTables = [];

        if (this.total === 0) {
            this.downloading = false;

            return;
        }

        const tmpStyle = document.createElement('style');
        tmpStyle.id = 'qr-capture-reset-bulk';
        tmpStyle.textContent = `
            .qr-print-card-bulk,
            .qr-print-card-bulk * {
                text-decoration: none !important;
                outline: none !important;
                outline-offset: 0 !important;
                -webkit-text-decoration: none !important;
            }
            .qr-print-card-bulk *:not(.border):not(.border-4):not(.border-t):not(.border-2) {
                border: none !important;
            }
            .qr-print-card-bulk p,
            .qr-print-card-bulk h1,
            .qr-print-card-bulk h2,
            .qr-print-card-bulk h3,
            .qr-print-card-bulk span {
                box-shadow: none !important;
                border: none !important;
            }
        `;
        document.head.appendChild(tmpStyle);

        try {
            await this.loadScripts();

            const zip = new window.JSZip();
            const cards = Array.from(document.querySelectorAll('.qr-print-card-bulk'));

            for (let i = 0; i < cards.length; i++) {
                const card = cards[i];
                const tableNumber = card.getAttribute('data-table') ?? `${i + 1}`;

                try {
                    const dataUrl = await this.captureCard(card);
                    const base64Data = dataUrl.replace(/^data:image\/png;base64,/, '');
                    const fileName = this.sanitizeFileName(`Ban_${tableNumber}.png`);
                    zip.file(fileName, base64Data, { base64: true });
                } catch (error) {
                    console.error('Lỗi khi tạo ảnh cho bàn ' + tableNumber, error);
                    this.failedTables.push(tableNumber);
                }

                this.progress = i + 1;
                await this.sleep(20);
            }

            if (Object.keys(zip.files).length > 0) {
                const content = await zip.generateAsync({ type: 'blob' });
                const link = document.createElement('a');
                const objectUrl = URL.createObjectURL(content);
                link.download = 'QR_Ban_Zip.zip';
                link.href = objectUrl;
                link.click();
                setTimeout(() => URL.revokeObjectURL(objectUrl), 2000);
            }
        } finally {
            if (document.getElementById('qr-capture-reset-bulk')) {
                document.head.removeChild(tmpStyle);
            }

            this.cleanupCaptureRoot();
            this.downloading = false;
        }
    }
}">

    <div class="flex flex-col items-center justify-center gap-4 py-8">
        <div class="bg-primary-50 mb-2 rounded-full p-4">
            <svg class="text-primary-600 h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 0 0-2.25 2.25v9a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25H15M9 12l3 3m0 0 3-3m-3 3V2.25" />
            </svg>
        </div>

        <h2 class="text-center text-xl font-bold">{{ __('Tải xuống các mã QR đã chọn') }}</h2>
        <p class="mb-4 text-center text-sm text-gray-500">
            {{ __('Số lượng mã QR sẽ tạo:') }} <span class="rounded-md border border-gray-200 px-2 py-0.5 font-bold text-black dark:text-white" x-text="total"></span>
        </p>

        <p class="mb-2 text-center text-xs font-medium text-amber-600" x-show="failedTables.length > 0" x-text="`Không tạo được ${failedTables.length} mã QR: Bàn ${failedTables.join(', ')}`"></p>

        <div class="w-full max-w-xs transition-all duration-300" x-show="downloading">
            <div class="mb-1 flex justify-between text-xs font-semibold">
                <span>{{ __('Đang xử lý...') }}</span>
                <span x-text="`${progress} / ${total}`"></span>
            </div>
            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200">
                <div class="bg-primary-600 h-full rounded-full transition-all duration-300 ease-out" :style="`width: ${(progress / total) * 100}%`"></div>
            </div>
            <p class="mt-4 text-center text-xs font-medium text-orange-500">⚠️ {{ __('Vui lòng để yên cửa sổ này để quá trình không bị gián đoạn') }}</p>
        </div>

        <div class="mt-2 w-full max-w-xs" x-show="!downloading">
            <button class="bg-primary-600 hover:bg-primary-700 flex w-full items-center justify-center gap-2 rounded-lg px-6 py-2.5 text-sm font-medium text-white shadow-md transition-all active:scale-95" @click="downloadAll()">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                {{ __('Bắt đầu tạo & Tải tệp ZIP') }}
            </button>
        </div>
    </div>

    {{-- Cards mapping --}}
    {{-- Đặt div cố định để chứa code, sử dụng visibility hidden thay vì opacity để nó có thể tính toán height/width bình thường, nhưng không hiển thị chướng mắt --}}
    <div style="position: fixed; left: 0; top: 0; pointer-events: none; z-index: -9999; visibility: hidden;">
        @foreach ($records as $index => $record)
            @php
                $tableNumber = $record->table_number;
                $branchName = $record->branch->name ?? null;
                $url = route('customer-menu.index', ['tableId' => $record->id]);
                $qrCode = base64_encode(SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(400)->generate($url));
            @endphp
            <div class="qr-print-card-bulk flex w-[320px] shrink-0 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-md dark:border-gray-700" data-table="{{ $tableNumber }}" style="display: none;">
                {{-- Header --}}
                <div class="bg-primary-600 flex w-full flex-col items-center gap-2 px-5 py-4">
                    @if ($appLogo)
                        <img class="h-14 w-14 rounded-full border-2 border-white/50 object-cover shadow" src="{{ $appLogo }}" alt="Logo" />
                    @else
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white/20">
                            <svg class="h-8 w-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 6h13M7 13L5.4 5M10 21a1 1 0 100-2 1 1 0 000 2zm7 0a1 1 0 100-2 1 1 0 000 2z" />
                            </svg>
                        </div>
                    @endif
                    <div class="text-center">
                        <h2 class="text-lg font-bold leading-tight text-white">{{ $appName }}</h2>
                        @if ($branchName)
                            <p class="mt-0.5 text-xs text-white/75">{{ $branchName }}</p>
                        @endif
                    </div>
                </div>

                {{-- QR Code area --}}
                <div class="flex w-full flex-col items-center gap-3 bg-white px-6 py-5">
                    <div class="border-primary-100 rounded-xl border-4 bg-white p-1 shadow-inner">
                        <img class="h-52 w-52 max-w-none" src="data:image/png;base64,{{ $qrCode }}" alt="{{ __('Mã QR Bàn :number', ['number' => $tableNumber]) }}" />
                    </div>

                    {{-- Table number badge --}}
                    <div class="bg-primary-50 border-primary-200 w-full rounded-xl border px-6 py-2 text-center">
                        <p class="text-primary-500 text-xs font-medium uppercase tracking-widest">{{ __('Số Bàn') }}</p>
                        <p class="text-primary-700 text-4xl font-extrabold leading-tight">{{ $tableNumber }}</p>
                    </div>

                    <p class="text-center text-xs text-gray-400">{{ __('Quét mã để xem thực đơn & gọi món') }}</p>

                    {{-- URL small --}}
                    <p class="w-full break-all text-center text-[10px] leading-tight text-gray-300">{{ $url }}</p>
                </div>

                {{-- Footer --}}
                <div class="w-full border-t border-gray-100 bg-gray-50 px-5 py-2 text-center">
                    <p class="text-[10px] text-gray-400">{{ __('Cảm ơn quý khách!') }}</p>
                </div>
            </div>
        @endforeach
    </div>
</div>
