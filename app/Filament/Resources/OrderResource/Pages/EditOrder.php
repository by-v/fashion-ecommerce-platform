<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        $actions = [];

        if (! in_array($record->status, ['paid', 'processed', 'shipped', 'completed'])) {
            $actions[] = Actions\DeleteAction::make()
                ->label('Hapus Pesanan')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () use ($record) {
                    if ($record->status === 'pending') {
                        Order::restoreStockForOrder($record);
                    }
                    $record->delete();
                });
        }

        return $actions;
    }
}
