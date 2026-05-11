<?php

namespace App\Filament\Resources\Courses\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use App\Helpers\Helper;
use Filament\Schemas\Schema;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    TextInput::make('title')->label(__('Course.Title'))->required()->extraInputAttributes(['required' => false])->columnSpan(12)
                ])->columns(12)->columnSpan(6)->columnStart(4)
            ])->columns(12);
    }
}
