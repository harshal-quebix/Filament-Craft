<?php

namespace App\Filament\Resources\Courses\Pages;

use App\Filament\Resources\Courses\CourseResource;
use App\Filament\Resources\Concerns\HasTrashToggleAction;
use Filament\Resources\Pages\ListRecords;

class ListCourses extends ListRecords
{
    use HasTrashToggleAction;


    protected static string $resource = CourseResource::class;
}
