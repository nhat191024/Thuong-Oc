<div x-data="{
    downloading: false,
    progress: 0,
    total: {{ count($records) }},
    loadScripts(callback) {
        let loaded = 0;
        const checkDone = () => { if (++loaded === 2) callback(); };

        if (window.domtoimage) {
            checkDone();
        } else {
            const s1 = document.createElement('script');
            s1.src = 'https://cdn.jsdelivr.net/npm/dom-to-image-more@3.4.0/dist/dom-to-image-more.min.js';
            s1.onload = checkDone;
            document.head.appendChild(s1);
        }

        if (window.JSZip) {
            checkDone();
        } else {
            const s2 = document.createElement('script');
            s2.src = 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js';
            s2.onload = checkDone;
            document.head.appendChild(s2);
        }
    },
    async downloadAll() {
        this.downloading = true;
        this.progress = 0;

        this.loadScripts(async () => {
            // Inject a temporary style to neutralise Filament/Tailwind artifacts during capture
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

            const zip = new JSZip();
            const cards = document.querySelectorAll('.qr-print-card-bulk');

            for (let i = 0; i < cards.length; i++) {
                const card = cards[i];
                const tableNumber = card.getAttribute('data-table');

                // Show card momentarily for capture
                card.style.opacity = '1';
                card.style.zIndex = '1';

                try {
                    // Chờ một chút để trình duyệt kịp render
                    await new Promise(resolve => setTimeout(resolve, 50));

                    const dataUrl = await domtoimage.toPng(card, {
                        scale: 3,
                        bgcolor: '#ffffff',
                        width: card.offsetWidth,
                        height: card.offsetHeight,
                        style: {
                            transform: 'scale(1)',
                            transformOrigin: 'top left'
                        }
                    });

                    // remove data:image/png;base64,
                    const base64Data = dataUrl.replace(/^data:image\/png;base64,/, '');
                    zip.file(`Ban_${tableNumber}.png`, base64Data, { base64: true });
                } catch (e) {
                    console.error('Lỗi khi tạo ảnh cho bàn ' + tableNumber, e);
                }

                // Hide card again
                card.style.opacity = '0';
                card.style.zIndex = '-1';
                this.progress = i + 1;
            }

            zip.generateAsync({ type: 'blob' }).then((content) => {
                if (document.getElementById('qr-capture-reset-bulk')) {
                    document.head.removeChild(tmpStyle);
                }

                // Create download link for ZIP
                const link = document.createElement('a');
                link.download = 'QR_Ban_Zip.zip';
                link.href = URL.createObjectURL(content);
                link.click();

                this.downloading = false;
            });
        });
    }
}">

    <div class="flex flex-col items-center justify-center gap-4 py-8">
        <div class="bg-primary-50 mb-2 rounded-full p-4">
            <svg class="text-primary-600 h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 0 0-2.25 2.25v9a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25H15M9 12l3 3m0 0 3-3m-3 3V2.25" />
            </svg>
        </div>

        <h2 class="text-center text-xl font-bold">{{ __('Tải xuống các mã QR đã phối') }}</h2>
        <p class="mb-4 text-center text-sm text-gray-500">
            {{ __('Số lượng mã QR sẽ tạo:') }} <span class="rounded-md border border-gray-200 px-2 py-0.5 font-bold text-black dark:text-white" x-text="total"></span>
        </p>

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
    {{-- Đặt khung ở vị trí cố định trên màn hình (nhưng bị mất nền và không click được) để đảm bảo thẻ ở trong Viewport, tránh lỗi dom-to-image chụp ảnh trắng --}}
    <div style="position: fixed; left: 0; top: 0; pointer-events: none; z-index: -9999; opacity: 0.01;">
        @foreach ($records as $index => $record)
            @php
                $tableNumber = $record->table_number;
                $branchName = $record->branch->name ?? null;
                $url = route('customer-menu.index', ['tableId' => $record->id]);
                $qrCode = base64_encode(SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(400)->generate($url));
            @endphp
            <div class="qr-print-card-bulk flex w-[320px] shrink-0 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-md dark:border-gray-700" data-table="{{ $tableNumber }}" style="position: absolute; top: 0; left: 0; opacity: 0; z-index: -1;">
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
