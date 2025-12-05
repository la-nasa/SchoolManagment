<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bulletin de Notes - {{ $student->full_name ?? 'Élève' }}</title>
    <style>
        @page {
            margin: 15px;
            size: A4 portrait;
        }
        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 10px;
            line-height: 1.2;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }
        .school-name {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .ministry {
            font-size: 9px;
            margin-bottom: 2px;
        }
        .academic-year {
            font-size: 10px;
            font-weight: bold;
        }
        .student-info {
            margin: 8px 0;
            padding: 5px;
            border: 1px solid #000;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 2px 5px;
            border: 1px solid #000;
        }
        .marks-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
            font-size: 8px;
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
        .subject-group {
            background-color: #e8e8e8;
            font-weight: bold;
        }
        .summary {
            margin-top: 10px;
            padding: 5px;
            border: 1px solid #000;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 3px;
            border: 1px solid #000;
        }
        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 8px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .bg-light { background-color: #f9f9f9; }
        .na { color: #666; font-style: italic; }
        .text-success { color: #28a745; }
        .text-danger { color: #dc3545; }
        /* Fix for PDF rendering */
        table { page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <div class="school-name">
            RÉPUBLIQUE DU CAMEROUN<br>
            Paix – Travail – Patrie<br>
            <strong>{{ $settings->school_name ?? 'ÉTABLISSEMENT SCOLAIRE' }}</strong>
        </div>
        <div class="ministry">
            MINISTÈRE DES ENSEIGNEMENTS SECONDAIRES<br>
            BP: {{ $settings->school_address ?? '' }} - Tél: {{ $settings->school_phone ?? '' }}
        </div>
        <div class="academic-year">
            BULLETIN DE NOTES DU {{ strtoupper($term->name ?? '') }} TRIMESTRE<br>
            ANNÉE SCOLAIRE {{ $schoolYear->year ?? '' }}
        </div>
    </div>

    <!-- Informations de l'élève -->
    <div class="student-info">
        <table class="info-table">
            <tr>
                <td width="25%">
                    <strong>Nom(s) et Prénom(s) :</strong><br>
                    {{ $student->full_name ?? ($student->first_name . ' ' . $student->last_name) ?? 'Non renseigné' }}
                </td>
                <td width="25%">
                    <strong>Date de Naissance :</strong><br>
                    @if($student->birth_date)
                        {{ $student->birth_date->format('d/m/Y') }} à {{ $student->birth_place ?? 'Lieu non renseigné' }}
                    @else
                        <span class="na">Non renseignée</span>
                    @endif
                </td>
                <td width="15%">
                    <strong>Sexe :</strong><br>
                    @if($student->gender == 'M')
                        Masculin
                    @elseif($student->gender == 'F')
                        Féminin
                    @else
                        Non renseigné
                    @endif
                </td>
                <td width="35%">
                    <strong>Matricule :</strong><br>
                    {{ $student->matricule ?? 'Non attribué' }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Classe :</strong><br>
                    @if($student->class)
                        {{ $student->class->full_name ?? $student->class->name ?? 'Classe non assignée' }}
                    @else
                        <span class="na">Classe non assignée</span>
                    @endif
                </td>
                <td><strong>Effectif :</strong><br>{{ $classStats['total_students'] ?? 'N/A' }}</td>
                <td><strong>N° :</strong><br>{{ $results['rank'] ?? 'N/A' }}</td>
                <td>
                    <strong>Professeur principal :</strong><br>
                    @if($student->class && $student->class->teacher)
                        {{ $student->class->teacher->full_name ?? ($student->class->teacher->first_name . ' ' . $student->class->teacher->last_name) ?? 'Non assigné' }}
                    @else
                        <span class="na">Non assigné</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- Tableau des notes -->
    <table class="marks-table">
        <thead>
            <tr>
                <th width="25%">Matière</th>
                @if(isset($examTypes) && count($examTypes) > 0)
                    @foreach($examTypes as $examType)
                    <th width="{{ 60 / count($examTypes) }}%">
                        {{ $examType->abbreviation ?? $examType->name ?? 'N/A' }}
                    </th>
                    @endforeach
                @else
                    <th width="15%">Notes</th>
                @endif
                <th width="8%">Moyenne</th>
                <th width="6%">Coef</th>
                <th width="8%">Total</th>
                <th width="6%">Rang</th>
                <th width="12%">Appréciation</th>
                <th width="15%">Signature</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalWeighted = 0;
                $totalCoefficients = 0;
                $hasData = false;
            @endphp
            
            @forelse($results['subject_results'] ?? [] as $subjectResult)
                @php
                    $hasData = true;
                    $subject = $subjectResult['subject'] ?? null;
                    $average = $subjectResult['average'] ?? 0;
                    $coefficient = $subjectResult['coefficient'] ?? 1;
                    $total = $average * $coefficient;
                    $totalWeighted += $total;
                    $totalCoefficients += $coefficient;
                @endphp
                <tr>
                    <td class="text-left">{{ $subject->name ?? 'Matière inconnue' }}</td>
                    
                    @if(isset($examTypes) && count($examTypes) > 0)
                        @foreach($examTypes as $examType)
                        <td>-</td>
                        @endforeach
                    @else
                        <td>-</td>
                    @endif
                    
                    <td class="bold {{ $average >= 10 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($average, 2) }}
                    </td>
                    <td>{{ $coefficient }}</td>
                    <td>{{ number_format($total, 2) }}</td>
                    <td>{{ $subjectResult['rank'] ?? '-' }}</td>
                    <td>{{ $subjectResult['appreciation'] ?? 'Non évalué' }}</td>
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ (isset($examTypes) ? count($examTypes) : 1) + 7 }}" class="text-center na">
                        Aucune note disponible pour ce trimestre
                    </td>
                </tr>
            @endforelse
            
            <!-- Totaux -->
            @if($hasData)
            <tr class="subject-group">
                <td class="text-left"><strong>TOTAUX</strong></td>
                @if(isset($examTypes) && count($examTypes) > 0)
                    @foreach($examTypes as $examType)
                    <td></td>
                    @endforeach
                @else
                    <td></td>
                @endif
                <td></td>
                <td class="bold">{{ $totalCoefficients }}</td>
                <td class="bold">{{ number_format($totalWeighted, 2) }}</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- Résumé et appréciations -->
    <div class="summary">
        <table class="summary-table">
            <tr>
                <td width="25%">
                    <strong>MOYENNE TRIMESTRELLE :</strong><br>
                    <span class="bold {{ ($results['general_average'] ?? 0) >= 10 ? 'text-success' : 'text-danger' }}">
                        {{ isset($results['general_average']) ? number_format($results['general_average'], 2) : 'N/A' }}/20
                    </span>
                </td>
                <td width="25%">
                    <strong>RANG :</strong><br>
                    {{ $results['rank'] ?? 'N/A' }} sur {{ $results['total_students'] ?? ($classStats['total_students'] ?? 'N/A') }}
                </td>
                <td width="25%">
                    <strong>MOYENNE DE LA CLASSE :</strong><br>
                    {{ number_format($classStats['class_average'] ?? 0, 2) }}/20
                </td>
                <td width="25%">
                    <strong>TAUX DE RÉUSSITE :</strong><br>
                    {{ number_format($classStats['success_rate'] ?? 0, 1) }}%
                </td>
            </tr>
            <tr>
                <td>
                    <strong>MEILLEURE MOYENNE :</strong><br>
                    {{ number_format($classStats['max_average'] ?? 0, 2) }}/20
                </td>
                <td>
                    <strong>MOYENNE MINIMALE :</strong><br>
                    {{ number_format($classStats['min_average'] ?? 0, 2) }}/20
                </td>
                <td colspan="2">
                    <strong>APPRÉCIATION :</strong><br>
                    {{ $results['appreciation'] ?? ($bulletin->appreciation ?? 'Non évalué') }}
                </td>
            </tr>
            @if(isset($bulletin) && $bulletin->head_teacher_comment)
            <tr>
                <td colspan="4">
                    <strong>OBSERVATIONS :</strong><br>
                    {{ $bulletin->head_teacher_comment }}
                </td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <table width="100%">
            <tr>
                <td width="33%" class="text-center">
                    Le Professeur Principal<br><br>
                    _________________________<br>
                    @if($student->class && $student->class->teacher)
                        {{ $student->class->teacher->full_name ?? ($student->class->teacher->first_name . ' ' . $student->class->teacher->last_name) ?? '' }}
                    @endif
                </td>
                <td width="34%" class="text-center">
                    Le Chef d'Établissement<br><br>
                    _________________________<br>
                    {{ $settings->principal_name ?? 'Le Principal' }}
                </td>
                <td width="33%" class="text-center">
                    Visa des Parents/Tuteurs<br><br>
                    _________________________
                </td>
            </tr>
        </table>
        <div style="margin-top: 10px;">
            {{ $settings->school_name ?? 'Établissement' }} - {{ $settings->school_city ?? '' }} - Tél: {{ $settings->school_phone ?? '' }}
            <br>Bulletin généré le {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>
</body>
</html>