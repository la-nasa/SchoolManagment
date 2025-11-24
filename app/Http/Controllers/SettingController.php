<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use App\Models\SchoolYear;
use App\Models\Term;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorize('manage-settings');

        $settings = SchoolSetting::all()->keyBy('key')->map(function ($item) {
            return $item->value;
        });
        // $settings = SchoolSetting::all()->keyBy('key');
        $schoolYears = SchoolYear::all();
        $terms = Term::all();
        $currentSchoolYear = SchoolYear::current();
        $currentTerm = Term::current();

        return view('settings.index', compact('settings', 'schoolYears', 'terms', 'currentSchoolYear', 'currentTerm'));
    }

    public function update(Request $request)
    {
        $this->authorize('manage-settings');

        $validated = $request->validate([
            'school_name' => 'required|string|max:255',
            'school_address' => 'required|string|max:500',
            'school_phone' => 'nullable|string|max:20',
            'school_email' => 'nullable|email|max:255',
            'school_logo' => 'nullable|image|max:2048',
            'current_school_year' => 'required|exists:school_years,id',
            'current_term' => 'required|exists:terms,id',
            'min_success_average' => 'required|numeric|min:0|max:20',
            'theme_primary_color' => 'nullable|string|max:7',
            'theme_secondary_color' => 'nullable|string|max:7',
        ]);

        foreach ($validated as $key => $value) {
            if (in_array($key, ['_token', '_method'])) {
                continue;
            }
            
            SchoolSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
        // Mettre à jour les paramètres généraux
        // $this->updateSetting('school_name', $validated['school_name']);
        // $this->updateSetting('school_address', $validated['school_address']);
        // $this->updateSetting('school_phone', $validated['school_phone']);
        // $this->updateSetting('school_email', $validated['school_email']);
        // $this->updateSetting('min_success_average', $validated['min_success_average']);
        // $this->updateSetting('theme_primary_color', $validated['theme_primary_color'] ?? '#1e40af');
        // $this->updateSetting('theme_secondary_color', $validated['theme_secondary_color'] ?? '#f59e0b');

        // // Gérer le logo
        // if ($request->hasFile('school_logo')) {
        //     $logoPath = $request->file('school_logo')->store('settings', 'public');
        //     $this->updateSetting('school_logo', $logoPath);
        // }

        // // Mettre à jour l'année scolaire courante
        // $schoolYear = SchoolYear::find($validated['current_school_year']);
        // $schoolYear->setAsCurrent();

        // // Mettre à jour le trimestre courant
        // $term = Term::find($validated['current_term']);
        // $term->setAsCurrent();

        return redirect()->route('settings.index')
            ->with('success', 'Paramètres mis à jour avec succès.');
    }

    private function updateSetting($key, $value)
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => 'string']
        );
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

        foreach ($validated as $key => $value) {
            SchoolSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->route('settings.index')
            ->with('success', 'Paramètres de sécurité mis à jour avec succès.');
    }



    public function updateAppearance(Request $request)
    {
        $this->authorize('manage-settings');

        $validated = $request->validate([
            'theme_mode' => 'required|in:light,dark,system',
            'sidebar_style' => 'required|in:expanded,compact',
            'language' => 'required|in:fr,en',
            'primary_color' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
            'theme' => 'required|in:light,dark',
            'logo' => 'nullable|image|max:2048',
        ]);

        // foreach ($validated as $key => $value) {
        //     $this->updateSetting($key, $value);
        // }
         foreach ($validated as $key => $value) {
            if ($key === 'logo' && $request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('settings', 'public');
                SchoolSetting::updateOrCreate(
                    ['key' => 'logo'],
                    ['value' => $logoPath]
                );
            } else {
                SchoolSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }
        }

        return redirect()->route('settings.index')
            ->with('success', 'Paramètres d\'apparence mis à jour avec succès.');
    }

    public function updateAcademic(Request $request)
    {
        $this->authorize('manage-settings');

        $validated = $request->validate([
            'min_success_average' => 'required|numeric|min:0|max:20',
            'max_absence_rate' => 'required|numeric|min:0|max:100',
            'evaluation_weights' => 'required|array',
            'evaluation_weights.*.exam_type_id' => 'required|exists:exam_types,id',
            'evaluation_weights.*.weight' => 'required|numeric|min:0',
        ]);

        $this->updateSetting('min_success_average', $validated['min_success_average']);
        $this->updateSetting('max_absence_rate', $validated['max_absence_rate']);
        $this->updateSetting('evaluation_weights', json_encode($validated['evaluation_weights']));

        return redirect()->route('settings.index')
            ->with('success', 'Paramètres académiques mis à jour avec succès.');
    }
}
