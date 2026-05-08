<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class ThemeController extends Controller
{
    public function css()
    {
        try {
            $setting = Setting::where('key', 'theme_color')->first();
            $colorName = $setting?->value ?? 'blue';
            $colorMap = [
                'slate' => '#64748b', 'gray' => '#6b7280', 'zinc' => '#71717a', 'neutral' => '#737373',
                'stone' => '#78716c', 'red' => '#ef4444', 'orange' => '#f97316', 'amber' => '#f59e0b',
                'yellow' => '#eab308', 'lime' => '#84cc16', 'green' => '#22c55e', 'emerald' => '#10b981',
                'teal' => '#14b8a6', 'cyan' => '#06b6d4', 'sky' => '#0ea5e9', 'blue' => '#3b82f6',
                'indigo' => '#6366f1', 'violet' => '#8b5cf6', 'purple' => '#a855f7', 'fuchsia' => '#d946ef',
                'pink' => '#ec4899', 'rose' => '#f43f5e'
            ];
            $themeColor = $colorMap[$colorName] ?? '#3b82f6';
        } catch (\Exception $e) {
            $themeColor = '#3b82f6';
        }

        $css = "
:root {
    --theme-color: {$themeColor};
    --theme-color-20: {$themeColor}20;
    --theme-color-cc: {$themeColor}CC;
    --theme-color-e6: {$themeColor}E6;
}

.theme-bg-gradient {
    background: linear-gradient(135deg, var(--theme-color), var(--theme-color-cc));
}

.theme-fill-20 { fill: var(--theme-color-20); }
.theme-fill-cc { fill: var(--theme-color-cc); }
.theme-fill { fill: var(--theme-color); }
.theme-fill-e6 { fill: var(--theme-color-e6); }
.theme-stroke { stroke: var(--theme-color); }
        ";

        return response($css)->header('Content-Type', 'text/css');
    }
}
