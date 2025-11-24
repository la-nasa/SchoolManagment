@extends('layouts.app')

@section('title', 'Modifier la Note')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Modifier la Note</h3>
            <p class="mt-1 text-sm text-gray-500">
                {{ $mark->evaluation->subject->name }} - {{ $mark->evaluation->class->name }}
            </p>
        </div>

        <form action="{{ route('marks.update', $mark) }}" method="POST" class="px-4 py-5 sm:p-6">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Student Info -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Informations de l'élève</h4>
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-12 w-12 bg-blue-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-medium">
                                {{ substr($mark->student->first_name, 0, 1) }}{{ substr($mark->student->last_name, 0, 1) }}
                            </span>
                        </div>
                        <div class="ml-4">
                            <div class="text-lg font-medium text-gray-900">
                                {{ $mark->student->first_name }} {{ $mark->student->last_name }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $mark->student->matricule }} - {{ $mark->student->class->name }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Evaluation Info -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Informations de l'évaluation</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-500">Matière:</span>
                            <span class="ml-2 text-gray-900">{{ $mark->evaluation->subject->name }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500">Type:</span>
                            <span class="ml-2 text-gray-900">{{ $mark->evaluation->type }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500">Séquence:</span>
                            <span class="ml-2 text-gray-900">{{ $mark->evaluation->sequence_type }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500">Note max:</span>
                            <span class="ml-2 text-gray-900">{{ $mark->evaluation->max_mark }}</span>
                        </div>
                    </div>
                </div>

                <!-- Mark Details -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="mark" class="block text-sm font-medium text-gray-700">Note *</label>
                        <input type="number" name="mark" id="mark" value="{{ old('mark', number_format($mark->mark, 2)) }}" required
                               step="0.25" min="0" max="{{ $mark->evaluation->max_mark }}"
                               class="mt-1 form-input @error('mark') border-red-500 @enderror">
                        @error('mark')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">
                            Note sur {{ $mark->evaluation->max_mark }}
                            ({{ number_format(($mark->mark / $mark->evaluation->max_mark) * 20, 1) }}/20)
                        </p>
                    </div>

                    <div>
                        <label for="appreciation" class="block text-sm font-medium text-gray-700">Appréciation</label>
                        <select name="appreciation" id="appreciation" class="mt-1 form-select @error('appreciation') border-red-500 @enderror">
                            <option value="">Sélectionner</option>
                            <option value="Excellent" {{ old('appreciation', $mark->appreciation) == 'Excellent' ? 'selected' : '' }}>Excellent</option>
                            <option value="Très bien" {{ old('appreciation', $mark->appreciation) == 'Très bien' ? 'selected' : '' }}>Très bien</option>
                            <option value="Bien" {{ old('appreciation', $mark->appreciation) == 'Bien' ? 'selected' : '' }}>Bien</option>
                            <option value="Assez bien" {{ old('appreciation', $mark->appreciation) == 'Assez bien' ? 'selected' : '' }}>Assez bien</option>
                            <option value="Passable" {{ old('appreciation', $mark->appreciation) == 'Passable' ? 'selected' : '' }}>Passable</option>
                            <option value="Insuffisant" {{ old('appreciation', $mark->appreciation) == 'Insuffisant' ? 'selected' : '' }}>Insuffisant</option>
                        </select>
                        @error('appreciation')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="remarks" class="block text-sm font-medium text-gray-700">Remarques</label>
                    <textarea name="remarks" id="remarks" rows="3" class="mt-1 form-textarea @error('remarks') border-red-500 @enderror"
                              placeholder="Remarques additionnelles...">{{ old('remarks', $mark->remarks) }}</textarea>
                    @error('remarks')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('marks.index') }}" class="btn-secondary">
                    Annuler
                </a>
                <button type="submit" class="btn-primary">
                    Mettre à jour la note
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const markInput = document.getElementById('mark');
    const appreciationSelect = document.getElementById('appreciation');

    function updateAppreciation() {
        const mark = parseFloat(markInput.value);
        const maxMark = {{ $mark->evaluation->max_mark }};
        const percentage = (mark / maxMark) * 20;

        if (isNaN(mark)) {
            appreciationSelect.value = '';
            return;
        }

        if (percentage >= 18) {
            appreciationSelect.value = 'Excellent';
        } else if (percentage >= 16) {
            appreciationSelect.value = 'Très bien';
        } else if (percentage >= 14) {
            appreciationSelect.value = 'Bien';
        } else if (percentage >= 12) {
            appreciationSelect.value = 'Assez bien';
        } else if (percentage >= 10) {
            appreciationSelect.value = 'Passable';
        } else {
            appreciationSelect.value = 'Insuffisant';
        }
    }

    markInput.addEventListener('change', updateAppreciation);
    markInput.addEventListener('input', updateAppreciation);
});
</script>
@endpush
