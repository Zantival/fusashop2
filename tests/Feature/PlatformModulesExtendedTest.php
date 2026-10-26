<?php

namespace Tests\Feature;

use App\Models\CompanyProfile;
use App\Models\IntegrationLog;
use App\Models\PQRS;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PlatformModulesExtendedTest extends TestCase
{
    use RefreshDatabase;

    private User $consumer;
    private User $merchant;
    private User $analyst;

    protected function setUp(): void
    {
        parent::setUp();

        $this->consumer = User::create([
            'name' => 'Consumidor Fusa',
            'email' => 'consumidor@fusashop.test',
            'password' => bcrypt('password123'),
            'role' => 'consumer',
            'phone' => '3001234567',
            'email_verified_at' => now(),
        ]);

        $this->merchant = User::create([
            'name' => 'Comerciante Fusa',
            'email' => 'comercio@fusashop.test',
            'password' => bcrypt('password123'),
            'role' => 'merchant',
            'phone' => '3109876543',
            'email_verified_at' => now(),
        ]);

        CompanyProfile::create([
            'user_id' => $this->merchant->id,
            'merchant_id' => $this->merchant->id,
            'company_name' => 'EcoGlamping Fusagasugá',
            'phone' => '3109876543',
            'address' => 'Vereda Chinauta, Fusagasugá',
            'rut_path' => 'kyc/rut_fake.pdf',
            'status' => 'approved',
            'kyc_status' => 'approved',
            'category' => 'Hoteles y Alojamientos',
        ]);

        $this->analyst = User::create([
            'name' => 'Analista Soporte',
            'email' => 'analista@fusashop.test',
            'password' => bcrypt('password123'),
            'role' => 'analyst',
            'phone' => '3201112233',
            'email_verified_at' => now(),
        ]);
    }

    public function test_consumer_can_manage_shipping_addresses(): void
    {
        // 1. Ver listado de direcciones
        $response = $this->actingAs($this->consumer)->get(route('consumer.addresses'));
        $response->assertOk();

        // 2. Guardar nueva dirección
        $storeResponse = $this->actingAs($this->consumer)->post(route('consumer.addresses.store'), [
            'label' => 'Casa Principal',
            'recipient' => 'Consumidor Fusa',
            'phone' => '3001234567',
            'street' => 'Carrera 6 # 7-15',
            'city' => 'Fusagasugá',
            'state' => 'CU',
            'postal_code' => '252211',
            'is_default' => 1,
        ]);
        $storeResponse->assertRedirect(route('consumer.addresses'));

        $this->assertDatabaseHas('shipping_addresses', [
            'user_id' => $this->consumer->id,
            'label' => 'Casa Principal',
            'city' => 'Fusagasugá',
            'is_default' => true,
        ]);

        $address = ShippingAddress::where('user_id', $this->consumer->id)->first();

        // 3. Actualizar dirección
        $updateResponse = $this->actingAs($this->consumer)->put(route('consumer.addresses.update', $address->id), [
            'label' => 'Oficina Centro',
            'recipient' => 'Consumidor Fusa',
            'phone' => '3001234567',
            'street' => 'Calle 8 # 5-20',
            'city' => 'Fusagasugá',
            'state' => 'CU',
            'postal_code' => '252211',
            'is_default' => 1,
        ]);
        $updateResponse->assertRedirect(route('consumer.addresses'));

        $this->assertDatabaseHas('shipping_addresses', [
            'id' => $address->id,
            'label' => 'Oficina Centro',
        ]);

        // 4. Eliminar dirección
        $deleteResponse = $this->actingAs($this->consumer)->delete(route('consumer.addresses.destroy', $address->id));
        $deleteResponse->assertRedirect(route('consumer.addresses'));

        $this->assertDatabaseMissing('shipping_addresses', ['id' => $address->id]);
    }

    public function test_consumer_can_create_pqrs_and_analyst_can_reply(): void
    {
        // 1. Consumidor radica PQRS
        $createResponse = $this->actingAs($this->consumer)->get(route('pqrs.create'));
        $createResponse->assertOk();

        $storeResponse = $this->actingAs($this->consumer)->post(route('pqrs.store'), [
            'type' => 'peticion',
            'category' => 'envio',
            'priority' => 'media',
            'subject' => 'Consulta cobertura vereda Pekin',
            'content' => 'Buenas tardes, quisiera saber si las entregas llegan hasta la vereda Pekin en Fusagasugá.',
        ]);
        $storeResponse->assertRedirect(route('pqrs.index'));

        $pqrs = PQRS::where('user_id', $this->consumer->id)->first();
        $this->assertNotNull($pqrs);
        $this->assertSame('pending', $pqrs->status);

        // 2. Consumidor consulta su ticket
        $showResponse = $this->actingAs($this->consumer)->get(route('pqrs.show', $pqrs->id));
        $showResponse->assertOk();
        $showResponse->assertSee('Consulta cobertura vereda Pekin');

        // 3. Analista ve el ticket en la bandeja de administración
        $adminListResponse = $this->actingAs($this->analyst)->get(route('analyst.pqrs.index'));
        $adminListResponse->assertOk();
        $adminListResponse->assertSee('Consulta cobertura vereda Pekin');

        // 4. Analista responde el ticket
        $replyResponse = $this->actingAs($this->analyst)->post(route('analyst.pqrs.reply', $pqrs->id), [
            'admin_response' => 'Estimado usuario, confirmamos que contamos con cobertura para la vereda Pekin.',
            'status' => 'resolved',
        ]);
        $replyResponse->assertRedirect();

        $pqrs->refresh();
        $this->assertSame('resolved', $pqrs->status);
        $this->assertCount(2, $pqrs->conversation_history);
    }

    public function test_cookie_consent_endpoint_and_middleware(): void
    {
        // 1. Aceptar cookies
        $postResponse = $this->postJson(route('cookie.consent'), ['consent' => true]);
        $postResponse->assertOk()->assertJson(['ok' => true]);
        $postResponse->assertCookie('cookie_consent', '1');

        // 2. Navegación con cookie activa procesada por middleware
        $getResponse = $this->withCookie('cookie_consent', '1')->get(route('consumer.home'));
        $getResponse->assertOk();
    }

    public function test_safe_encrypted_cast_on_company_profile(): void
    {
        $profile = $this->merchant->companyProfile;
        $plainSecret = 'wompi_secret_integrity_xyz_789';

        $profile->wompi_integrity_secret = $plainSecret;
        $profile->save();

        // En la base de datos cruda el valor está cifrado y no coincide con texto plano
        $rawSecret = DB::table('company_profiles')->where('id', $profile->id)->value('wompi_integrity_secret');
        $this->assertNotSame($plainSecret, $rawSecret);

        // Al acceder por el modelo Eloquent se descifra de manera transparente
        $this->assertSame($plainSecret, $profile->fresh()->wompi_integrity_secret);
    }

    public function test_analyst_can_view_integrations_dashboard(): void
    {
        IntegrationLog::create([
            'service' => 'envia',
            'action' => 'quote_rates',
            'status' => 'failed',
            'error' => 'Timeout al conectar con API Envia Encomiendas',
            'retries' => 1,
        ]);

        $response = $this->actingAs($this->analyst)->get(route('analyst.integrations'));
        $response->assertOk();
        $response->assertSee('Timeout al conectar con API Envia Encomiendas');
    }

    public function test_consumer_and_merchant_reservation_lifecycle(): void
    {
        // Producto tipo servicio / glamping
        $product = Product::create([
            'merchant_id' => $this->merchant->id,
            'name' => 'Noche Romántica en Glamping Sumapaz',
            'slug' => 'noche-romantica-glamping-sumapaz',
            'category' => 'Hoteles y Alojamientos',
            'price' => 180000,
            'stock' => 5,
            'is_active' => true,
        ]);

        // 1. Consumidor solicita reserva
        $storeResponse = $this->actingAs($this->consumer)->post(route('consumer.reservations.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'party_size' => 2,
            'preferred_date' => now()->addDays(2)->format('Y-m-d'),
            'preferred_time' => '15:00',
            'customer_notes' => 'Celebración de aniversario.',
        ]);
        $storeResponse->assertRedirect(route('consumer.reservations'));

        $reservation = Reservation::where('user_id', $this->consumer->id)->first();
        $this->assertNotNull($reservation);
        $this->assertSame('pending', $reservation->status);

        // 2. Consumidor consulta sus reservas
        $consumerList = $this->actingAs($this->consumer)->get(route('consumer.reservations'));
        $consumerList->assertOk();
        $consumerList->assertSee('Noche Romántica en Glamping Sumapaz');

        // 3. Comerciante consulta reservas en su panel
        $merchantList = $this->actingAs($this->merchant)->get(route('merchant.reservations'));
        $merchantList->assertOk();
        $merchantList->assertSee('Celebración de aniversario');

        // 4. Comerciante confirma la reserva
        $updateResponse = $this->actingAs($this->merchant)->patch(route('merchant.reservations.update', $reservation->id), [
            'status' => 'confirmed',
            'merchant_notes' => 'Reserva confirmada. Los esperamos a partir de las 3:00 PM.',
        ]);
        $updateResponse->assertRedirect();
        $this->assertSame('confirmed', $reservation->fresh()->status);

        // 5. Consumidor cancela la reserva
        $cancelResponse = $this->actingAs($this->consumer)->patch(route('consumer.reservations.cancel', $reservation->id));
        $cancelResponse->assertRedirect();
        $this->assertSame('cancelled', $reservation->fresh()->status);
    }
}
