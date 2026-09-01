<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Ventas FusaShop</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; line-height: 1.6; font-size: 14px; margin: 0; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #006c47; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { margin: 0 0 5px 0; color: #006c47; font-size: 24px; }
        .header p { margin: 0; color: #777; font-size: 12px; }
        .section { margin-bottom: 40px; }
        .section-title { font-size: 18px; border-bottom: 1px solid #ddd; padding-bottom: 8px; margin-bottom: 15px; color: #222; }
        table { w-full; width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f9f9f9; font-weight: bold; color: #555; text-transform: uppercase; font-size: 12px; }
        tr:nth-child(even) { background-color: #fafafa; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row td { background-color: #006c47; color: white; font-weight: bold; }
        .page-break { page-break-before: always; }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
        .btn-print { display: inline-block; padding: 10px 20px; background-color: #006c47; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; border: none; cursor: pointer; }
    </style>
</head>
<body>

    <div class="no-print" style="text-align: center; margin-bottom: 20px; padding: 20px; background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;">
        <p style="margin-top:0; font-size: 16px;">Vista previa del Informe de Ventas</p>
        <button onclick="window.print()" class="btn-print">Imprimir / Guardar como PDF</button>
    </div>

    <div class="header">
        <h1>FusaShop - Informe de Ventas</h1>
        <p>Generado el: {{ date('d M Y, h:i A') }}</p>
        <p>Confidencial - Uso Interno Exclusivo de Analistas</p>
    </div>

    <div class="section">
        <h2 class="section-title">Resumen de Ventas Mensuales {{ date('Y') }}</h2>
        <table>
            <thead>
                <tr>
                    <th>Mes</th>
                    <th class="text-right">Total Generado (COP)</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $months = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']; 
                    $sumMonths = 0;
                @endphp
                @foreach($monthlySales as $ms)
                    @php $sumMonths += $ms->total; @endphp
                    <tr>
                        <td>{{ $months[$ms->month - 1] }}</td>
                        <td class="text-right">${{ number_format($ms->total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                @if($monthlySales->isEmpty())
                    <tr>
                        <td colspan="2" class="text-center">No hay datos de ventas registrados este año.</td>
                    </tr>
                @else
                    <tr class="total-row">
                        <td>TOTAL AÑO</td>
                        <td class="text-right">${{ number_format($sumMonths, 0, ',', '.') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2 class="section-title">Ingresos Brutos por Empresa Registrada</h2>
        <table>
            <thead>
                <tr>
                    <th>Nombre de la Empresa (Comerciante)</th>
                    <th class="text-right">Volumen de Ventas (COP)</th>
                </tr>
            </thead>
            <tbody>
                @php $sumCompanies = 0; @endphp
                @foreach($salesByCompany as $sc)
                    @php $sumCompanies += $sc->total_sales; @endphp
                    <tr>
                        <td>{{ $sc->company_name }}</td>
                        <td class="text-right">${{ number_format($sc->total_sales, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                @if($salesByCompany->isEmpty())
                    <tr>
                        <td colspan="2" class="text-center">No hay registros de ventas vinculados a empresas.</td>
                    </tr>
                @else
                    <tr class="total-row">
                        <td>TOTAL VOLUMEN EN PLATAFORMA</td>
                        <td class="text-right">${{ number_format($sumCompanies, 0, ',', '.') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div style="text-align: center; margin-top: 50px; font-size: 11px; color: #888;">
        &copy; {{ date('Y') }} FusaShop Inc. Todos los derechos reservados.
    </div>

    <script>
        // Automatic print prompt when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
