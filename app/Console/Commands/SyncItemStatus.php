<?php

namespace App\Console\Commands;

use App\Models\Item;
use Illuminate\Console\Command;

class SyncItemStatus extends Command
{
    protected $signature = 'items:sync-status {--dry-run : List what would change without saving}';

    protected $description = 'Realign item availability with the stock level (leaves out-of-service items alone)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        // Anything not deliberately taken out of service should read "available"
        // when there is stock and "empty" when there is not.
        $items = Item::where('status', '!=', Item::STATUS_OUT_OF_SERVICE)->get();

        $rows = [];

        foreach ($items as $item) {
            $expected = $item->quantity > 0 ? Item::STATUS_AVAILABLE : Item::STATUS_EMPTY;

            if ($item->status === $expected) {
                continue;
            }

            $rows[] = [$item->id, $item->name, $item->quantity, $item->status, $expected];

            if (! $dryRun) {
                // The saving hook works this out itself; saving is enough.
                $item->save();
            }
        }

        if (empty($rows)) {
            $this->info('Every item already matches its stock level.');

            return self::SUCCESS;
        }

        $this->table(['ID', 'Item', 'Qty', 'Was', 'Now'], $rows);
        $this->info(($dryRun ? 'Would update ' : 'Updated ') . count($rows) . ' item(s).');

        return self::SUCCESS;
    }
}
