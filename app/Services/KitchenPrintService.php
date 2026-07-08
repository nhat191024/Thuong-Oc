<?php

namespace App\Services;

use App\Models\BillDetail;
use App\Models\Printer as PrinterModel;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

class KitchenPrintService
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

    public function printForKitchen(BillDetail $billDetail, PrinterModel $printer): bool
    {
        $this->printerIp = $printer->ip_address;
        $this->printerPort = $printer->port;
        $this->timeout = $printer->timeout;
        $this->characterTable = $printer->character_table;
        $this->characterEncoding = $printer->character_encoding;

        return $this->printCompletedOrder($billDetail);
    }

    public function printCompletedOrder(BillDetail $billDetail): bool
    {
        $billDetail->loadMissing(['dish.food', 'dish.cookingMethod', 'bill.table']);

        try {
            $connector = new NetworkPrintConnector($this->printerIp, $this->printerPort, $this->timeout);
            $printer = new Printer($connector);

            $printer->initialize();
            $this->selectCharacterTable($printer);

            // Header
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->setTextSize(2, 2);
            $this->text($printer, "BẾP PHỤC VỤ\n");
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(false);
            $this->text($printer, "================================\n");

            // Table number
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->setEmphasis(true);
            $printer->setTextSize(2, 2);
            $tableNumber = $billDetail->bill->table->table_number ?? 'N/A';
            $this->text($printer, "BÀN: $tableNumber\n");
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(false);
            $this->text($printer, "--------------------------------\n");

            // Dish name
            $dishName = $billDetail->dish
                ? $billDetail->dish->food->name
                : ($billDetail->custom_dish_name ?? 'Món ăn');
            $cookingMethod = $billDetail->dish?->cookingMethod?->name ?? '';

            $fullDishName = $dishName . ($cookingMethod ? ' (' . $cookingMethod . ')' : '');

            $printer->setEmphasis(true);
            $printer->setTextSize(1, 2);
            $this->text($printer, "$fullDishName\n");
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(false);

            $this->text($printer, 'Số lượng: ' . $billDetail->quantity . "\n");

            if (! empty($billDetail->note)) {
                $this->text($printer, "--------------------------------\n");
                $this->text($printer, 'Ghi chú: ' . $billDetail->note . "\n");
            }

            $this->text($printer, "================================\n");
            $this->text($printer, 'Hoàn thành: ' . now()->format('H:i d/m/Y') . "\n");

            $printer->feed(3);
            $printer->cut();
            $printer->close();

            return true;
        } catch (\Exception $e) {
            Log::warning('KitchenPrintService: Cannot connect to printer', [
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
}
