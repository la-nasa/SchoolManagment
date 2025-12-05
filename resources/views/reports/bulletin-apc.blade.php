<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bulletin APC - {{ $student->full_name }}</title>
    <style>
        @page {
            margin: 10px;
            size: A4 portrait;
        }
        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 9px;
            line-height: 1.1;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 8px;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
        }
        .ministry {
            font-size: 8px;
            margin-bottom: 1px;
        }
        .school-name {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .student-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
            font-size: 8px;
        }
        .student-table td {
            border: 1px solid #000;
            padding: 2px 3px;
            vertical-align: top;
        }
        .competences-table {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0;
            font-size: 7px;
        }
        .competences-table th,
        .competences-table td {
            border: 1px solid #000;
            padding: 2px;
            text-align: center;
            vertical-align: middle;
        }
        .competences-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .competence-cell {
            text-align: left;
            font-size: 6px;
            line-height: 1;
        }
        .summary {
            margin-top: 5px;
            padding: 3px;
            border: 1px solid #000;
            font-size: 8px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 2px;
            border: 1px solid #000;
        }
        .footer {
            margin-top: 8px;
            text-align: center;
            font-size: 7px;
            border-top: 1px solid #000;
            padding-top: 3px;
        }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .photo-cell {
            width: 60px;
            height: 70px;
            border: 1px dashed #000;
            text-align: center;
            vertical-align: middle;
        }
        .text-success { color: #28a745; }
        .text-danger { color: #dc3545; }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <div class="ministry">
            RÉPUBLIQUE DU CAMEROUN<br>
            Paix – Travail – Patrie<br>
            MINISTÈRE DES ENSEIGNEMENTS SECONDAIRES
        </div>
        <div class="school-name">
            {{ $settings->school_name ?? 'ÉTABLISSEMENT SCOLAIRE' }}<br>
            BULLETIN SCOLAIRE DU {{ strtoupper($term->name ?? '') }} TRIMESTRE<br>
            ANNÉE SCOLAIRE {{ $schoolYear->year ?? '' }}
        </div>
    </div>

    <!-- Informations de l'élève -->
    <table class="student-table">
        <tr>
            <td class="photo-cell" rowspan="4">
                Photo<br>de<br>l'élève
            </td>
            <td width="30%">
                <strong>Nom et Prénoms :</strong><br>
                {{ $student->full_name ?? ($student->first_name . ' ' . $student->last_name) }}
            </td>
            <td width="15%">
                <strong>Classe :</strong><br>
                {{ $student->class->full_name ?? $student->class->name ?? 'Non assignée' }}
            </td>
            <td width="15%">
                <strong>Effectif :</strong><br>
                {{ $classStats['total_students'] ?? 'N/A' }}
            </td>
        </tr>
        <tr>
            <td>
                <strong>Date et lieu de naissance :</strong><br>
                @if($student->birth_date)
                    {{ $student->birth_date->format('d/m/Y') }} à {{ $student->birth_place ?? 'Lieu non renseigné' }}
                @else
                    Non renseigné
                @endif
            </td>
            <td>
                <strong>Genre :</strong><br>
                @if($student->gender == 'M')
                    Masculin
                @elseif($student->gender == 'F')
                    Féminin
                @else
                    Non renseigné
                @endif
            </td>
            <td>
                <strong>Matricule :</strong><br>
                {{ $student->matricule ?? 'Non attribué' }}
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <strong>Professeur principal :</strong><br>
                @if($student->class && $student->class->teacher)
                    {{ $student->class->teacher->full_name ?? ($student->class->teacher->first_name . ' ' . $student->class->teacher->last_name) ?? 'Non assigné' }}
                @else
                    Non assigné
                @endif
            </td>
        </tr>
    </table>

    <!-- Tableau des compétences et notes -->
    <table class="competences-table">
        <thead>
            <tr>
                <th width="30%">MATIÈRES</th>
                <th width="25%">COMPÉTENCES ÉVALUÉES</th>
                <th width="8%">Moyenne</th>
                <th width="6%">Coef</th>
                <th width="8%">Total</th>
                <th width="8%">Rang</th>
                <th width="15%">Appréciation</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalWeighted = 0;
                $totalCoefficients = 0;
            @endphp
            
            @forelse($results['subject_results'] ?? [] as $subjectResult)
                @php
                    $subject = $subjectResult['subject'] ?? null;
                    $average = $subjectResult['average'] ?? 0;
                    $coefficient = $subjectResult['coefficient'] ?? 1;
                    $total = $average * $coefficient;
                    $totalWeighted += $total;
                    $totalCoefficients += $coefficient;
                @endphp
                <tr>
                    <td class="text-left bold">
                        {{ $subject->name ?? 'Matière inconnue' }}
                    </td>
                    <td class="competence-cell">
                        @if(isset($competences) && isset($subject) && isset($competences[$subject->name ?? '']))
                            @foreach($competences[$subject->name] as $competence)
                                • {{ $competence }}<br>
                            @endforeach
                        @else
                        Compétences non définies
                        @endif
                    </td>
                    <td class="{{ $average >= 10 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($average, 2) }}
                    </td>
                    <td>{{ $coefficient }}</td>
                    <td>{{ number_format($total, 2) }}</td>
                    <td>{{ $subjectResult['rank'] ?? '-' }}</td>
                    <td>
                        {{ $subjectResult['appreciation'] ?? 'Non évalué' }}<br>
                        _________________
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center na">
                        Aucune note disponible pour ce trimestre
                    </td>
                </tr>
            @endforelse
            
            <!-- Ligne du total -->
            @if(count($results['subject_results'] ?? []) > 0)
            <tr class="bold">
                <td colspan="2" class="text-right">TOTAUX</td>
                <td>
                    @if($totalCoefficients > 0)
                        {{ number_format($totalWeighted / $totalCoefficients, 2) }}
                    @else
                        0.00
                    @endif
                </td>
                <td>{{ $totalCoefficients }}</td>
                <td>{{ number_format($totalWeighted, 2) }}</td>
                <td></td>
                <td></td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- Section discipline et profil -->
    <div class="summary">
        <table class="summary-table">
            <tr>
                <td width="50%">
                    <strong>PROFIL DE L'ÉLÈVE</strong><br>
                    <table width="100%">
                        <tr>
                            <td>Moyenne Générale:</td>
                            <td class="bold">
                                <span class="{{ ($results['general_average'] ?? 0) >= 10 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($results['general_average'] ?? 0, 2) }}/20
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>Rang:</td>
                            <td class="bold">{{ $results['rank'] ?? 'N/A' }}/{{ $results['total_students'] ?? ($classStats['total_students'] ?? 'N/A') }}</td>
                        </tr>
                        <tr>
                            <td>Appréciation:</td>
                            <td>{{ $results['appreciation'] ?? 'Non évalué' }}</td>
                        </tr>
                    </table>
                </td>
                <td width="50%">
                    <strong>PROFIL DE LA CLASSE</strong><br>
                    <table width="100%">
                        <tr>
                            <td>Moyenne Générale:</td>
                            <td>{{ number_format($classStats['class_average'] ?? 0, 2) }}/20</td>
                        </tr>
                        <tr>
                            <td>[Min – Max]:</td>
                            <td>{{ number_format($classStats['min_average'] ?? 0, 2) }} - {{ number_format($classStats['max_average'] ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Taux de réussite:</td>
                            <td>{{ number_format($classStats['success_rate'] ?? 0, 1) }}%</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong>Observations :</strong><br>
                    {{ $bulletin->head_teacher_comment ?? 'Aucune observation.' }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Pied de page avec signatures -->
    <div class="footer">
        <table width="100%">
            <tr>
                <td width="33%" class="text-center">
                    Visa du parent / Tuteur<br><br>
                    _________________________
                </td>
                <td width="34%" class="text-center">
                    Le Professeur principal<br><br>
                    _________________________<br>
                    @if($student->class && $student->class->teacher)
                        {{ $student->class->teacher->full_name ?? ($student->class->teacher->first_name . ' ' . $student->class->teacher->last_name) ?? '' }}
                    @endif
                </td>
                <td width="33%" class="text-center">
                    Le Chef d'établissement<br><br>
                    _________________________<br>
                    {{ $settings->principal_name ?? 'Le Principal' }}
                </td>
            </tr>
        </table>
        <div style="margin-top: 5px; font-size: 6px;">
            Bulletin généré le {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>
</body>
</html>