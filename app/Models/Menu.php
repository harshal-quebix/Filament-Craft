<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_name',
        'page_type',
        'url',
        'content',
        'placement',
        'sort_order',
        'is_active',
        'slug',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($menu) {
            if (empty($menu->slug)) {
                $menu->slug = Str::slug($menu->page_name);
            }
        });

        static::updating(function ($menu) {
            if (empty($menu->slug)) {
                $menu->slug = Str::slug($menu->page_name);
            }
        });
    }

    /**
     * Get menus for header
     */
    public static function getHeaderMenus()
    {
        return self::where('placement', 'header')
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    /**
     * Get menus for footer
     */
    public static function getFooterMenus()
    {
        return self::where('placement', 'footer')
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    /**
     * Get route URL for the menu
     */
    public function getRouteUrl(): string
    {
        if ($this->page_type === 'url') {
            return url($this->url ?? '#');
        }

        return route('page.show', $this->slug);
    }

    /**
     * Check if menu is of type URL
     */
    public function isUrlType(): bool
    {
        return $this->page_type === 'url';
    }

    /**
     * Check if menu is of type Content
     */
    public function isContentType(): bool
    {
        return $this->page_type === 'content';
    }
}
