<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bulletin de Notes - {{ $student->getFullName() ?? 'Élève' }}</title>
    <style>
        @page {
            margin: 15px;
            size: A4 portrait;
        }
        body {
            font-family: 'Arial', sans-serif;
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
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <div class="school-name">
            RÉPUBLIQUE DU CAMEROUN<br>
            Paix – Travail – Patrie<br>
            <strong>{{ $settings->school_name ?? 'LYCÉE' }}</strong>
        </div>
        <div class="ministry">
            MINISTÈRE DES ENSEIGNEMENTS SECONDAIRES<br>
            BP: {{ $settings->school_address ?? '' }} - Tél: {{ $settings->school_phone ?? '' }}
        </div>
        <div class="academic-year">
            BULLETIN DE NOTES DU {{ strtoupper($term->name) }} TRIMESTRE<br>
            ANNÉE SCOLAIRE {{ $schoolYear->year }}
        </div>
    </div>

    <!-- Informations de l'élève -->
    <div class="student-info">
        <table class="info-table">
            <tr>
                <td width="25%"><strong>Nom(s) et Prénom(s) :</strong><br>{{ $student->getFullName() ?? 'Non renseigné' }}</td>
                <td width="25%">
                    <strong>Date de Naissance :</strong><br>
                    @if($student->date_of_birth)
                        {{ $student->date_of_birth->format('d/m/Y') }} à {{ $student->place_of_birth ?? 'Lieu non renseigné' }}
                    @else
                        <span class="na">Non renseignée</span>
                    @endif
                </td>
                <td width="15%"><strong>Sexe :</strong><br>{{ $student->gender ?? 'Non renseigné' }}</td>
                <td width="35%"><strong>Matricule :</strong><br>{{ $student->matricule ?? 'Non attribué' }}</td>
            </tr>
            <tr>
                <td>
                    <strong>Classe :</strong><br>
                    @if($student->classe)
                        {{ $student->classe->full_name ?? $student->classe->name ?? 'Classe non assignée' }}
                    @else
                        <span class="na">Classe non assignée</span>
                    @endif
                </td>
                <td><strong>Effectif :</strong><br>{{ $classStats['total_students'] ?? 'N/A' }}</td>
                <td><strong>N° :</strong><br>{{ $results['rank'] ?? 'N/A' }}</td>
                <td>
                    <strong>Professeur principal :</strong><br>
                    @if($student->classe && $student->classe->teacher)
                        {{ $student->classe->teacher->getFullName() ?? 'Non assigné' }}
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
                <th width="20%">Matière</th>
                @foreach($examTypes as $examType)
                <th width="8%">{{ $examType->abbreviation ?? 'N/A' }}/{{ $examType->max_mark ?? 20 }}</th>
                @endforeach
                <th width="8%">Moyenne</th>
                <th width="6%">Coef</th>
                <th width="8%">Total</th>
                <th width="6%">Rang</th>
                <th width="10%">Appréciation</th>
                <th width="16%">Signature</th>
            </tr>
        </thead>
        <tbody>
            @forelse($results['subjects'] ?? [] as $subject)
            <tr>
                <td class="text-left">{{ $subject['name'] ?? 'Matière inconnue' }}</td>
                @foreach($examTypes as $examType)
                <td>{{ $subject['marks'][$examType->id]['mark'] ?? '-' }}</td>
                @endforeach
                <td class="bold">{{ isset($subject['average']) ? number_format($subject['average'], 2) : '-' }}</td>
                <td>{{ $subject['coefficient'] ?? '-' }}</td>
                <td>{{ isset($subject['total']) ? number_format($subject['total'], 2) : '-' }}</td>
                <td>{{ $subject['rank'] ?? 'N/A' }}°</td>
                <td>{{ $subject['appreciation'] ?? 'Non évalué' }}</td>
                <td></td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ count($examTypes) + 7 }}" class="text-center na">
                    Aucune note disponible pour ce trimestre
                </td>
            </tr>
            @endforelse

            <!-- Totaux par groupe -->
            @if(isset($results['literary_total_coef']))
            <tr class="subject-group">
                <td class="text-left">DISCIPLINES LITTÉRAIRES</td>
                <td colspan="{{ count($examTypes) + 1 }}"></td>
                <td class="bold">{{ $results['literary_total_coef'] ?? '' }}</td>
                <td class="bold">{{ number_format($results['literary_total_score'] ?? 0, 2) }}</td>
                <td colspan="3">Moyenne : {{ number_format($results['literary_average'] ?? 0, 2) }}</td>
            </tr>
            @endif

            @if(isset($results['scientific_total_coef']))
            <tr class="subject-group">
                <td class="text-left">DISCIPLINES SCIENTIFIQUES</td>
                <td colspan="{{ count($examTypes) + 1 }}"></td>
                <td class="bold">{{ $results['scientific_total_coef'] ?? '' }}</td>
                <td class="bold">{{ number_format($results['scientific_total_score'] ?? 0, 2) }}</td>
                <td colspan="3">Moyenne : {{ number_format($results['scientific_average'] ?? 0, 2) }}</td>
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
                    {{ isset($results['general_average']) ? number_format($results['general_average'], 2) : 'N/A' }}
                </td>
                <td width="25%">
                    <strong>RANG TRIMESTRELLE :</strong><br>
                    {{ $results['rank'] ?? 'N/A' }}
                </td>
                <td width="25%">
                    <strong>MOYENNE GÉNÉRALE DE LA CLASSE :</strong><br>
                    {{ number_format($classStats['class_average'] ?? 0, 2) }}
                </td>
                <td width="25%">
                    <strong>MOYENNE DU PREMIER :</strong><br>
                    {{ number_format($classStats['top_average'] ?? 0, 2) }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>MOYENNE DU DERNIER :</strong><br>
                    {{ number_format($classStats['bottom_average'] ?? 0, 2) }}
                </td>
                <td>
                    <strong>APPRÉCIATION TRAVAIL :</strong><br>
                    {{ $bulletin->appreciation ?? ($results['appreciation'] ?? 'Non évalué') }}
                </td>
                <td colspan="2">
                    <strong>OBSERVATIONS :</strong><br>
                    {{ $bulletin->head_teacher_comment ?? 'Aucune observation' }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <table width="100%">
            <tr>
                <td width="33%" class="text-center">
                    Le Professeur Principal<br><br>
                    _________________________<br>
                    @if($student->classe && $student->classe->teacher)
                        {{ $student->classe->teacher->getFullName() ?? 'Non assigné' }}
                    @else
                        <span class="na">Non assigné</span>
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
        </div>
    </div>
</body>
</html>
