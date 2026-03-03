<div class="flex flex-col items-center gap-4 p-4">
    <img src="data:image/png;base64,{{ $qrCode }}" alt="{{ __('Mã QR Bàn :number', ['number' => $tableNumber]) }}" class="w-64 h-64" />
    <p class="text-sm text-gray-700 break-all text-center">{{ $url }}</p>
    <a href="{{ $url }}" target="_blank" class="text-primary-600 hover:underline text-sm">
        {{ __('Mở link bàn') }}
    </a>
</div>
