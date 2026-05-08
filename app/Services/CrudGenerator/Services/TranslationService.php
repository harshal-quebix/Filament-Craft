<?php

namespace App\Services\CrudGenerator\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TranslationService
{
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

        $crudKeys = [
            "{$modelName} created successfully!",
            "{$modelName} Created Successfully",
            "{$modelName} updated successfully!",
            "{$modelName} Updated Successfully",
            "{$modelName} deleted successfully",
            "{$modelName} Deleted Successfully",
        ];

        foreach ($crudKeys as $k) {
            $en[$k] = $k;
        }

        $ar["{$modelName} created successfully!"] = "تم إنشاء {$arName} بنجاح!";
        $ar["{$modelName} Created Successfully"] = "تم إنشاء {$arName} بنجاح";
        $ar["{$modelName} updated successfully!"] = "تم تحديث {$arName} بنجاح!";
        $ar["{$modelName} Updated Successfully"] = "تم تحديث {$arName} بنجاح";
        $ar["{$modelName} deleted successfully"] = "تم حذف {$arName} بنجاح";
        $ar["{$modelName} Deleted Successfully"] = "تم حذف {$arName} بنجاح";

        $fr["{$modelName} created successfully!"] = "{$frName} créé avec succès!";
        $fr["{$modelName} Created Successfully"] = "{$frName} créé avec succès";
        $fr["{$modelName} updated successfully!"] = "{$frName} mis à jour avec succès!";
        $fr["{$modelName} Updated Successfully"] = "{$frName} mis à jour avec succès";
        $fr["{$modelName} deleted successfully"] = "{$frName} supprimé avec succès";
        $fr["{$modelName} Deleted Successfully"] = "{$frName} supprimé avec succès";

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

        $keys = array_merge($keys, [
            "{$modelName} created successfully!",
            "{$modelName} Created Successfully",
            "{$modelName} updated successfully!",
            "{$modelName} Updated Successfully",
            "{$modelName} deleted successfully",
            "{$modelName} Deleted Successfully",
        ]);

        foreach (['en.json', 'ar.json', 'fr.json'] as $file) {
            $filePath = resource_path("lang/{$file}");
            if (!File::exists($filePath)) continue;
            
            $this->ensureWritableFile($filePath);
            $current = json_decode(File::get($filePath), true) ?? [];
            foreach ($keys as $key) {
                unset($current[$key]);
            }
            File::put($filePath, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    private function translateToArabic(string $text): string
    {
        return $this->translate($text, 'ar', [
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
        ]);
    }

    private function translateToFrench(string $text): string
    {
        return $this->translate($text, 'fr', [
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
        ]);
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
            }

            return $fallbackMap[$text] ?? $text;
        });
    }

    private function updateLanguageFile(string $file, array $keys): void
    {
        $langPath = resource_path('lang');
        $filePath = $langPath . '/' . $file;

        if (!File::exists($langPath)) {
            File::makeDirectory($langPath, 0755, true);
        }
        if (!File::exists($filePath)) {
            File::put($filePath, '{}');
        }

        $this->ensureWritableFile($filePath);
        $current = json_decode(File::get($filePath), true) ?? [];
        File::put($filePath, json_encode(array_merge($current, $keys), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function ensureWritableFile(string $filePath): void
    {
        $dir = dirname($filePath);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
    }
}
