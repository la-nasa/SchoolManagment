<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Procès-Verbal - {{ $classe->name }} - {{ $term->name }}</title>
    <style>
        @page {
            margin: 10px;
            size: A4 landscape;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 9px;
            line-height: 1.1;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 8px;
            border-bottom: 2px solid #000;
            padding-bottom: 3px;
        }
        .ministry {
            font-size: 8px;
            margin-bottom: 1px;
        }
        .school-name {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .title {
            font-size: 10px;
            font-weight: bold;
            margin: 5px 0;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
            font-size: 8px;
        }
        .info-table td {
            padding: 2px 3px;
            border: 1px solid #000;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
            font-size: 7px;
        }
        .main-table th,
        .main-table td {
            border: 1px solid #000;
            padding: 2px;
            text-align: center;
            vertical-align: middle;
        }
        .main-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .student-name {
            text-align: left;
            min-width: 100px;
            font-size: 7px;
        }
        .subject-header {
            background-color: #e8e8e8;
            font-weight: bold;
        }
        .rotate {
            transform: rotate(-90deg);
            transform-origin: center;
            white-space: nowrap;
            width: 20px;
            height: 80px;
            font-size: 6px;
        }
        .absent {
            color: #ff0000;
            font-weight: bold;
        }
        .summary {
            margin-top: 8px;
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
            margin-top: 10px;
            text-align: center;
            font-size: 7px;
            border-top: 1px solid #000;
            padding-top: 3px;
        }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .total-row {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .page-break {
            page-break-before: always;
        }
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
            {{ $settings->school_name ?? 'LYCÉE' }}
        </div>
        <div class="title">
            PROCÈS-VERBAL DES NOTES - {{ strtoupper($term->name) }} TRIMESTRE<br>
            ANNÉE SCOLAIRE {{ $schoolYear->year }}<br>
            CLASSE : {{ $classe->name }} (Effectif : {{ count($students) }})
        </div>
    </div>

    <!-- Informations générales -->
    <table class="info-table">
        <tr>
            <td width="25%"><strong>Année Scolaire :</strong> {{ $schoolYear->year }}</td>
            <td width="25%"><strong>Trimestre :</strong> {{ $term->name }}</td>
            <td width="25%"><strong>Classe :</strong> {{ $classe->name }}</td>
            <td width="25%"><strong>Total Coefficients :</strong> {{ $totalCoefficients }}</td>
        </tr>
        <tr>
            <td><strong>Prof. Principal :</strong> {{ $classe->teacher->name ?? 'Non assigné' }}</td>
            <td><strong>Date :</strong> {{ now()->format('d/m/Y') }}</td>
            <td><strong>Élèves inscrits :</strong> {{ count($students) }}</td>
            <td><strong>Élèves classés :</strong> {{ count($students) }}</td>
        </tr>
    </table>

    <!-- Tableau principal -->
    <table class="main-table">
        <thead>
            <tr>
                <th rowspan="3" width="3%">N°</th>
                <th rowspan="3" class="student-name">NOMS ET PRÉNOMS</th>
                <th rowspan="3" width="6%">Matricule</th>
                <th rowspan="3" width="3%">Sexe</th>
                
                <!-- En-tête des matières -->
                @foreach($subjects as $subject)
                <th colspan="{{ count($subject['evaluations']) + 2 }}" class="subject-header">
                    {{ $subject['name'] }} (Coef: {{ $subject['coefficient'] }})
                </th>
                @endforeach
                
                <th rowspan="3" width="4%">Moy. Gén.</th>
                <th rowspan="3" width="3%">Rang</th>
                <th rowspan="3" width="8%">Appréciation</th>
            </tr>
            
            <!-- Sous-en-tête pour chaque matière (noms des évaluations) -->
            <tr>
                @foreach($subjects as $subject)
                    @foreach($subject['evaluations'] as $evaluation)
                    <th width="3%" class="rotate">{{ substr($evaluation['name'], 0, 10) }}</th>
                    @endforeach
                    <th width="3%">Moy.</th>
                    <th width="3%">Total</th>
                @endforeach
            </tr>
            
            <!-- Sous-en-tête pour les coefficients des évaluations -->
            <tr>
                @foreach($subjects as $subject)
                    @foreach($subject['evaluations'] as $evaluation)
                    <th width="3%">/{{ $evaluation['max_marks'] }}</th>
                    @endforeach
                    <th width="3%">/20</th>
                    <th width="3%"></th>
                @endforeach
            </tr>
        </thead>
        
        <tbody>
            @php
                $counter = 1;
            @endphp
            
            @foreach($students as $studentData)
            <tr>
                <td>{{ $counter++ }}</td>
                <td class="student-name">{{ $studentData['student']->full_name }}</td>
                <td>{{ $studentData['student']->matricule ?? 'N/A' }}</td>
                <td>{{ $studentData['student']->gender ?? 'N/A' }}</td>
                
                <!-- Notes par matière -->
                @foreach($subjects as $subject)
                    <!-- Notes des évaluations -->
                    @foreach($subject['evaluations'] as $evaluation)
                    <td>
                        @if(isset($studentData['marks'][$subject['id']][$evaluation['id']]))
                            @if($studentData['marks'][$subject['id']][$evaluation['id']]['is_absent'] ?? false)
                                <span class="absent">Abs</span>
                            @else
                                {{ number_format($studentData['marks'][$subject['id']][$evaluation['id']]['marks'] ?? 0, 1) }}
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    @endforeach
                    
                    <!-- Moyenne de la matière -->
                    <td class="bold">
                        @if(isset($studentData['averages'][$subject['id']]) && $studentData['averages'][$subject['id']] !== null)
                            {{ number_format($studentData['averages'][$subject['id']], 2) }}
                        @else
                            -
                        @endif
                    </td>
                    
                    <!-- Total pondéré -->
                    <td class="bold">
                        @if(isset($studentData['averages'][$subject['id']]) && $studentData['averages'][$subject['id']] !== null)
                            {{ number_format($studentData['averages'][$subject['id']] * $subject['coefficient'], 2) }}
                        @else
                            -
                        @endif
                    </td>
                @endforeach
                
                <!-- Moyenne générale et rang -->
                <td class="bold">{{ number_format($studentData['general_average'] ?? 0, 2) }}</td>
                <td class="bold">{{ $studentData['rank'] ?? 'N/A' }}</td>
                <td>{{ $studentData['appreciation'] ?? 'Non évalué' }}</td>
            </tr>
            @endforeach
            
            <!-- Ligne des moyennes de classe par matière -->
            <tr class="total-row">
                <td colspan="4" class="text-right bold">MOYENNE CLASSE</td>
                
                @foreach($subjects as $subject)
                    <td colspan="{{ count($subject['evaluations']) }}"></td>
                    <td class="bold">{{ number_format($subject['class_average'] ?? 0, 2) }}</td>
                    <td class="bold">{{ number_format(($subject['class_average'] ?? 0) * $subject['coefficient'], 2) }}</td>
                @endforeach
                
                <td class="bold">{{ number_format($classStatistics['class_average'] ?? 0, 2) }}</td>
                <td colspan="2"></td>
            </tr>
            
            <!-- Ligne des minima -->
            <tr>
                <td colspan="4" class="text-right">MINIMUM</td>
                
                @foreach($subjects as $subject)
                    <td colspan="{{ count($subject['evaluations']) }}"></td>
                    <td>{{ number_format($subject['min_average'] ?? 0, 2) }}</td>
                    <td>{{ number_format(($subject['min_average'] ?? 0) * $subject['coefficient'], 2) }}</td>
                @endforeach
                
                <td>{{ number_format($classStatistics['min_average'] ?? 0, 2) }}</td>
                <td colspan="2"></td>
            </tr>
            
            <!-- Ligne des maxima -->
            <tr>
                <td colspan="4" class="text-right">MAXIMUM</td>
                
                @foreach($subjects as $subject)
                    <td colspan="{{ count($subject['evaluations']) }}"></td>
                    <td>{{ number_format($subject['max_average'] ?? 0, 2) }}</td>
                    <td>{{ number_format(($subject['max_average'] ?? 0) * $subject['coefficient'], 2) }}</td>
                @endforeach
                
                <td>{{ number_format($classStatistics['max_average'] ?? 0, 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>

    <!-- Résumé statistique -->
    <div class="summary">
        <table class="summary-table">
            <tr>
                <td width="20%"><strong>Effectif Total :</strong> {{ count($students) }}</td>
                <td width="20%"><strong>Moyenne Classe :</strong> {{ number_format($classStatistics['class_average'] ?? 0, 2) }}/20</td>
                <td width="20%"><strong>Taux Réussite :</strong> {{ number_format($classStatistics['success_rate'] ?? 0, 1) }}%</td>
                <td width="20%"><strong>Meilleure Moyenne :</strong> {{ number_format($classStatistics['top_average'] ?? 0, 2) }}/20</td>
                <td width="20%"><strong>Plus Basse Moyenne :</strong> {{ number_format($classStatistics['bottom_average'] ?? 0, 2) }}/20</td>
            </tr>
            <tr>
                <td><strong>Total Coefficients :</strong> {{ $totalCoefficients }}</td>
                <td><strong>Médiane :</strong> {{ number_format($classStatistics['median'] ?? 0, 2) }}/20</td>
                <td><strong>Écart-type :</strong> {{ number_format($classStatistics['standard_deviation'] ?? 0, 2) }}</td>
                <td><strong>Absences totales :</strong> {{ $attendanceStatistics['absent'] ?? 0 }}</td>
                <td><strong>Taux d'absence :</strong> {{ number_format($attendanceStatistics['absence_rate'] ?? 0, 1) }}%</td>
            </tr>
        </table>
    </div>

    <!-- Observations -->
    @if(!empty($observations))
    <div class="summary" style="margin-top: 5px;">
        <strong>OBSERVATIONS :</strong><br>
        {{ $observations }}
    </div>
    @endif

    <!-- Pied de page avec signatures -->
    <div class="footer">
        <table width="100%">
            <tr>
                <td width="25%" class="text-center">
                    Le Professeur Principal<br><br>
                    _________________________<br>
                    {{ $classe->teacher->name ?? 'Non assigné' }}
                </td>
                <td width="25%" class="text-center">
                    Le Censeur<br><br>
                    _________________________
                </td>
                <td width="25%" class="text-center">
                    Le Chef d'Établissement<br><br>
                    _________________________<br>
                    {{ $settings->principal_name ?? 'Le Principal' }}
                </td>
                <td width="25%" class="text-center">
                    Date : {{ now()->format('d/m/Y') }}
                </td>
            </tr>
        </table>
        <div style="margin-top: 5px; font-size: 6px;">
            Document officiel - {{ $settings->school_name ?? 'Établissement' }} - 
            Généré le {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</body>
</html>