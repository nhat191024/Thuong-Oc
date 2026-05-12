<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Printer as PrinterModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

class BillPrintService
{
    private string $printerIp;

    private int $printerPort;

    private int $timeout;

    public function __construct()
    {
        $this->printerIp = config('services.printer.ip', '192.168.1.250');
        $this->printerPort = (int) config('services.printer.port', 9100);
        $this->timeout = (int) config('services.printer.timeout', 3);
    }

    public function printForBill(Bill $bill, PrinterModel $printer): bool
    {
        $this->printerIp = $printer->ip_address;
        $this->printerPort = $printer->port;
        $this->timeout = $printer->timeout;

        return $this->printReceipt($bill);
    }

    public function printReceipt(Bill $bill): bool
    {
        $bill->loadMissing(['table', 'customer', 'billDetails.dish.food', 'billDetails.dish.cookingMethod']);

        try {
            $connector = new NetworkPrintConnector($this->printerIp, $this->printerPort, $this->timeout);
            $printer = new Printer($connector);

            $printer->initialize();

            // Header
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->setTextSize(2, 2);
            $printer->text("HOA DON\n");
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(false);
            $printer->text("================================\n");

            // Table & time info
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $tableNumber = $bill->table->table_number ?? 'N/A';
            $printer->text("Ban: $tableNumber\n");
            $printer->text('Vao: ' . \Carbon\Carbon::parse($bill->time_in)->format('H:i d/m/Y') . "\n");
            $printer->text('Ra : ' . now()->format('H:i d/m/Y') . "\n");

            if ($bill->customer) {
                $customerName = Str::ascii($bill->customer->name ?? '');
                $printer->text("KH : $customerName\n");
            }

            $printer->text("--------------------------------\n");
            $printer->text(str_pad('Mon an', 20) . str_pad('SL', 4, ' ', STR_PAD_LEFT) . str_pad('Tien', 10, ' ', STR_PAD_LEFT) . "\n");
            $printer->text("--------------------------------\n");

            // Bill details
            foreach ($bill->billDetails as $detail) {
                $dishName = $detail->dish
                    ? $detail->dish->food->name
                    : ($detail->custom_dish_name ?? 'Mon an');
                $cookingMethod = $detail->dish?->cookingMethod?->name ?? '';

                $dishName = Str::ascii($dishName);
                if ($cookingMethod) {
                    $dishName .= ' (' . Str::ascii($cookingMethod) . ')';
                }

                $qty = $detail->quantity;
                $lineTotal = $detail->quantity * $detail->price;

                // Wrap long dish names
                if (mb_strlen($dishName) > 20) {
                    $printer->text(wordwrap($dishName, 20, "\n", true) . "\n");
                    $printer->text(str_pad('', 20) . str_pad((string) $qty, 4, ' ', STR_PAD_LEFT) . str_pad(number_format($lineTotal), 10, ' ', STR_PAD_LEFT) . "\n");
                } else {
                    $printer->text(str_pad($dishName, 20) . str_pad((string) $qty, 4, ' ', STR_PAD_LEFT) . str_pad(number_format($lineTotal), 10, ' ', STR_PAD_LEFT) . "\n");
                }
            }

            $printer->text("================================\n");

            // Totals
            $total = $bill->total;
            $finalTotal = $bill->final_total ?? $total;
            $discount = $total - $finalTotal;

            $printer->text(str_pad('Tong cong:', 24) . str_pad(number_format($total), 10, ' ', STR_PAD_LEFT) . "\n");

            if ($discount > 0) {
                $printer->text(str_pad('Giam gia:', 24) . str_pad('-' . number_format($discount), 10, ' ', STR_PAD_LEFT) . "\n");
            }

            $printer->setEmphasis(true);
            $printer->text(str_pad('THANH TOAN:', 24) . str_pad(number_format($finalTotal), 10, ' ', STR_PAD_LEFT) . "\n");
            $printer->setEmphasis(false);

            $paymentMethod = $bill->payment_method?->label() ?? '';
            if ($paymentMethod) {
                $printer->text('Phuong thuc: ' . Str::ascii($paymentMethod) . "\n");
            }

            $printer->text("================================\n");
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Cam on quy khach!\n");
            $printer->text("Hen gap lai!\n");

            $printer->feed(3);
            $printer->cut();
            $printer->close();

            return true;
        } catch (\Exception $e) {
            Log::warning('BillPrintService: Cannot connect to printer', [
                'ip' => $this->printerIp,
                'port' => $this->printerPort,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
