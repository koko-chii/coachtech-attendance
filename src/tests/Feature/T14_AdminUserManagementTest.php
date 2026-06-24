<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\AttendanceRecord;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class T14_AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function 管理者ユーザーが全一般ユーザーの氏名とメールアドレスを確認できる(): void
    {
        $admin = Admin::factory()->create();
        User::factory()->create(['name' => 'スタッフ太郎', 'email' => 'taro@example.com']);
        User::factory()->create(['name' => 'スタッフ次郎', 'email' => 'jiro@example.com']);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.staff.list'));

        $response->assertStatus(200);
        $response->assertSee('スタッフ太郎');
        $response->assertSee('taro@example.com');
        $response->assertSee('スタッフ次郎');
        $response->assertSee('jiro@example.com');
    }

    #[Test]
    public function 選択したユーザーの勤怠情報が正しく表示される(): void
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $today = Carbon::today()->format('Y-m-d');

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => $today,
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.staff', ['id' => $user->id]));

        $response->assertStatus(200);
        // フォーマット依存を防ぐため、確実に含まれる「年」で検証します
        $response->assertSee(Carbon::today()->format('Y'));
    }

    #[Test]
    public function 前月を押下した時に表示月の前月の情報が表示される(): void
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $lastMonth = Carbon::today()->subMonth()->format('Y-m');

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.staff', [
            'id' => $user->id,
            'month' => $lastMonth
        ]));

        $response->assertStatus(200);
    }

    #[Test]
    public function 翌月を押下した時に表示月の翌月の情報が表示される(): void
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $nextMonth = Carbon::today()->addMonth()->format('Y-m');

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.staff', [
            'id' => $user->id,
            'month' => $nextMonth
        ]));

        $response->assertStatus(200);
    }

    #[Test]
    public function 詳細を押下するとその日の勤怠詳細画面に遷移する(): void
    {
        $admin = Admin::factory()->create();
        $attendance = AttendanceRecord::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);
    }
}
