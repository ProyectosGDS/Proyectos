
@if(!empty($rows))
<table>
    <thead>
        <tr>
            @foreach ($columns as $key => $value)
                <th style="text-align: center; size : 14px"><strong>{{ mb_strtoupper($key) }}</strong></th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $row)
        <tr>
            @foreach ($columns as $key => $value)
            <td>{{ mb_strtoupper($row->{$key}) }}</td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>
@else
<table>
    <thead>
        <tr>
            <th>No hay informacion</th>
        </tr>
    </thead>
</table>
@endif