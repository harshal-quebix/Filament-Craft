<?php

namespace App\Filament\Resources\Courses\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CourseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextEntry::make('title')->label(__('Course.Title'))->placeholder('-')->columnSpan(12),
                TextEntry::make('created_at')->label(__('Created At'))->dateTime()->placeholder('-')->columnSpan(1),
                TextEntry::make('updated_at')->label(__('Updated At'))->dateTime()->placeholder('-')->columnSpan(1)
            ]);
    }
}
