@props([
    'headers' => [],
    'data' => [],
    'emptyMessage' => 'Aucune donnée disponible.',
    'actions' => null,
    'paginated' => false
])

<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    @foreach($headers as $header)
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ $header }}
                    </th>
                    @endforeach
                    @if($actions)
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                    @endif
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($data as $row)
                <tr class="table-row-hover">
                    @foreach($row as $cell)
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $cell }}
                    </td>
                    @endforeach
                    @if($actions)
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        {{ $actions($row) }}
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count($headers) + ($actions ? 1 : 0) }}" class="px-6 py-4 text-center text-sm text-gray-500">
                        {{ $emptyMessage }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($paginated && $data instanceof \Illuminate\Pagination\LengthAwarePaginator)
    <div class="px-4 py-4 sm:px-6 border-t border-gray-200">
        {{ $data->links() }}
    </div>
    @endif
</div>
