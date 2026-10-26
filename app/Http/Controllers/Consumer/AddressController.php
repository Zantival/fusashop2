<?php

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use App\Support\ColombiaLocations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public const STATES = [
        'AM' => 'Amazonas', 'AN' => 'Antioquia', 'AR' => 'Arauca', 'AT' => 'Atlántico', 'DC' => 'Bogotá D.C.',
        'BL' => 'Bolívar', 'BY' => 'Boyacá', 'CL' => 'Caldas', 'CQ' => 'Caquetá', 'CS' => 'Casanare',
        'CA' => 'Cauca', 'CE' => 'Cesar', 'CH' => 'Chocó', 'CO' => 'Córdoba', 'CU' => 'Cundinamarca',
        'GN' => 'Guainía', 'GV' => 'Guaviare', 'HU' => 'Huila', 'LG' => 'La Guajira', 'MA' => 'Magdalena',
        'ME' => 'Meta', 'NA' => 'Nariño', 'NS' => 'Norte de Santander', 'PU' => 'Putumayo', 'QD' => 'Quindío',
        'RI' => 'Risaralda', 'SA' => 'San Andrés', 'ST' => 'Santander', 'SU' => 'Sucre', 'TO' => 'Tolima',
        'VC' => 'Valle del Cauca', 'VD' => 'Vaupés', 'VI' => 'Vichada',
    ];

    public function index()
    {
        return view('consumer.addresses', [
            'addresses' => Auth::user()->shippingAddresses()->orderByDesc('is_default')->get(),
            'states' => self::STATES,
            'municipalities' => ColombiaLocations::all(),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $data = $this->validated($request);
        $data['is_default'] = $request->boolean('is_default') || !$user->shippingAddresses()->exists();

        if ($data['is_default']) {
            $user->shippingAddresses()->update(['is_default' => false]);
        }
        $user->shippingAddresses()->create($data);

        return redirect()->route('consumer.addresses')->with('success', 'Dirección guardada.');
    }

    public function update(Request $request, int $id)
    {
        $user = Auth::user();
        $address = $user->shippingAddresses()->findOrFail($id);
        $data = $this->validated($request);

        if ($request->boolean('is_default')) {
            $user->shippingAddresses()->update(['is_default' => false]);
            $data['is_default'] = true;
        }
        $address->update($data);

        return redirect()->route('consumer.addresses')->with('success', 'Dirección actualizada.');
    }

    public function destroy(int $id)
    {
        $user = Auth::user();
        $address = $user->shippingAddresses()->findOrFail($id);
        $wasDefault = $address->is_default;
        $address->delete(); // los pedidos conservan un snapshot (shipping_snapshot)

        if ($wasDefault) {
            $user->shippingAddresses()->first()?->update(['is_default' => true]);
        }

        return redirect()->route('consumer.addresses')->with('success', 'Dirección eliminada.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'label' => 'required|string|max:60',
            'recipient' => 'required|string|max:120',
            'phone' => ['required', 'regex:/^[0-9+\s\-]{7,20}$/'],
            'street' => ['required', 'string', 'max:160', 'regex:/^(calle|cl\.?|carrera|cra\.?|kr\.?|avenida|av\.?|diagonal|diag\.?|transversal|transv\.?|tv\.?|circular|vereda|vía|via|kilómetro|kilometro)(?:\s|$)/iu'],
            'number' => 'nullable|string|max:30',
            'district' => 'nullable|string|max:100',
            'city' => 'required|string|max:100',
            'state' => 'required|in:' . implode(',', array_keys(self::STATES)),
            'postal_code' => ['nullable', 'regex:/^[0-9]{6}$/'],
            'reference' => 'nullable|string|max:255',
        ]);

        $location = ColombiaLocations::find($data['city'], $data['state']);
        if (!$location || $location['state_code'] !== $data['state']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'city' => 'Selecciona un municipio válido que pertenezca al departamento elegido.',
            ]);
        }
        $data['city'] = $location['name'];

        $dummyPostals = ['000000', '123456', '123465', '111111', '999999', '123123'];
        if (empty($data['postal_code']) || in_array($data['postal_code'], $dummyPostals, true)) {
            $loc = ColombiaLocations::find($data['city'], $data['state']);
            if ($loc && !empty($loc['postal_code'])) {
                $data['postal_code'] = $loc['postal_code'];
            }
        }

        if (empty($data['postal_code']) && !empty($location['postal_code'])) {
            $data['postal_code'] = $location['postal_code'];
        }

        if (empty($data['postal_code'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'postal_code' => 'No encontramos el código postal del municipio. Escríbelo para continuar.',
            ]);
        }

        return $data;
    }
}
