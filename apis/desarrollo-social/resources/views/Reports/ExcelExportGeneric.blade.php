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

<table>
    <thead>
        <tr>
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
