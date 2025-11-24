@props([
    'filters' => [],
    'action' => '',
    'method' => 'GET'
])

<div class="bg-white shadow rounded-lg p-6">
    <form method="{{ $method }}" action="{{ $action }}" class="grid grid-cols-1 gap-4 sm:grid-cols-{{ count($filters) + 1 }}">
        @foreach($filters as $filter)
        <div>
            <label for="{{ $filter['name'] }}" class="block text-sm font-medium text-gray-700">
                {{ $filter['label'] }}
            </label>
            @if($filter['type'] === 'select')
            <select name="{{ $filter['name'] }}" id="{{ $filter['name'] }}" class="mt-1 form-select">
                @foreach($filter['options'] as $value => $label)
                <option value="{{ $value }}" {{ request($filter['name']) == $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
                @endforeach
            </select>
            @elseif($filter['type'] === 'text')
            <input type="text" name="{{ $filter['name'] }}" id="{{ $filter['name'] }}"
                   value="{{ request($filter['name']) }}" class="mt-1 form-input"
                   placeholder="{{ $filter['placeholder'] ?? '' }}">
            @elseif($filter['type'] === 'date')
            <input type="date" name="{{ $filter['name'] }}" id="{{ $filter['name'] }}"
                   value="{{ request($filter['name']) }}" class="mt-1 form-input">
            @endif
        </div>
        @endforeach
        <div class="flex items-end">
            <button type="submit" class="btn-primary w-full">
                Filtrer
            </button>
        </div>
    </form>
</div>
