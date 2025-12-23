<div class="fi-ta-ctn divide-y divide-gray-200 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10 w-full">
    <div class="fi-ta-content relative divide-y divide-gray-200 dark:divide-white/10 overflow-x-auto w-full">
        <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
            <thead class="bg-gray-50 dark:bg-white/5">
                <tr>
                    <th class="px-4 py-3 text-start text-sm font-medium text-gray-950 dark:text-white">Món ăn</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-950 dark:text-white">SL</th>
                    <th class="px-4 py-3 text-end text-sm font-medium text-gray-950 dark:text-white">Đơn giá</th>
                    <th class="px-4 py-3 text-end text-sm font-medium text-gray-950 dark:text-white">Thành tiền</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-950 dark:text-white">Trạng thái</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                @foreach ($getState() as $detail)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                            <div class="font-medium text-gray-950 dark:text-white">
                                {{ $detail->dish->food->name ?? 'Unknown' }}
                                @if($detail->dish->cookingMethod)
                                    - {{ $detail->dish->cookingMethod->name }}
                                @endif
                            </div>
                            @if($detail->note)
                                <div class="text-xs italic mt-1">{{ $detail->note }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-gray-500 dark:text-gray-400">
                            {{ $detail->quantity }}
                        </td>
                        <td class="px-4 py-3 text-end text-sm text-gray-500 dark:text-gray-400">
                            {{ number_format($detail->price) }} ₫
                        </td>
                        <td class="px-4 py-3 text-end text-sm font-medium text-gray-950 dark:text-white">
                            {{ number_format($detail->quantity * $detail->price) }} ₫
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $color = match($detail->status->value) {
                                    'approved' => 'success',
                                    'cancelled' => 'danger',
                                    default => 'gray',
                                };
                                $label = match($detail->status->value) {
                                    'approved' => 'Đã duyệt',
                                    'cancelled' => 'Đã huỷ',
                                    default => $detail->status->value,
                                };
                            @endphp
                            <x-filament::badge :color="$color">
                                {{ $label }}
                            </x-filament::badge>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
