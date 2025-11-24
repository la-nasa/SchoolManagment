<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bulletin APC - {{ $student->getFullName() }}</title>
    <style>
        @page {
            margin: 10px;
            size: A4 portrait;
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
            {{ $settings->school_name ?? 'LYCÉE DE ...' }}<br>
            BULLETIN SCOLAIRE DU {{ strtoupper($term->name) }} TRIMESTRE<br>
            ANNÉE SCOLAIRE {{ $schoolYear->year }}
        </div>
    </div>

    <!-- Informations de l'élève -->
    <table class="student-table">
        <tr>
            <td class="photo-cell" rowspan="4">
                Photo<br>de<br>l'élève
            </td>
            <td width="30%"><strong>Nom et Prénoms de l'élève :</strong><br>{{ $student->getFullName() }}</td>
            <td width="15%"><strong>Classe :</strong><br>{{ $student->classe->full_name }}</td>
            <td width="15%"><strong>Effectif :</strong><br>{{ $classStats['total_students'] ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Date et lieu de naissance :</strong><br>{{ $student->date_of_birth->format('d/m/Y') }} à {{ $student->place_of_birth }}</td>
            <td><strong>Genre :</strong><br>{{ $student->gender }}</td>
            <td><strong>Redoublant :</strong><br>{{ $student->is_repeating ? 'Oui' : 'Non' }}</td>
        </tr>
        <tr>
            <td><strong>Identifiant Unique :</strong><br>{{ $student->matricule }}</td>
            <td colspan="2"><strong>Professeur principal :</strong><br>{{ $student->classe->teacher ? $student->classe->teacher->getFullName() : 'Non assigné' }}</td>
        </tr>
        <tr>
            <td colspan="3"><strong>Noms et contacts des Parents / Tuteurs :</strong><br>{{ $student->parent_contact ?? 'Non renseigné' }}</td>
        </tr>
    </table>

    <!-- Tableau des compétences et notes -->
    <table class="competences-table">
        <thead>
            <tr>
                <th width="25%">MATIÈRES ET NOM DE L'ENSEIGNANT</th>
                <th width="35%">COMPÉTENCES ÉVALUÉES</th>
                <th width="4%">N/20</th>
                <th width="4%">M/20</th>
                <th width="4%">Coef</th>
                <th width="6%">M x coef</th>
                <th width="6%">COTE</th>
                <th width="8%">[Min – Max]</th>
                <th width="8%">Appréciations et Visa de l'enseignant</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results['subjects'] as $subject)
            <tr>
                <td class="text-left bold">
                    {{ $subject['name'] }}<br>
                    <em>M/Mme {{ $subject['teacher'] ?? 'Non assigné' }}</em>
                </td>
                <td class="competence-cell">
                    @if(isset($competences[$subject['name']]))
                        @foreach($competences[$subject['name']] as $competence)
                            • {{ $competence }}<br>
                        @endforeach
                    @else
                        Compétences non définies
                    @endif
                </td>
                <td>{{ $subject['marks']['sequence_1']['mark'] ?? '-' }}</td>
                <td>{{ $subject['marks']['sequence_2']['mark'] ?? '-' }}</td>
                <td>{{ $subject['coefficient'] }}</td>
                <td class="bold">{{ number_format($subject['total'], 2) }}</td>
                <td>{{ number_format($subject['average'], 2) }}</td>
                <td>{{ $subject['min_max'] ?? 'N/A' }}</td>
                <td>
                    {{ $subject['appreciation'] }}<br>
                    _________________
                </td>
            </tr>
            @endforeach
            
            <!-- Ligne du total -->
            <tr class="bold">
                <td colspan="4" class="text-right">TOTAL</td>
                <td>{{ $results['total_coefficient'] }}</td>
                <td>{{ number_format($results['total_score'], 2) }}</td>
                <td colspan="3">MOYENNE : {{ number_format($results['general_average'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Section discipline et profil -->
    <div class="summary">
        <table class="summary-table">
            <tr>
                <td width="50%">
                    <strong>DISCIPLINE</strong><br>
                    <table width="100%">
                        <tr>
                            <td>Abs. non. J. (h):</td>
                            <td>___________</td>
                            <td>Avertissement de conduite:</td>
                            <td>□</td>
                        </tr>
                        <tr>
                            <td>Abs just. (h):</td>
                            <td>___________</td>
                            <td>Blâme de conduite:</td>
                            <td>□</td>
                        </tr>
                        <tr>
                            <td>Retards (nombre de fois):</td>
                            <td>___________</td>
                            <td>Exclusions (jours):</td>
                            <td>___________</td>
                        </tr>
                        <tr>
                            <td>Consignes (heures):</td>
                            <td>___________</td>
                            <td>Exclusion définitive:</td>
                            <td>□</td>
                        </tr>
                    </table>
                </td>
                <td width="25%">
                    <strong>TRAVAIL DE L'ÉLÈVE</strong><br>
                    <table width="100%">
                        <tr><td>TOTAL GÉNÉRAL:</td><td class="bold">{{ number_format($results['total_score'], 2) }}</td></tr>
                        <tr><td>COEF:</td><td class="bold">{{ $results['total_coefficient'] }}</td></tr>
                        <tr><td>MOYENNE TRIM:</td><td class="bold">{{ number_format($results['general_average'], 2) }}</td></tr>
                        <tr><td>COTE:</td><td class="bold">{{ $results['rank'] }}</td></tr>
                    </table>
                </td>
                <td width="25%">
                    <strong>PROFIL DE LA CLASSE</strong><br>
                    <table width="100%">
                        <tr><td>Moyenne Générale:</td><td>{{ number_format($classStats['class_average'] ?? 0, 2) }}</td></tr>
                        <tr><td>[Min – Max]:</td><td>{{ number_format($classStats['min_average'] ?? 0, 2) }} - {{ number_format($classStats['max_average'] ?? 0, 2) }}</td></tr>
                        <tr><td>Nombre de moyennes:</td><td>{{ $classStats['total_students'] ?? 'N/A' }}</td></tr>
                        <tr><td>Taux de réussite:</td><td>{{ number_format($classStats['success_rate'] ?? 0, 1) }}%</td></tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <strong>Appréciation du travail de l'élève (points forts et points à améliorer) :</strong><br>
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
                    Nom et visa du professeur principal<br><br>
                    _________________________<br>
                    {{ $student->classe->teacher ? $student->classe->teacher->getFullName() : '' }}
                </td>
                <td width="33%" class="text-center">
                    Le Chef d'établissement<br><br>
                    _________________________<br>
                    {{ $settings->principal_name ?? 'Le Principal' }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>