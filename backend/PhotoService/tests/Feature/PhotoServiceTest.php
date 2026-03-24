<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class PhotoServiceTest extends TestCase
{
    protected string $jwtToken;
    protected string $userId = 'ef01ea51-43df-4b10-91b9-9d74d0802a40';

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('s3_backend');

        $this->jwtToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJlZjAxZWE1MS00M2RmLTRiMTAtOTFiOS05ZDc0ZDA4MDJhNDAiLCJlbWFpbCI6ImFyaWNyYXRlQGdtYWlsLmNvbSIsImlhdCI6MTc3MjAxMDIzNywiZXhwIjoxNzcyMDEzODM3fQ.G0P-PrHoIk3ZHIvKMag4_zN65ZLeGDidXcqZtqSNO8g';

        $user = new class($this->userId) {
            private $id;
            public function __construct($id) { $this->id = $id; }
            public function getId() { return $this->id; }
        };

        $this->instance('Illuminate\Http\Request', tap(request(), function ($req) use ($user) {
            $req->setUserResolver(fn() => $user);
        }));

        $this->withoutMiddleware(\App\Http\Middleware\JwtMiddleware::class);
    }


    public function test_photo_upload_lifecycle()
    {
        // Получаем ссылку на загрузку
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->jwtToken,
        ])->post('/api/photos/upload-url', [
            'fileName' => 'test.jpg',
            'mimeType' => 'image/jpeg',
            'description' => 'Тестовое фото',
        ]);

        $response->assertStatus(201);
        $photoId = $response->json('photo_id');
        $uploadUrl = $response->json('upload_url');

        $this->assertNotEmpty($photoId);
        $this->assertNotEmpty($uploadUrl);

        // Симуляция загрузки файла
        $file = UploadedFile::fake()->image('test.jpg');
        Storage::disk('s3_backend')->put("uploads/{$photoId}", file_get_contents($file));
        $this->assertTrue(Storage::disk('s3_backend')->exists("uploads/{$photoId}"));

        // Пометка фото как загруженного
        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->jwtToken,
        ])->post('/api/photos/mark-uploaded', [
            'photo_id' => $photoId,
            'url' => "https://fake-s3/{$photoId}",
            'size' => $file->getSize(),
        ]);

        $response2->assertStatus(200);
        $this->assertEquals('uploaded', $response2->json('status'));

        // Получение данных фото
        $response3 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->jwtToken,
        ])->get("/api/photos/{$photoId}");

        $response3->assertStatus(200);
        $response3->assertJsonStructure([
            'id', 'status', 'dominant_color', 'url', 'size', 'file_name'
        ]);

        // Проверка недавних фото
        $response4 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->jwtToken,
        ])->get('/api/photos/recent');

        $response4->assertStatus(200);
        $this->assertNotEmpty($response4->json());
    }
}