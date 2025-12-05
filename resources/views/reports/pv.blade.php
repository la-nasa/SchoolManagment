<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PV - {{ $evaluation->class->name ?? 'Classe' }} - {{ $evaluation->subject->name ?? 'Évaluation' }}</title>
    <style>
        @page {
            margin: 10px;
            size: A4 landscape;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 8px;
            line-height: 1.1;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 5px;
            border-bottom: 2px solid #000;
            padding-bottom: 3px;
        }
        .school-name {
            font-size: 10px;
            font-weight: bold;
        }
        .pv-title {
            font-size: 12px;
            font-weight: bold;
            margin: 5px 0;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
        }
        .info-table td {
            padding: 2px;
            border: 1px solid #000;
        }
        .marks-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
            font-size: 7px;
        }
        .marks-table th,
        .marks-table td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
        }
        .marks-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .student-row:hover {
            background-color: #f5f5f5;
        }
        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 7px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .absent { color: #ff0000; font-style: italic; }
        .na { color: #666; font-style: italic; }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">
            RÉPUBLIQUE DU CAMEROUN - Paix – Travail – Patrie<br>
            {{ $settings->school_name ?? 'ÉTABLISSEMENT SCOLAIRE' }}
        </div>
        <div class="pv-title">
            PROCÈS-VERBAL DE DEVOIR SURVEILLÉ<br>
            {{ strtoupper($evaluation->examType->name ?? 'ÉVALUATION') }} 
            - ANNÉE {{ $schoolYear->year ?? now()->year }}
        </div>
    </div>

    <table class="info-table">
        <tr>
            <td width="20%"><strong>Classe :</strong> {{ $evaluation->class->name ?? 'Non spécifiée' }}</td>
            <td width="20%"><strong>Effectif :</strong> {{ $evaluation->class->students()->count() ?? 0 }}</td>
            <td width="20%"><strong>Matière :</strong> {{ $evaluation->subject->name ?? 'Non spécifiée' }}</td>
            <td width="20%"><strong>Date :</strong> {{ $evaluation->exam_date->format('d/m/Y') ?? now()->format('d/m/Y') }}</td>
            <td width="20%"><strong>Surveillant :</strong> {{ $generatedBy->name ?? 'Non spécifié' }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Type d'évaluation :</strong> {{ $evaluation->examType->name ?? 'Non spécifié' }}</td>
            <td><strong>Note maximale :</strong> {{ $evaluation->max_marks ?? 20 }}</td>
            <td><strong>Durée :</strong> {{ $evaluation->duration ?? 0 }} minutes</td>
            <td><strong>Coefficient :</strong> {{ $evaluation->coefficient ?? 1 }}</td>
        </tr>
    </table>

    <table class="marks-table">
        <thead>
            <tr>
                <th width="3%">N°</th>
                <th width="10%">Matricule</th>
                <th width="20%">Nom et Prénom</th>
                <th width="8%">Note</th>
                <th width="8%">Appréciation</th>
                <th width="8%">Absent</th>
                <th width="43%">Observations</th>
            </tr>
        </thead>
        <tbody>
            @forelse($studentsWithMarks as $index => $studentData)
            @php
                $student = $studentData['student'] ?? null;
            @endphp
            <tr class="student-row">
                <td>{{ $index + 1 }}</td>
                <td>{{ $student->matricule ?? 'N/A' }}</td>
                <td class="text-left">
                    @if($student)
                        {{ $student->first_name ?? '' }} {{ $student->last_name ?? '' }}
                    @else
                        <span class="na">Étudiant non trouvé</span>
                    @endif
                </td>
                <td class="bold">
                    @if($studentData['is_absent'] ?? false)
                        <span class="absent">Absent</span>
                    @else
                        {{ $studentData['marks'] ?? 0 }}/{{ $evaluation->max_marks ?? 20 }}
                    @endif
                </td>
                <td>{{ $studentData['appreciation'] ?? '-' }}</td>
                <td>
                    @if($studentData['is_absent'] ?? false)
                    ✓
                    @else
                    -
                    @endif
                </td>
                <td class="text-left">
                    {{ $studentData['mark']->comment ?? '' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center na">
                    Aucun étudiant avec des notes pour cette évaluation
                </td>
            </tr>
            @endforelse
            
            <!-- Statistiques -->
            @if(count($studentsWithMarks) > 0)
            @php
                $validMarks = collect($studentsWithMarks)->filter(function($student) {
                    return !($student['is_absent'] ?? false);
                });
                $average = $validMarks->count() > 0 ? 
                    $validMarks->avg(function($student) { return $student['marks'] ?? 0; }) : 0;
                $maxMark = $validMarks->max('marks') ?? 0;
                $minMark = $validMarks->min('marks') ?? 0;
            @endphp
            <tr class="bold" style="background-color: #f0f0f0;">
                <td colspan="3" class="text-right">STATISTIQUES :</td>
                <td>
                    Moy: {{ number_format($average, 2) }}
                </td>
                <td>
                    Max: {{ $maxMark }}
                </td>
                <td>
                    Min: {{ $minMark }}
                </td>
                <td>
                    Présents: {{ $validMarks->count() }}/{{ count($studentsWithMarks) }}
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <table width="100%">
            <tr>
                <td width="25%" class="text-center">
                    Le Surveillant<br><br>
                    _________________________<br>
                    {{ $generatedBy->name ?? 'Non spécifié' }}
                </td>
                <td width="25%" class="text-center">
                    Le Professeur<br><br>
                    _________________________<br>
                    {{ $evaluation->subject->name ?? 'Matière' }}
                </td>
                <td width="25%" class="text-center">
                    Le Professeur Principal<br><br>
                    _________________________<br>
                    {{ $evaluation->class->teacher->name ?? 'Non assigné' }}
                </td>
                <td width="25%" class="text-center">
                    Le Chef d'Établissement<br><br>
                    _________________________<br>
                    {{ $settings->principal_name ?? 'Le Principal' }}
                </td>
            </tr>
        </table>
        <div style="margin-top: 5px;">
            Fait à {{ $settings->school_city ?? '' }}, le {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>
</body>
</html>