<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class HelperTest extends TestCase
{
    public function test_get_image_url_returns_null_for_empty_path(): void
    {
        $this->assertNull(getImageUrl(''));
        $this->assertNull(getImageUrl(null));
    }

    public function test_delete_image_returns_true_for_empty_path(): void
    {
        $this->assertTrue(deleteImage(''));
        $this->assertTrue(deleteImage(null));
    }

    public function test_upload_file_from_path_returns_null_for_missing_file(): void
    {
        $this->assertNull(uploadFileFromPath('/nonexistent/path/file.jpg'));
    }

    public function test_upload_file_from_path_validates_extension(): void
    {
        Storage::fake('public');
        
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile . '.exe', 'fake content');
        
        $result = uploadFileFromPath($tempFile . '.exe', 'test', 'public');
        $this->assertNull($result);
        
        @unlink($tempFile . '.exe');
    }

    public function test_upload_file_from_path_accepts_valid_image(): void
    {
        Storage::fake('public');
        
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        $imageContent = file_get_contents('https://via.placeholder.com/10x10.png');
        file_put_contents($tempFile . '.png', $imageContent);
        
        $result = uploadFileFromPath($tempFile . '.png', 'test', 'public');
        $this->assertNotNull($result);
        $this->assertStringContainsString('test/', $result);
        
        @unlink($tempFile . '.png');
    }

    public function test_setting_helper_returns_default(): void
    {
        $result = setting('nonexistent_key', 'default_value');
        $this->assertEquals('default_value', $result);
    }

    public function test_get_setting_helper_returns_default(): void
    {
        $result = getSetting('nonexistent_key', 'fallback');
        $this->assertEquals('fallback', $result);
    }
}
