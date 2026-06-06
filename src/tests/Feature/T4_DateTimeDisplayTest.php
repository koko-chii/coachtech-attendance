<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class T4_DateTimeDisplayTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function 現在の日時情報がUIと同じ形式で出力されている()
    {
        $user = User::factory()->create();
        Carbon::setTestNow(Carbon::create(2026, 6, 6, 15, 30, 0));

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('2026年6月6日');
        $response->assertSee('15:30');

        Carbon::setTestNow();
    }
}
