<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Repeater;

class SafeRepeater extends Repeater
{
    public function getItemLabel(string $key): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        $container = $this->getChildSchema($key);

        if (! $container) {
            return null;
        }

        return $this->evaluate($this->itemLabel, [
            'container' => $container,
            'item' => $container,
            'key' => $key,
            'schema' => $container,
            'state' => $container->getStateSnapshot(),
            'uuid' => $key,
        ]);
    }
}
