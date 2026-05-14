<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_opening_admin_attendance_does_not_create_rows(): void
    {
        $admin = Admin::create([
            'username' => 'admin',
            'password' => bcrypt('password'),
            'nama' => 'Admin',
        ]);

        Player::create([
            'nama_pemain' => 'Pemain Test',
            'avatar_color' => '#4361ee',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->assertSame(0, Attendance::count());
    }

    public function test_admin_can_save_and_edit_attendance_batch(): void
    {
        $admin = Admin::create([
            'username' => 'admin',
            'password' => bcrypt('password'),
            'nama' => 'Admin',
        ]);

        $player = Player::create([
            'nama_pemain' => 'Pemain Telat',
            'avatar_color' => '#4361ee',
        ]);

        $date = now('Asia/Jakarta')->toDateString();

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.attendance.saveBatch'), [
                'tanggal' => $date,
                'attendances' => [
                    ['player_id' => $player->id, 'status' => 'tidak_hadir'],
                ],
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('attendance', [
            'player_id' => $player->id,
            'status_hadir' => 'tidak_hadir',
        ]);

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.attendance.saveBatch'), [
                'tanggal' => $date,
                'attendances' => [
                    ['player_id' => $player->id, 'status' => 'hadir'],
                ],
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('attendance', [
            'player_id' => $player->id,
            'status_hadir' => 'hadir',
        ]);
    }
}
