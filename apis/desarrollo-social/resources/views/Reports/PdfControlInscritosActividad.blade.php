@php
    $escuelas = in_array($header->programa->dependencia_id,['8','5']);
@endphp

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Listado de inscritos</title>
</head>
<style>
    @page {
        margin: 190px 30px 45px 30px
    }

    body {
        font-family: Arial, sans-serif;
        font-size: 11px;
    }

    header {
        position: fixed;
        top: -160px;
        width: 100%;
        text-align: center;
    }

    footer {
        position: fixed;
        bottom: -45px;
    }

    .page-break {
        page-break-after: always;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        border: 1px solid #000;
        padding: 4px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

    .title {
        font-weight: bold;
        background-color: #f2f2f2;
    }
</style>

<body>
    <header>
       
            <table>
                <tr>
                    <td colspan="4" style="text-align: center; vertical-align: middle;">
                        <h1 style="margin: 0; padding : 0;">LISTADO DE INSCRITOS</h1>
                        @if ($escuelas)
                            <h4 style="margin: 0; padding : 0;">{{ $header->programa->escuela->nombre }}</h4>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="title">PROGRAMA:</td>
                    <td>{{ $header->programa->id .' - '.$header->programa->nombre }}</td>
                    <td class="title">ACTIVIDAD : </td>
                    <td>{{ $header->id .' - '. $header->actividad->nombre }}</td>
                </tr>
                <tr>
                    <td class="title">RESPONSABLE:</td>
                    <td>{{ $header->responsable }}</td>
                    <td class="title">AÑO INSCRIPCIÓN</td>
                    <td><strong>{{ $beneficiarios_inscritos[0]->anio_inscripcion }}</strong></td>
                </tr>
                <tr>
                    <td class="title">TIPO:</td>
                    <td>{{ $header->tipo->nombre ?? '' }}</td>
                    <td class="title">ESTADO:</td>
                    <td>{{ $header->estado->nombre }}</td>
                </tr>
                <tr>
                    <td class="title">HORA:</td>
                    <td>{{ $header->horario }}</td>
                    <td class="title">FECHA:</td>
                    <td>{{ $header->fechas }}</td>
                </tr>
                <tr>
                    <td class="title">DIRECCION:</td>
                    <td colspan="3">{{ $header->direccion_completa }}</td>
                </tr>
            </table>
    </header>
    <footer></footer>
    <main>
        <table>
            <thead>
                <tr>
                    <th><strong>#</strong></th>
                    <th>
                        <strong>ID INSCRIPCIÓN</strong>
                    </th>
                    <th>
                        <strong>CUI</strong>
                    </th>
                    <th>
                        <strong>BENEFICIARIO</strong>
                    </th>
                    @if ($escuelas)
                        <th>
                            <strong>INTERLOCUTOR</strong>
                        </th>
                    @endif
                    <th>
                        <strong>ESTADO INSCRIPCIÓN</strong>
                    </th>
                    <th>
                        <strong>FECHA DE INSCRIPCIÓN</strong>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($beneficiarios_inscritos as $inscripcion )
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $inscripcion->id }}</td>
                        <td>{{ $inscripcion->beneficiario->cui }}</td>
                        <td>{{ $inscripcion->beneficiario->nombre_completo }}</td>
                        @if ($escuelas)
                            <td>{{ $inscripcion->beneficiario->interlocutor ?? '' }}</td>
                        @endif
                        <td>{{ $inscripcion->estado }}</td>
                        <td>{{ date('d/m/Y', strtotime($inscripcion->created_at)) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ intval(count($columns)) }}" align="center">No hay data .....</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </main>
    <footer>
    </footer>
    
    <script type="text/php">
        if ( isset($pdf) ) {
            $pdf->page_script('
                $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
                $pdf->text(370, 560, "Página $PAGE_NUM / $PAGE_COUNT", $font, 10);
            ');
        }
	</script>
</body>
</html>
