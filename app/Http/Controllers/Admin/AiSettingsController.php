<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class AiSettingsController extends Controller
{
    public function index()
    {
        $providers = config('ai.providers');
        $currentProvider = Setting::get('ai.provider') ?: config('ai.default_provider', 'gemini');
        $currentModels = Setting::get('ai.model') ?: [];

        return view('admin.ai', [
            'providers' => $providers,
            'currentProvider' => $currentProvider,
            'currentModels' => $currentModels,
        ]);
    }

    public function update(Request $request)
    {
        $provider = $request->input('provider');
        $models = $request->input('models', []);

        $available = array_keys(config('ai.providers'));
        if (!in_array($provider, $available, true)) {
            return redirect()->back()->withErrors(['provider' => 'Invalid provider']);
        }

        Setting::set('ai.provider', $provider);

        // Persist selected model per provider (optional)
        $sanitized = [];
        foreach ($models as $p => $m) {
            $allowed = config("ai.providers.$p.available_models", []);
            if (in_array($m, $allowed, true)) {
                $sanitized[$p] = $m;
            }
        }
        Setting::set('ai.model', $sanitized);

        return redirect()->back()->with('status', 'AI settings updated');
    }
}

