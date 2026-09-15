<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_number')
                    ->label('No. Pesanan')
                    ->disabled(),

                Forms\Components\Select::make('status')
                    ->label('Status Pesanan')
                    ->options(fn (?Order $record) => $record ? $record->getStatusTransitionOptions() : [
                        'pending' => 'Menunggu Pembayaran',
                        'paid' => 'Sudah Dibayar',
                        'processed' => 'Diproses',
                        'shipped' => 'Dikirim',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set, $record) {
                        if ($state === 'shipped' && $record && ! $record->shipped_at) {
                            $set('shipped_at', now());
                        }

                        if ($state === 'cancelled' && $record) {
                            Order::restoreStockForOrder($record);
                        }
                    }),

                Forms\Components\TextInput::make('tracking_number')
                    ->label('Nomor Resi')
                    ->placeholder('Contoh: JNE123456789')
                    ->visible(fn (Forms\Get $get) => in_array($get('status'), ['shipped', 'completed']))
                    ->nullable(),

                Forms\Components\Hidden::make('shipped_at'),

                Forms\Components\Section::make('Data Penerima')
                    ->schema([
                        Forms\Components\TextInput::make('recipient_name')
                            ->label('Nama Penerima')
                            ->disabled(),
                        Forms\Components\TextInput::make('phone')
                            ->label('No. HP')
                            ->disabled(),
                        Forms\Components\TextInput::make('country')
                            ->label('Negara')
                            ->disabled(),
                        Forms\Components\TextInput::make('city')
                            ->label('Kota')
                            ->disabled(),
                        Forms\Components\Textarea::make('address_detail')
                            ->label('Detail Alamat')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Ringkasan Pembayaran')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                        Forms\Components\TextInput::make('shipping_cost')
                            ->label('Ongkos Kirim')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                        Forms\Components\TextInput::make('total')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('No. Pesanan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pembeli'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'info' => 'processed',
                        'primary' => 'shipped',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('total')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Pesan')
                    ->dateTime('d M Y, H:i'),
                Tables\Columns\TextColumn::make('tracking_number')
                    ->label('No. Resi')
                    ->default('-')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu Pembayaran',
                        'paid' => 'Sudah Dibayar',
                        'processed' => 'Diproses',
                        'shipped' => 'Dikirim',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('delete')
                        ->label('Hapus Terpilih')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(function (Order $order) {
                                if ($order->status === 'pending') {
                                    Order::restoreStockForOrder($order);
                                }
                                $order->delete();
                            });
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
