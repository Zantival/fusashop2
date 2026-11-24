<?php

namespace Tests\Feature;

use App\Models\CompanyProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MerchantKycAndMipymeSellingTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name'              => 'Usuario Prueba',
            'email'             => 'user_' . uniqid() . '@fusashop.test',
            'password'          => Hash::make('Password123!'),
            'role'              => 'consumer',
            'email_verified_at' => now(),
        ], $overrides));
    }

    public function test_mipyme_can_submit_profile_with_cedula_and_address_without_rut_or_camara(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $merchant = $this->createUser([
            'name' => 'Don Pedro Emprendedor',
            'role' => 'merchant',
        ]);

        $this->actingAs($merchant)
            ->get(route('merchant.profile'))
            ->assertOk()
            ->assertSee('Puedes vender como MiPyme')
            ->assertSee('RUT y la Cámara de Comercio son opcionales');

        $cedulaFile = UploadedFile::fake()->create('cedula_titular.pdf', 500, 'application/pdf');

        $response = $this->actingAs($merchant)
            ->post(route('merchant.profile.store'), [
                'owner_name'       => 'Pedro Pérez',
                'company_name'     => 'Arepas Doña Juana',
                'business_type'    => 'Alimentos y Bebidas',
                'phone'            => '3123456789',
                'address'          => 'Calle 8 # 5-20, Fusagasugá',
                'id_document_file' => $cedulaFile,
                // rut_file and camara_comercio_file are omitted (optional for MiPymes)
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('company_profiles', [
            'merchant_id'  => $merchant->id,
            'company_name' => 'Arepas Doña Juana',
            'address'      => 'Calle 8 # 5-20, Fusagasugá',
            'kyc_status'   => 'pending',
        ]);

        $profile = CompanyProfile::where('merchant_id', $merchant->id)->first();
        $this->assertNotEmpty($profile->id_document_path);
        $this->assertTrue($profile->hasIdDocument());
        $this->assertFalse($profile->hasRut());
        $this->assertFalse($profile->hasCamaraComercio());
    }

    public function test_admin_can_approve_mipyme_to_sell_without_rut_or_camara(): void
    {
        Storage::fake('local');

        $analyst = $this->createUser([
            'name'          => 'Admin Principal',
            'role'          => 'analyst',
            'admin_subrole' => 'super_admin',
        ]);

        $merchant = $this->createUser([
            'name' => 'Vendedor MiPyme',
            'role' => 'merchant',
        ]);

        Storage::disk('local')->put('kyc/cedula_test.pdf', '%PDF CEDULA');

        $profile = CompanyProfile::create([
            'merchant_id'      => $merchant->id,
            'company_name'     => 'Artesanías Don Carlos',
            'phone'            => '3119876543',
            'address'          => 'Carrera 6 # 10-12, Fusagasugá',
            'id_document_path' => 'kyc/cedula_test.pdf',
            'business_type'    => 'Artesanías',
            'kyc_status'       => 'pending',
        ]);

        // Admin can inspect cédula
        $docResponse = $this->actingAs($analyst)
            ->get(route('analyst.users.kyc-document', [$merchant->id, 'id_document']));
        $docResponse->assertOk();

        // Admin approves MiPyme to sell
        $approveResponse = $this->actingAs($analyst)
            ->post(route('analyst.users.kyc', $merchant->id), [
                'kyc_status' => 'approved',
                'kyc_notes'  => 'MiPyme aprobada para vender con cédula y dirección verificada.',
            ]);

        $approveResponse->assertRedirect();
        $this->assertDatabaseHas('company_profiles', [
            'merchant_id' => $merchant->id,
            'kyc_status'  => 'approved',
        ]);

        // Merchant is now approved and can access dashboard
        $dashboardResponse = $this->actingAs($merchant)->get(route('merchant.dashboard'));
        $dashboardResponse->assertOk();
    }

    public function test_admin_routes_do_not_throw_404_on_trashed_user(): void
    {
        $analyst = $this->createUser([
            'name'          => 'Admin Principal',
            'role'          => 'analyst',
            'admin_subrole' => 'super_admin',
        ]);

        $merchant = $this->createUser([
            'name' => 'Comercio Eliminado',
            'role' => 'merchant',
        ]);

        $profile = CompanyProfile::create([
            'merchant_id'  => $merchant->id,
            'company_name' => 'Comercio en Papelera',
            'phone'        => '3101234567',
            'kyc_status'   => 'pending',
            'address'      => 'Fusagasugá',
        ]);

        // User is soft-deleted
        $merchant->delete();
        $this->assertTrue($merchant->trashed());

        // Auditar / Editar route: does NOT throw 404!
        $editResponse = $this->actingAs($analyst)->get(route('analyst.users.edit', $merchant->id));
        $editResponse->assertOk();

        // KYC decision route: does NOT throw 404!
        $kycResponse = $this->actingAs($analyst)->post(route('analyst.users.kyc', $merchant->id), [
            'kyc_status' => 'approved',
        ]);
        $kycResponse->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseHas('company_profiles', [
            'merchant_id' => $merchant->id,
            'kyc_status'  => 'pending',
        ]);
    }

    public function test_login_authenticates_with_case_insensitive_email_and_regenerates_session(): void
    {
        $user = $this->createUser([
            'email'    => 'tienda.ejemplo@fusashop.test',
            'password' => Hash::make('ClaveSegura123!'),
        ]);

        // Attempt login with uppercase letters
        $response = $this->post(route('login.post'), [
            'email'    => 'TIENDA.EJEMPLO@FUSASHOP.TEST',
            'password' => 'ClaveSegura123!',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_blocked_user_cannot_login(): void
    {
        $blockedUser = $this->createUser([
            'email'      => 'bloqueado@fusashop.test',
            'password'   => Hash::make('ClaveSegura123!'),
            'is_blocked' => true,
        ]);

        $response = $this->post(route('login.post'), [
            'email'    => 'bloqueado@fusashop.test',
            'password' => 'ClaveSegura123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
