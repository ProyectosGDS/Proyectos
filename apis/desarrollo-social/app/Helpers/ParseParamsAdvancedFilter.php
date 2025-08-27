<?php
namespace App\Helpers;

use Illuminate\Http\Request;

class ParseParamsAdvancedFilter
{
    public static function parser(Request $request): array
    {
        $searching = $request->input('searching', []);
        $sorting = $request->input('sort', []);
        $filters = $searching['filters'] ?? [];

        return [
            'search' => [
                'q' => $searching['search'] ?? '',
                'fields' => $searching['fields'] ?? [],
            ],
            'filters' => is_array($filters) ? $filters : [],
            'sort' => $sorting
        ];
    }
}