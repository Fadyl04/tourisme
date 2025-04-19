<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SiteController extends Controller
{
    public function index(Request $request)
    {
        $sites = Site::paginate(10); // ou Site::all() si tu ne veux pas de pagination
        return response()->json($sites);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->role_id !== 'admin' && $user->role_id !== 'organizer') {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'picture_site' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
                'name_site' => 'required|string|max:255|unique:sites,name_site',
                'description_site' => 'required|string',
                'localisation_site' => 'required|string|max:255',
                'route_visit' => 'required|string',
                'date_dapart_visit' => 'required|date|after_or_equal:today',
                'place_depart_visit' => 'required|string|max:255',
                'amount_visit_site' => 'required|numeric|min:0|max:1000000',
                'number_available_site' => 'required|integer|min:0',
            ]);

            $file = $request->file('picture_site');
            $filename = now()->format('YmdHis') . '_' . Str::slug($validated['name_site']) . '.' . $file->extension();
            $path = $file->storeAs('sites/images', $filename, 'public');

            $site = Site::create([
                'picture_site' => $path,
                'name_site' => $validated['name_site'],
                'description_site' => $validated['description_site'],
                'localisation_site' => $validated['localisation_site'],
                'route_visit' => $validated['route_visit'],
                'date_dapart_visit' => $validated['date_dapart_visit'],
                'place_depart_visit' => $validated['place_depart_visit'],
                'amount_visit_site' => $validated['amount_visit_site'],
                'number_available_site' => $validated['number_available_site']
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Site créé avec succès', 'data' => $site], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création site : ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur serveur', 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function show($id)
    {
        try {
            $site = Site::findOrFail($id);
            return response()->json(['success' => true, 'message' => 'Site trouvé', 'data' => $site], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Site non trouvé'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if ($user->role_id !== 'admin' && $user->role_id !== 'organizer') {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        DB::beginTransaction();

        try {
            $site = Site::findOrFail($id);

            $validated = $request->validate([
                'picture_site' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
                'name_site' => 'sometimes|string|max:255|unique:sites,name_site,' . $site->id,
                'description_site' => 'sometimes|string',
                'localisation_site' => 'sometimes|string|max:255',
                'route_visit' => 'sometimes|string',
                'date_dapart_visit' => 'sometimes|date|after_or_equal:today',
                'place_depart_visit' => 'sometimes|string|max:255',
                'amount_visit_site' => 'sometimes|numeric|min:0|max:1000000',
                'number_available_site' => 'sometimes|integer|min:0',
            ]);

            if ($request->hasFile('picture_site')) {
                if ($site->picture_site && Storage::disk('public')->exists($site->picture_site)) {
                    Storage::disk('public')->delete($site->picture_site);
                }

                $file = $request->file('picture_site');
                $filename = now()->format('YmdHis') . '_' . Str::slug($validated['name_site'] ?? $site->name_site) . '.' . $file->extension();
                $validated['picture_site'] = $file->storeAs('sites/images', $filename, 'public');
            }

            $site->update($validated);
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Site mis à jour', 'data' => $site->fresh()], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Site non trouvé'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur mise à jour site #$id : " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur serveur', 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();

        if ($user->role_id !== 'admin' && $user->role_id !== 'organizer') {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        try {
            DB::beginTransaction();

            $site = Site::findOrFail($id);

            if ($site->picture_site) {
                Storage::disk('public')->delete($site->picture_site);
            }

            $site->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Site supprimé avec succès'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur suppression site : " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur serveur', 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function searchSite($name)
    {
        $sites = Site::where('name_site', 'LIKE', "%{$name}%")->get();

        if ($sites->isEmpty()) {
            return response()->json(['message' => 'Aucun site trouvé.'], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sites trouvés',
            'data' => $sites
        ], 200);
    }
}
