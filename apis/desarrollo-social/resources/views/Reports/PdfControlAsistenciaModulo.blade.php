<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Listado asistencia</title>
</head>
<style>
    @page {margin: 170px 30px 45px 30px}

    body{
        font-family: Arial, sans-serif;
        font-size: 11px;
    }
    
    header{
        position: fixed;
        top:-140px;
        width: 100%;
        text-align: center;
    }

    footer{
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
    th, td {
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
                    <h1 style="margin: 0; padding : 0;">CONTROL DE ASISTENCIA</h1>
                    <h4 style="margin: 0; padding : 0;">{{ $modulo->programa->dependencia->nombre }}</h4>
                </td>
            </tr>
            <tr>
                <td class="title">PROGRAMA:</td>
                <td>{{ $modulo->programa->nombre }}</td>
                <td class="title">MODULO : </td>
                <td>{{ $modulo->nombre }}</td>
            </tr>
            <tr>
                <td class="title">ID MODULO</td>
                <td><strong>{{ $modulo->id }}</strong></td>
                <td class="title">SEDE:</td>
                <td>{{ $curso->sede->nombre_completo }}</td>
            </tr>
            <tr>
                <td class="title">SECCION</td>
                <td>{{ $curso->seccion ?? '' }}</td>
                <td class="title">MODALIDAD</td>
                <td>{{ $curso->modalidad ?? '' }}</td>
            </tr>
        </table>
    </header>
    <footer></footer>
    <main>
        <table>
            <thead>
                <tr>
                    <th>NO</th>
                    <th>ID</th>
                    <th>NOMBRE COMPLETO</th>
                    <th width="100px" align="center" style="text-align: center;">
                        ASISTENCIA DEL {{ date('d-M-Y',strtotime($fecha)) }}
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($beneficiarios_inscritos as $beneficiario)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $beneficiario->beneficiario->id }}</td>
                        <td>{{ mb_strtoupper($beneficiario->beneficiario->nombre_completo) }}</td>
                        <td></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </main>
    <footer>
    </footer>
    
    <script type="text/php">
        if ( isset($pdf) ) {
            $pdf->page_script('
                $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
                $pdf->text(270, 810, "Página $PAGE_NUM / $PAGE_COUNT", $font, 10);
            ');
        }
	</script>
</body>
</html>