<?php

namespace Tests\Feature;

use App\Models\GymPackage;
use App\Models\Member;
use App\Models\Snack;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WebsiteBisagymFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $penjaga;
    protected GymPackage $package;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Spatie roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $penjagaRole = Role::firstOrCreate(['name' => 'penjaga']);
        $developerRole = Role::firstOrCreate(['name' => 'developer']);
        $kasirRole = Role::firstOrCreate(['name' => 'kasir']);

        // Admin
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'is_location_restricted' => false,
            'two_factor_verified_at' => now(),
        ]);
        $this->admin->assignRole($adminRole);

        // Penjaga
        $this->penjaga = User::factory()->create([
            'role' => 'penjaga',
            'is_active' => true,
            'is_location_restricted' => false,
            'two_factor_verified_at' => now(),
        ]);
        $this->penjaga->assignRole($penjagaRole);
        $this->penjaga->assignRole($kasirRole);

        // Standard Gym Package
        $this->package = GymPackage::create([
            'name' => 'Basic Monthly',
            'duration' => 1,
            'duration_unit' => 'bulan',
            'price' => 250000,
            'admin_fee' => 30000,
            'max_members' => 1,
            'category' => 'member',
            'is_active' => true,
        ]);
    }

    public function test_members_index_page_loads_for_admin(): void
    {
        $response = $this->actingAs($this->admin)->get('/members');
        $response->assertStatus(200);
        $response->assertSee('Data Member');
    }

    public function test_members_create_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/members/create');
        $response->assertStatus(200);
    }

    public function test_member_registration_and_detail_view(): void
    {
        $response = $this->actingAs($this->admin)->post('/members', [
            'name' => 'John Doe Test',
            'place_of_birth' => 'Jakarta',
            'date_of_birth' => '1995-05-15',
            'gender' => 'L',
            'nik' => '1234567890123456',
            'job' => 'PNS',
            'address' => 'Jl. Merdeka No. 10',
            'phone' => '081234567890',
            'email' => 'johndoe@test.com',
            'package_id' => $this->package->id,
            'activation_date' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('members', [
            'email' => 'johndoe@test.com',
            'nik' => '1234567890123456',
        ]);

        $member = Member::where('email', 'johndoe@test.com')->first();
        $this->assertNotNull($member);

        // Test Show
        $showResponse = $this->actingAs($this->admin)->get('/members/' . $member->id);
        $showResponse->assertStatus(200);
        $showResponse->assertSee('John Doe Test');

        // Test E-Card
        $ecardResponse = $this->actingAs($this->admin)->get('/members/' . $member->id . '/ecard');
        $ecardResponse->assertStatus(200);
    }

    public function test_gym_packages_index_and_create(): void
    {
        $response = $this->actingAs($this->admin)->get('/gym-packages');
        $response->assertStatus(200);

        $createResponse = $this->actingAs($this->admin)->get('/gym-packages/create');
        $createResponse->assertStatus(200);
    }

    public function test_snacks_and_pos_index(): void
    {
        Snack::create([
            'snack_code' => 'SNK-TEST',
            'name' => 'Air Mineral',
            'category' => 'Minuman',
            'stock_gudang' => 20,
            'stock_kulkas' => 10,
            'stock' => 30,
            'capital_price' => 2500,
            'selling_price' => 5000,
        ]);

        $response = $this->actingAs($this->admin)->get('/snacks');
        $response->assertStatus(200);

        $posResponse = $this->actingAs($this->admin)->get('/pos');
        $posResponse->assertStatus(200);
    }

    public function test_attendance_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/attendance');
        $response->assertStatus(200);
    }

    public function test_face_enrollment_index_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/face-enrollment');
        $response->assertStatus(200);
    }

    public function test_dashboard_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_streaming_page_loads_for_admin(): void
    {
        $response = $this->actingAs($this->admin)->get('/streaming');
        $response->assertStatus(200);
        $response->assertSee('Streaming Kamera');
    }

    public function test_streaming_page_loads_for_penjaga(): void
    {
        $response = $this->actingAs($this->penjaga)->get('/streaming');
        $response->assertStatus(200);
        $response->assertSee('Streaming Kamera');
    }

    public function test_devices_page_loads_for_admin(): void
    {
        $response = $this->actingAs($this->admin)->get('/devices');
        $response->assertStatus(200);
        $response->assertSee('Perangkat');
    }

    public function test_devices_page_loads_for_penjaga(): void
    {
        $response = $this->actingAs($this->penjaga)->get('/devices');
        $response->assertStatus(200);
        $response->assertSee('Perangkat');
    }

    public function test_streaming_requires_auth(): void
    {
        $response = $this->get('/streaming');
        $response->assertRedirect('/login');
    }

    public function test_devices_requires_auth(): void
    {
        $response = $this->get('/devices');
        $response->assertRedirect('/login');
    }

    public function test_attendance_page_loads_with_kiosk_tab(): void
    {
        $response = $this->actingAs($this->admin)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('Scan Absensi Member');
        $response->assertSee('Kiosk Wajah');
    }

    public function test_face_checkin_requires_auth(): void
    {
        $response = $this->postJson('/attendance/face-checkin', []);
        $response->assertStatus(401);
    }

    public function test_face_checkin_validates_image(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/attendance/face-checkin', []);
        $response->assertStatus(422);
        $response->assertJson(['decision' => 'DENY', 'code' => 'EMPTY_IMAGE']);
    }
}

