<?php

namespace App\Services;

use App\Models\BillDetail;
use App\Models\Printer as PrinterModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

class KitchenPrintService
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

    public function printForKitchen(BillDetail $billDetail, PrinterModel $printer): bool
    {
        $this->printerIp = $printer->ip_address;
        $this->printerPort = $printer->port;
        $this->timeout = $printer->timeout;

        return $this->printCompletedOrder($billDetail);
    }

    public function printCompletedOrder(BillDetail $billDetail): bool
    {
        $billDetail->loadMissing(['dish.food', 'dish.cookingMethod', 'bill.table']);

        try {
            $connector = new NetworkPrintConnector($this->printerIp, $this->printerPort, $this->timeout);
            $printer = new Printer($connector);

            $printer->initialize();

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->setTextSize(2, 2);

            $tableNumber = $billDetail->bill->table->table_number ?? 'N/A';
            $printer->text("BAN: $tableNumber\n");

            $dishName = $billDetail->dish
                ? $billDetail->dish->food->name
                : ($billDetail->custom_dish_name ?? 'Mon an');
            $cookingMethod = $billDetail->dish?->cookingMethod?->name ?? '';

            $fullDishName = Str::ascii($dishName) . ($cookingMethod ? ' (' . Str::ascii($cookingMethod) . ')' : '');

            $printer->text("$fullDishName\n");
            $printer->text('So luong: ' . $billDetail->quantity . "\n");

            if (! empty($billDetail->note)) {
                $printer->text('Ghi chu: ' . Str::ascii($billDetail->note) . "\n");
            }

            $printer->feed(3);
            $printer->cut();
            $printer->close();

            return true;
        } catch (\Exception $e) {
            Log::warning('KitchenPrintService: Cannot connect to printer', [
                'ip' => $this->printerIp,
                'port' => $this->printerPort,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
