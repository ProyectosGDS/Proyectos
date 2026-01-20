
<table>
    <thead>
        <tr>
            @foreach ($columns as $key => $value)
                <th style="text-align: center; size : 14px"><strong>{{ mb_strtoupper($key) }}</strong></th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
        <tr>
            @foreach ($columns as $key => $value)
            <td>{{ mb_strtoupper($row->{$key}) }}</td>
            @endforeach
        </tr>
        @empty
        <tr>
            <td colspan="{{ count($columns) }}"> NO HAY DATOS .....</td>
        </tr>
        @endforelse
    </tbody>
</table>