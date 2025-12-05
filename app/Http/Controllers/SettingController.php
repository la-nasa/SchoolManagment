<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use App\Models\SchoolYear;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorize('manage-settings');

        // Récupérer les paramètres existants ou créer une nouvelle instance
        $settings = SchoolSetting::getSettings();
        $schoolYears = SchoolYear::orderBy('start_date', 'desc')->get();
        $terms = Term::with('schoolYear')->orderBy('order')->get();
        $currentSchoolYear = SchoolYear::current();
        $currentTerm = Term::current();

        return view('settings.index', compact('settings', 'schoolYears', 'terms', 'currentSchoolYear', 'currentTerm'));
    }

    public function update(Request $request)
    {
        $this->authorize('manage-settings');

        $validated = $request->validate([
            'school_name' => 'required|string|max:255',
            'school_acronym' => 'nullable|string|max:20',
            'school_address' => 'nullable|string|max:500',
            'school_phone' => 'nullable|string|max:20',
            'school_email' => 'nullable|email|max:255',
            'school_website' => 'nullable|url|max:255',
            'principal_name' => 'nullable|string|max:255',
            'principal_title' => 'nullable|string|max:255',
            'current_school_year' => 'nullable|exists:school_years,id',
            'current_term' => 'nullable|exists:terms,id',
            'trimester1_start' => 'nullable|date',
            'trimester2_start' => 'nullable|date',
            'trimester3_start' => 'nullable|date',
            'sequences_per_trimester' => 'nullable|in:2,3,4',
            'grading_system' => 'nullable|in:20,100',
            'passing_mark' => 'nullable|numeric|min:0',
            'excellent_mark' => 'nullable|numeric|min:0',
            'calculation_method' => 'nullable|in:average,weighted',
            'rounding_method' => 'nullable|in:none,half,whole',
        ]);

        // Récupérer ou créer les paramètres
        $settings = SchoolSetting::getSettings();

        // Mettre à jour les paramètres généraux
        $settings->update([
            'school_name' => $validated['school_name'],
            'school_acronym' => $validated['school_acronym'] ?? $settings->school_acronym,
            'school_address' => $validated['school_address'] ?? $settings->school_address,
            'school_phone' => $validated['school_phone'] ?? $settings->school_phone,
            'school_email' => $validated['school_email'] ?? $settings->school_email,
            'school_website' => $validated['school_website'] ?? $settings->school_website,
            'principal_name' => $validated['principal_name'] ?? $settings->principal_name,
            'principal_title' => $validated['principal_title'] ?? $settings->principal_title,
        ]);

        // Mettre à jour les paramètres académiques dans des colonnes supplémentaires
        // Vous devrez peut-être ajouter ces colonnes à votre table school_settings
        $academicSettings = [
            'trimester1_start' => $validated['trimester1_start'] ?? null,
            'trimester2_start' => $validated['trimester2_start'] ?? null,
            'trimester3_start' => $validated['trimester3_start'] ?? null,
            'sequences_per_trimester' => $validated['sequences_per_trimester'] ?? null,
            'grading_system' => $validated['grading_system'] ?? null,
            'passing_mark' => $validated['passing_mark'] ?? null,
            'excellent_mark' => $validated['excellent_mark'] ?? null,
            'calculation_method' => $validated['calculation_method'] ?? null,
            'rounding_method' => $validated['rounding_method'] ?? null,
        ];

        // Mettre à jour les paramètres académiques
        foreach ($academicSettings as $key => $value) {
            if ($value !== null) {
                $settings->$key = $value;
            }
        }

        $settings->save();

        // Mettre à jour l'année scolaire courante si spécifiée
        if (isset($validated['current_school_year'])) {
            $schoolYear = SchoolYear::find($validated['current_school_year']);
            if ($schoolYear) {
                SchoolYear::where('is_current', true)->update(['is_current' => false]);
                $schoolYear->update(['is_current' => true]);
            }
        }

        // Mettre à jour le trimestre courant si spécifié
        if (isset($validated['current_term'])) {
            $term = Term::find($validated['current_term']);
            if ($term) {
                Term::where('is_current', true)->update(['is_current' => false]);
                $term->update(['is_current' => true]);
            }
        }

        return redirect()->route('admin.settings')
            ->with('success', 'Paramètres mis à jour avec succès.');
    }

    public function updateSecurity(Request $request)
    {
        $this->authorize('manage-settings');

        $validated = $request->validate([
            'session_timeout' => 'required|integer|min:15|max:1440',
            'max_login_attempts' => 'required|integer|min:1|max:10',
            'password_uppercase' => 'boolean',
            'password_lowercase' => 'boolean',
            'password_numbers' => 'boolean',
            'password_symbols' => 'boolean',
            'password_min_length' => 'required|integer|min:6|max:20',
            'password_expiry_days' => 'required|integer|min:0|max:365',
        ]);

        $settings = SchoolSetting::getSettings();

        // Mettre à jour les paramètres de sécurité
        // Vous devrez ajouter ces colonnes à votre table school_settings
        $securitySettings = [
            'session_timeout' => $validated['session_timeout'],
            'max_login_attempts' => $validated['max_login_attempts'],
            'password_uppercase' => $validated['password_uppercase'] ?? false,
            'password_lowercase' => $validated['password_lowercase'] ?? false,
            'password_numbers' => $validated['password_numbers'] ?? false,
            'password_symbols' => $validated['password_symbols'] ?? false,
            'password_min_length' => $validated['password_min_length'],
            'password_expiry_days' => $validated['password_expiry_days'],
        ];

        foreach ($securitySettings as $key => $value) {
            $settings->$key = $value;
        }

        $settings->save();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Paramètres de sécurité mis à jour avec succès.');
    }

    public function updateAppearance(Request $request)
    {
        $this->authorize('manage-settings');

        $validated = $request->validate([
            'primary_color' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
            'theme' => 'required|in:light,dark',
            'logo' => 'nullable|image|max:2048',
        ]);

        $settings = SchoolSetting::getSettings();

        // Gérer le logo
        if ($request->hasFile('logo')) {
            // Supprimer l'ancien logo s'il existe
            if ($settings->school_logo) {
                Storage::disk('public')->delete($settings->school_logo);
            }

            // Stocker le nouveau logo
            $logoPath = $request->file('logo')->store('settings', 'public');
            $settings->school_logo = $logoPath;
        }

        // Mettre à jour les paramètres d'apparence
        // Vous devrez ajouter ces colonnes à votre table school_settings
        $appearanceSettings = [
            'primary_color' => $validated['primary_color'],
            'secondary_color' => $validated['secondary_color'],
            'theme' => $validated['theme'],
        ];

        foreach ($appearanceSettings as $key => $value) {
            $settings->$key = $value;
        }

        $settings->save();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Paramètres d\'apparence mis à jour avec succès.');
    }
}
