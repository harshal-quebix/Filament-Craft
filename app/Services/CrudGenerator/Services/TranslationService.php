<?php

namespace App\Services\CrudGenerator\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TranslationService
{
    private const CRUD_MESSAGE_KEYS = [
        'created_success' => '{model} created successfully!',
        'created_success_title' => '{model} Created Successfully',
        'updated_success' => '{model} updated successfully!',
        'updated_success_title' => '{model} Updated Successfully',
        'deleted_success' => '{model} deleted successfully',
        'deleted_success_title' => '{model} Deleted Successfully',
    ];

    private const ARABIC_FALLBACK = [
        'Department' => 'القسم',
        'Name' => 'الاسم',
        'Title' => 'العنوان',
        'Description' => 'الوصف',
        'Status' => 'الحالة',
        'Email' => 'البريد الإلكتروني',
        'Phone' => 'الهاتف',
        'Address' => 'العنوان',
        'Category' => 'الفئة',
        'Product' => 'المنتج',
        'Order' => 'الطلب',
        'Customer' => 'العميل',
        'Employee' => 'الموظف',
        'Branch' => 'الفرع',
        'Designation' => 'المسمى الوظيفي',
        'Date' => 'التاريخ',
        'Time' => 'الوقت',
        'Price' => 'السعر',
        'Quantity' => 'الكمية',
        'Total' => 'المجموع',
        'Discount' => 'الخصم',
        'Tax' => 'الضريبة',
        'Amount' => 'المبلغ',
        'Code' => 'الرمز',
        'Type' => 'النوع',
        'Image' => 'الصورة',
        'File' => 'الملف',
        'Active' => 'نشط',
        'Inactive' => 'غير نشط',
        'Pending' => 'قيد الانتظار',
    ];

    private const FRENCH_FALLBACK = [
        'Department' => 'Département',
        'Name' => 'Nom',
        'Title' => 'Titre',
        'Description' => 'Description',
        'Status' => 'Statut',
        'Email' => 'E-mail',
        'Phone' => 'Téléphone',
        'Address' => 'Adresse',
        'Category' => 'Catégorie',
        'Product' => 'Produit',
        'Order' => 'Commande',
        'Customer' => 'Client',
        'Employee' => 'Employé',
        'Branch' => 'Branche',
        'Designation' => 'Désignation',
        'Date' => 'Date',
        'Time' => 'Heure',
        'Price' => 'Prix',
        'Quantity' => 'Quantité',
        'Total' => 'Total',
        'Discount' => 'Remise',
        'Tax' => 'Taxe',
        'Amount' => 'Montant',
        'Code' => 'Code',
        'Type' => 'Type',
        'Image' => 'Image',
        'File' => 'Fichier',
        'Active' => 'Actif',
        'Inactive' => 'Inactif',
        'Pending' => 'En attente',
    ];

    public function generateLocalizationKeys(string $modelName, array $fields): void
    {
        $arName = $this->translateToArabic($modelName);
        $frName = $this->translateToFrench($modelName);

        $en = [$modelName => $modelName, Str::plural($modelName) => Str::plural($modelName)];
        $ar = [$modelName => $arName, Str::plural($modelName) => $arName];
        $fr = [$modelName => $frName, Str::plural($modelName) => $frName];

        foreach ($fields as $field) {
            $name = Str::snake($field['name']);
            $label = Str::title(str_replace('_', ' ', $name));
            $key = "{$modelName}.{$label}";
            $en[$key] = $label;
            $ar[$key] = $this->translateToArabic($label);
            $fr[$key] = $this->translateToFrench($label);
        }

        foreach (self::CRUD_MESSAGE_KEYS as $template) {
            $message = str_replace('{model}', $modelName, $template);
            $en[$message] = $message;
        }

        $ar = array_merge($ar, $this->buildCrudMessages($modelName, $arName, 'ar'));
        $fr = array_merge($fr, $this->buildCrudMessages($modelName, $frName, 'fr'));

        $this->updateLanguageFile('en.json', $en);
        $this->updateLanguageFile('ar.json', $ar);
        $this->updateLanguageFile('fr.json', $fr);
    }

    public function removeLocalizationKeys(string $modelName, array $fields): void
    {
        $keys = [$modelName, Str::plural($modelName)];

        foreach ($fields as $field) {
            $name = Str::snake($field['name']);
            $label = Str::title(str_replace('_', ' ', $name));
            $keys[] = "{$modelName}.{$label}";
        }

        foreach (self::CRUD_MESSAGE_KEYS as $template) {
            $keys[] = str_replace('{model}', $modelName, $template);
        }

        foreach (['en.json', 'ar.json', 'fr.json'] as $file) {
            $filePath = config('crud-generator.paths.lang') . "/{$file}";
            if (! File::exists($filePath)) {
                continue;
            }

            try {
                $current = json_decode(File::get($filePath), true) ?? [];
                foreach ($keys as $key) {
                    unset($current[$key]);
                }
                File::put($filePath, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            } catch (\Exception $e) {
                Log::warning("CRUD Generator: Failed to remove keys from language file {$filePath}: " . $e->getMessage());
            }
        }
    }

    private function buildCrudMessages(string $modelName, string $translatedName, string $locale): array
    {
        $messages = [];

        if ($locale === 'ar') {
            $messages["{$modelName} created successfully!"] = "تم إنشاء {$translatedName} بنجاح!";
            $messages["{$modelName} Created Successfully"] = "تم إنشاء {$translatedName} بنجاح";
            $messages["{$modelName} updated successfully!"] = "تم تحديث {$translatedName} بنجاح!";
            $messages["{$modelName} Updated Successfully"] = "تم تحديث {$translatedName} بنجاح";
            $messages["{$modelName} deleted successfully"] = "تم حذف {$translatedName} بنجاح";
            $messages["{$modelName} Deleted Successfully"] = "تم حذف {$translatedName} بنجاح";
        } else {
            $messages["{$modelName} created successfully!"] = "{$translatedName} créé avec succès!";
            $messages["{$modelName} Created Successfully"] = "{$translatedName} créé avec succès";
            $messages["{$modelName} updated successfully!"] = "{$translatedName} mis à jour avec succès!";
            $messages["{$modelName} Updated Successfully"] = "{$translatedName} mis à jour avec succès";
            $messages["{$modelName} deleted successfully"] = "{$translatedName} supprimé avec succès";
            $messages["{$modelName} Deleted Successfully"] = "{$translatedName} supprimé avec succès";
        }

        return $messages;
    }

    private function translateToArabic(string $text): string
    {
        return $this->translate($text, 'ar', self::ARABIC_FALLBACK);
    }

    private function translateToFrench(string $text): string
    {
        return $this->translate($text, 'fr', self::FRENCH_FALLBACK);
    }

    private function translate(string $text, string $locale, array $fallbackMap): string
    {
        if (empty($text)) {
            return $text;
        }

        $cacheKey = 'crud_generator.translation.' . $locale . '.' . md5($text);

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($text, $locale, $fallbackMap) {
            try {
                $response = Http::timeout(3)->get('https://translate.googleapis.com/translate_a/single', [
                    'client' => 'gtx',
                    'sl' => 'en',
                    'tl' => $locale,
                    'dt' => 't',
                    'q' => $text,
                ]);
                if ($response->successful()) {
                    $translated = $response->json()[0][0][0] ?? null;
                    if ($translated && $translated !== $text) {
                        return $translated;
                    }
                }
            } catch (\Exception $e) {
                // Silently fall back to local map
            }

            return $fallbackMap[$text] ?? $text;
        });
    }

    private function updateLanguageFile(string $file, array $keys): void
    {
        $langPath = config('crud-generator.paths.lang', resource_path('lang'));
        $filePath = $langPath . '/' . $file;

        try {
            if (! File::exists($langPath)) {
                File::makeDirectory($langPath, 0755, true);
            }
            if (! File::exists($filePath)) {
                File::put($filePath, '{}');
            }

            $current = json_decode(File::get($filePath), true) ?? [];
            File::put($filePath, json_encode(array_merge($current, $keys), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } catch (\Exception $e) {
            Log::warning("CRUD Generator: Failed to update language file {$filePath}: " . $e->getMessage());
        }
    }
}
