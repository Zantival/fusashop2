<?php

namespace Tests\Feature;

use App\Models\CompanyProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MerchantVerificationBadgeTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name'              => 'Usuario Prueba',
            'email'             => 'user_' . uniqid() . '@fusashop.test',
            'password'          => Hash::make('password123'),
            'role'              => 'consumer',
            'email_verified_at' => now(),
        ], $overrides));
    }

    public function test_analyst_can_give_and_remove_merchant_verification_badge(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('ruts/test.pdf', '%PDF RUT');
        Storage::disk('local')->put('camara/test.pdf', '%PDF CAMARA');

        $analyst = $this->createUser([
            'name'          => 'Analista Principal',
            'role'          => 'analyst',
            'admin_subrole' => 'super_admin',
        ]);

        $merchant = $this->createUser([
            'name' => 'Café Sumapaz',
            'role' => 'merchant',
        ]);

        CompanyProfile::create([
            'merchant_id'   => $merchant->id,
            'company_name'  => 'Café Sumapaz Especial',
            'phone'         => '3101234567',
            'rut_path'      => 'ruts/test.pdf',
            'camara_comercio_path' => 'camara/test.pdf',
            'business_type' => 'Alimentos',
            'kyc_status'    => 'pending',
        ]);

        $this->actingAs($analyst)->get(route('analyst.users.kyc-document', [$merchant->id, 'rut']))->assertOk();
        $this->actingAs($analyst)->get(route('analyst.users.kyc-document', [$merchant->id, 'camara_comercio']))->assertOk();

        // 1. Inicialmente sin verificar: no tiene el chulito en su vitrina pública
        $storeResponseBefore = $this->get(route('consumer.merchant.profile', $merchant->id));
        $storeResponseBefore->assertStatus(200);
        $storeResponseBefore->assertDontSee('Comercio Verificado Oficialmente');

        // 2. El analista le da el chulito de verificación con el botón de 1 clic
        $grantResponse = $this->actingAs($analyst)
            ->from(route('analyst.users'))
            ->patch(route('analyst.users.toggle-verification', $merchant->id));

        $grantResponse->assertRedirect(route('analyst.users'));
        $this->assertDatabaseHas('company_profiles', [
            'merchant_id' => $merchant->id,
            'kyc_status'  => 'approved',
        ]);

        // Verificar que ahora sí aparece el chulito en la vitrina pública y en la lista de usuarios del analista
        $storeResponseAfterGrant = $this->get(route('consumer.merchant.profile', $merchant->id));
        $storeResponseAfterGrant->assertStatus(200);
        $storeResponseAfterGrant->assertSee('Comercio Verificado Oficialmente');
        $storeResponseAfterGrant->assertSee('Verificada');

        $usersPageVerified = $this->actingAs($analyst)->get(route('analyst.users', ['role' => 'merchant']));
        $usersPageVerified->assertStatus(200);
        $usersPageVerified->assertSee('Quitar Verificación');

        // 3. El analista le quita el chulito de verificación con el mismo botón de 1 clic
        $revokeResponse = $this->actingAs($analyst)
            ->from(route('analyst.users'))
            ->patch(route('analyst.users.toggle-verification', $merchant->id));

        $revokeResponse->assertRedirect(route('analyst.users'));
        $this->assertDatabaseHas('company_profiles', [
            'merchant_id' => $merchant->id,
            'kyc_status'  => 'pending',
        ]);

        // Verificar que el chulito desaparece de la vitrina pública y el botón vuelve a decir "Dar Verificación ✓"
        $storeResponseAfterRevoke = $this->get(route('consumer.merchant.profile', $merchant->id));
        $storeResponseAfterRevoke->assertStatus(200);
        $storeResponseAfterRevoke->assertDontSee('Comercio Verificado Oficialmente');

        $usersPageUnverified = $this->actingAs($analyst)->get(route('analyst.users', ['role' => 'merchant']));
        $usersPageUnverified->assertStatus(200);
        $usersPageUnverified->assertSee('Dar Verificación ✓');
    }

    public function test_analyst_cannot_approve_without_both_documents_and_can_approve_after_review(): void
    {
        Storage::fake('local');
        $analyst = $this->createUser([
            'name'          => 'Analista Principal',
            'role'          => 'analyst',
            'admin_subrole' => 'super_admin',
        ]);

        $merchantWithoutProfile = $this->createUser([
            'name' => 'Artesanías del Sumapaz',
            'role' => 'merchant',
        ]);

        $this->actingAs($merchantWithoutProfile)
            ->get(route('merchant.dashboard'))
            ->assertRedirect(route('merchant.profile'));

        // No se debe aprobar ni crear un perfil aprobado cuando faltan documentos.
        $response = $this->actingAs($analyst)
            ->patch(route('analyst.users.toggle-verification', $merchantWithoutProfile->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('company_profiles', ['merchant_id' => $merchantWithoutProfile->id]);

        $this->actingAs($analyst)
            ->post(route('analyst.users.kyc', $merchantWithoutProfile->id), ['kyc_status' => 'approved'])
            ->assertRedirect();
        $this->assertDatabaseMissing('company_profiles', ['merchant_id' => $merchantWithoutProfile->id]);

        Storage::disk('local')->put('kyc/rut.pdf', '%PDF RUT');
        Storage::disk('local')->put('kyc/camara.pdf', '%PDF CAMARA');
        CompanyProfile::create([
            'merchant_id' => $merchantWithoutProfile->id,
            'company_name' => 'Artesanías del Sumapaz',
            'phone' => '3101234567',
            'business_type' => 'Artesanías',
            'rut_path' => 'kyc/rut.pdf',
            'camara_comercio_path' => 'kyc/camara.pdf',
            'kyc_status' => 'pending',
        ]);

        $this->actingAs($analyst)
            ->post(route('analyst.users.kyc', $merchantWithoutProfile->id), ['kyc_status' => 'approved'])
            ->assertRedirect();
        $this->assertDatabaseHas('company_profiles', [
            'merchant_id' => $merchantWithoutProfile->id,
            'kyc_status'  => 'approved',
        ]);

        // Y también puede quitarle la verificación desde el formulario KYC de edición de usuario
        $revokeKycResponse = $this->actingAs($analyst)
            ->post(route('analyst.users.kyc', $merchantWithoutProfile->id), [
                'kyc_status' => 'pending',
                'kyc_notes'  => 'Revisión periódica de documentación',
            ]);

        $revokeKycResponse->assertRedirect();
        $this->assertDatabaseHas('company_profiles', [
            'merchant_id' => $merchantWithoutProfile->id,
            'kyc_status'  => 'pending',
        ]);
    }
}
