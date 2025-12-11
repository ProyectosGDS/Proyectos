@php
    function getNestedValue($array, $key) {
        $keys = explode('.', $key);
        foreach ($keys as $innerKey) {
            if (isset($array[$innerKey])) {
                $array = $array[$innerKey];
            } else {
                return null;
            }
        }
        return $array;
    }
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
    <header></header>
    <main>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    @foreach ($columns as $column)
                        @if (!in_array('hidden',$column))
                            <th>
                                <strong>
                                    {{ mb_strtoupper($column['title']) }}
                                </strong>
                            </th>
                        @endif
                    @endforeach 
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item )
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    @foreach ($columns as $column)
                        @if (!in_array('hidden',$column) || column['key'] != 'actions')
                            <td>{{ getNestedValue($item, $column['key']) }}</td>
                        @endif
                    @endforeach
                </tr>
                @empty
                <tr>
                    <td colspan="{{ intval(count($columns)) }}" align="center" >No hay data .....</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </main>
    <footer></footer>

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

