<style>
    @media print {
        .no-print {
            display: none !important;
        }

        body * {
            visibility: hidden;
        }

        #qr-print-card,
        #qr-print-card * {
            visibility: visible;
        }

        #qr-print-card {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 20px;
        }
    }
</style>

<div class="flex flex-col items-center gap-4 py-2">

    {{-- Print card --}}
    <div id="qr-print-card" class="w-full max-w-xs overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-md dark:border-gray-700">

        {{-- Header --}}
        <div class="bg-primary-600 flex flex-col items-center gap-2 px-5 py-4">
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
        <div class="flex flex-col items-center gap-3 bg-white px-6 py-5">
            <div class="border-primary-100 rounded-xl border-4 bg-white p-1 shadow-inner">
                <img class="h-52 w-52" src="data:image/png;base64,{{ $qrCode }}" alt="{{ __('Mã QR Bàn :number', ['number' => $tableNumber]) }}" />
            </div>

            {{-- Table number badge --}}
            <div class="bg-primary-50 border-primary-200 w-full rounded-xl border px-6 py-2 text-center">
                <p class="text-primary-500 text-xs font-medium uppercase tracking-widest">{{ __('Số Bàn') }}</p>
                <p class="text-primary-700 text-4xl font-extrabold leading-tight">{{ $tableNumber }}</p>
            </div>

            <p class="text-center text-xs text-gray-400">{{ __('Quét mã để xem thực đơn & gọi món') }}</p>

            {{-- URL small --}}
            <p class="break-all text-center text-[10px] leading-tight text-gray-300">{{ $url }}</p>
        </div>

        {{-- Footer --}}
        <div class="border-t border-gray-100 bg-gray-50 px-5 py-2 text-center">
            <p class="text-[10px] text-gray-400">{{ __('Cảm ơn quý khách!') }}</p>
        </div>
    </div>

    {{-- Action buttons --}}
    <div class="no-print flex w-full max-w-xs items-center gap-3">
        <a class="flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50" href="data:image/png;base64,{{ $qrCode }}" download="QR-Ban-{{ $tableNumber }}.png">
            <svg class="h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            {{ __('Tải QR') }}
        </a>
        <button class="bg-primary-600 hover:bg-primary-700 flex flex-1 items-center justify-center gap-1.5 rounded-lg px-4 py-2 text-sm font-medium text-white shadow-sm transition" onclick="window.print()">
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            {{ __('In') }}
        </button>
    </div>

</div>
