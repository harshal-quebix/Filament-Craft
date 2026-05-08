<?php

namespace App\Services\CrudGenerator\Resolvers;

use Illuminate\Support\Str;

class IconResolver
{
    private static ?array $iconMap = null;
    private static ?array $keywordMap = null;

    public function resolve(string $modelName): string
    {
        $this->loadMaps();
        
        $singular = Str::singular($modelName);
        
        if (isset(self::$iconMap[$modelName])) {
            return self::$iconMap[$modelName];
        }
        
        if (isset(self::$iconMap[$singular])) {
            return self::$iconMap[$singular];
        }
        
        $lowerName = strtolower($modelName);
        $lowerSingular = strtolower($singular);
        
        foreach (self::$keywordMap as $keyword => $icon) {
            if (str_contains($lowerName, $keyword) || str_contains($lowerSingular, $keyword)) {
                return $icon;
            }
        }
        
        return 'heroicon-o-rectangle-stack';
    }

    private function loadMaps(): void
    {
        if (self::$iconMap !== null) {
            return;
        }

        self::$iconMap = [
            'Blog' => 'heroicon-o-document-text',
            'Post' => 'heroicon-o-document-text',
            'Article' => 'heroicon-o-newspaper',
            'News' => 'heroicon-o-newspaper',
            'Product' => 'heroicon-o-shopping-bag',
            'Category' => 'heroicon-o-tag',
            'Tag' => 'heroicon-o-tag',
            'User' => 'heroicon-o-users',
            'Customer' => 'heroicon-o-users',
            'Client' => 'heroicon-o-users',
            'Order' => 'heroicon-o-shopping-cart',
            'Invoice' => 'heroicon-o-document-currency-dollar',
            'Payment' => 'heroicon-o-credit-card',
            'Comment' => 'heroicon-o-chat-bubble-left-right',
            'Review' => 'heroicon-o-star',
            'Message' => 'heroicon-o-envelope',
            'Email' => 'heroicon-o-envelope',
            'Gallery' => 'heroicon-o-photo',
            'Image' => 'heroicon-o-photo',
            'Media' => 'heroicon-o-photo',
            'File' => 'heroicon-o-document',
            'Document' => 'heroicon-o-document',
            'Page' => 'heroicon-o-document',
            'Menu' => 'heroicon-o-list-bullet',
            'Setting' => 'heroicon-o-cog-6-tooth',
            'Config' => 'heroicon-o-cog-6-tooth',
            'Report' => 'heroicon-o-chart-bar',
            'Analytics' => 'heroicon-o-chart-bar',
            'Event' => 'heroicon-o-calendar',
            'Calendar' => 'heroicon-o-calendar',
            'Task' => 'heroicon-o-check-circle',
            'Todo' => 'heroicon-o-check-circle',
            'Project' => 'heroicon-o-folder',
            'Folder' => 'heroicon-o-folder',
            'Team' => 'heroicon-o-user-group',
            'Department' => 'heroicon-o-building-office',
            'Company' => 'heroicon-o-building-office-2',
            'Location' => 'heroicon-o-map-pin',
            'City' => 'heroicon-o-map-pin',
            'Country' => 'heroicon-o-globe-alt',
            'Language' => 'heroicon-o-language',
            'Translation' => 'heroicon-o-language',
            'Course' => 'heroicon-o-academic-cap',
        ];

        self::$keywordMap = [
            'book' => 'heroicon-o-book-open',
            'car' => 'heroicon-o-truck',
            'phone' => 'heroicon-o-phone',
            'house' => 'heroicon-o-home',
            'home' => 'heroicon-o-home',
            'money' => 'heroicon-o-currency-dollar',
            'cash' => 'heroicon-o-currency-dollar',
            'bank' => 'heroicon-o-building-office',
            'card' => 'heroicon-o-credit-card',
            'game' => 'heroicon-o-puzzle-piece',
            'sport' => 'heroicon-o-trophy',
            'music' => 'heroicon-o-speaker-wave',
            'movie' => 'heroicon-o-video-camera',
            'film' => 'heroicon-o-video-camera',
        ];
    }
}
