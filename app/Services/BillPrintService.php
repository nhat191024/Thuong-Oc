<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Printer as PrinterModel;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

class BillPrintService
{
    private string $printerIp;

    private int $printerPort;

    private int $timeout;

    private int $characterTable;

    private string $characterEncoding;

    public function __construct()
    {
        $this->printerIp = config('services.printer.ip', '192.168.1.250');
        $this->printerPort = (int) config('services.printer.port', 9100);
        $this->timeout = (int) config('services.printer.timeout', 3);
        $this->characterTable = (int) config('services.printer.character_table', 27);
        $this->characterEncoding = (string) config('services.printer.character_encoding', 'CP1258');
    }

    public function printForBill(Bill $bill, PrinterModel $printer): bool
    {
        $this->printerIp = $printer->ip_address;
        $this->printerPort = $printer->port;
        $this->timeout = $printer->timeout;
        $this->characterTable = $printer->character_table;
        $this->characterEncoding = $printer->character_encoding;

        return $this->printReceipt($bill);
    }

    public function printReceipt(Bill $bill): bool
    {
        $bill->loadMissing(['table', 'customer', 'billDetails.dish.food', 'billDetails.dish.cookingMethod']);

        try {
            $connector = new NetworkPrintConnector($this->printerIp, $this->printerPort, $this->timeout);
            $printer = new Printer($connector);

            $printer->initialize();
            $this->selectCharacterTable($printer);

            // Header
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->setTextSize(2, 2);
            $this->text($printer, "HÓA ĐƠN\n");
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(false);
            $this->text($printer, "================================\n");

            // Table & time info
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $tableNumber = $bill->table->table_number ?? 'N/A';
            $this->text($printer, "Bàn: $tableNumber\n");
            $this->text($printer, 'Vào: ' . \Carbon\Carbon::parse($bill->time_in)->format('H:i d/m/Y') . "\n");
            $this->text($printer, 'Ra : ' . now()->format('H:i d/m/Y') . "\n");

            if ($bill->customer) {
                $customerName = $bill->customer->name ?? '';
                $this->text($printer, "KH : $customerName\n");
            }

            $this->text($printer, "--------------------------------\n");
            $this->text($printer, $this->padEnd('Món ăn', 20) . $this->padStart('SL', 4) . $this->padStart('Tiền', 10) . "\n");
            $this->text($printer, "--------------------------------\n");

            // Bill details
            foreach ($bill->billDetails as $detail) {
                $dishName = $detail->dish
                    ? $detail->dish->food->name
                    : ($detail->custom_dish_name ?? 'Món ăn');
                $cookingMethod = $detail->dish?->cookingMethod?->name ?? '';

                if ($cookingMethod) {
                    $dishName .= ' (' . $cookingMethod . ')';
                }

                $qty = $detail->quantity;
                $lineTotal = $detail->quantity * $detail->price;

                foreach ($this->wrap($dishName, 20) as $index => $line) {
                    $quantity = $index === 0 ? (string) $qty : '';
                    $amount = $index === 0 ? number_format($lineTotal) : '';

                    $this->text($printer, $this->padEnd($line, 20) . $this->padStart($quantity, 4) . $this->padStart($amount, 10) . "\n");
                }
            }

            $this->text($printer, "================================\n");

            // Totals
            $total = $bill->total;
            $finalTotal = $bill->final_total ?? $total;
            $discount = $total - $finalTotal;

            $this->text($printer, $this->padEnd('Tổng cộng:', 24) . $this->padStart(number_format($total), 10) . "\n");

            if ($discount > 0) {
                $this->text($printer, $this->padEnd('Giảm giá:', 24) . $this->padStart('-' . number_format($discount), 10) . "\n");
            }

            $printer->setEmphasis(true);
            $this->text($printer, $this->padEnd('THANH TOÁN:', 24) . $this->padStart(number_format($finalTotal), 10) . "\n");
            $printer->setEmphasis(false);

            $paymentMethod = $bill->payment_method?->label() ?? '';
            if ($paymentMethod) {
                $this->text($printer, 'Phương thức: ' . $paymentMethod . "\n");
            }

            $this->text($printer, "================================\n");
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->text($printer, "Cảm ơn quý khách!\n");
            $this->text($printer, "Hẹn gặp lại!\n");

            $printer->feed(3);
            $printer->cut();
            $printer->close();

            return true;
        } catch (\Exception $e) {
            Log::warning('BillPrintService: Cannot connect to printer', [
                'ip' => $this->printerIp,
                'port' => $this->printerPort,
                'character_table' => $this->characterTable,
                'character_encoding' => $this->characterEncoding,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function selectCharacterTable(Printer $printer): void
    {
        $printer->getPrintConnector()->write(Printer::ESC . 't' . chr(max(0, min(255, $this->characterTable))));
    }

    private function text(Printer $printer, string $text): void
    {
        $this->selectCharacterTable($printer);

        if (! function_exists('iconv')) {
            $printer->getPrintConnector()->write($text);

            return;
        }

        $encoding = trim($this->characterEncoding) !== '' ? $this->characterEncoding : 'CP1258';
        $encodedText = @iconv('UTF-8', $encoding . '//TRANSLIT', $text);

        if ($encodedText === false) {
            $encodedText = @iconv('UTF-8', 'CP1258//TRANSLIT', $text);
        }

        $printer->getPrintConnector()->write($encodedText === false ? $text : $encodedText);
    }

    private function padEnd(string $value, int $length): string
    {
        return $value . str_repeat(' ', max(0, $length - mb_strlen($value)));
    }

    private function padStart(string $value, int $length): string
    {
        return str_repeat(' ', max(0, $length - mb_strlen($value))) . $value;
    }

    /**
     * @return array<int, string>
     */
    private function wrap(string $value, int $length): array
    {
        if (mb_strlen($value) <= $length) {
            return [$value];
        }

        $lines = [];

        while (mb_strlen($value) > $length) {
            $lines[] = mb_substr($value, 0, $length);
            $value = mb_substr($value, $length);
        }

        if ($value !== '') {
            $lines[] = $value;
        }

        return $lines;
    }
}
