<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SiteController extends Controller
{
    /**
     * Récupérer tous les sites touristiques.
     */
    public function index()
    {
        //
        $sites = Site::all();
        return response()->json($sites);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Créer un site touristique.
     */
    public function store(Request $request)
    {
        //
        DB::beginTransaction(); // Démarrer une transaction
        try {
            $validated = $request->validate([
                'site_picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
                'site_name' => 'required|string|max:255',
                'site_description' => 'required|string',
                'site_localisation' => 'required|string|max:255',
                'site_amount' => 'required|numeric|min:0|max:1000000',
                'site_number_available' => 'required|integer|min:0'
            ]);
            // Vérifier si un fichier est bien reçu
            if (!$request->hasFile('site_picture')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun fichier reçu',
                ], 400);
            }
            // Récupérer le fichier et le stocker
            $file = $request->file('site_picture');
            $filename = now()->format('YmdHis') . '_' . Str::slug($validated['site_name']) . '.' . $file->extension();
            $path = $file->storeAs('sites/images', $filename, 'public');
            // Création du site
            $site = Site::create([
                'site_picture' => $path,
                'site_name' => $validated['site_name'],
                'site_description' => $validated['site_description'],
                'site_localisation' => $validated['site_localisation'],
                'site_amount' => $validated['site_amount'],
                'site_number_available' => $validated['site_number_available']
            ]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Site touristique créé avec succès',
                'data' => $site
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'error' => $e->errors(),
            ],422);
            //throw $th;
        } catch (\Exception $e) {
            DB::rollBack(); // Annuler la transaction en cas d'erreur
            Log::error('Erreur création Site touristique : ' . $e->getMessage(), [
                'input' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création',
                'error' => config('app.debug') ? $e->getMessage() : null
            ],500);
        }
    }

    /**
     * Afficher un site touristique spécifique.
     */
    public function show($id)
    {
        //
        try {
            $site = Site::findOrFail($id);
            return response()->json([
                'success' => true,
                'message' => 'Site trouvé',
                'data' => $site
            ],200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'site non trouvé'
            ],404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Site $site)
    {
        //
    }

    /**
     * Mise à jour d'un site touristique.
     */
    public function update(Request $request, $id)
    {
        //
        DB::beginTransaction();
        try {

            $site = Site::findOrFail($id);
            $validated = $request->validate([
                'site_picture' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
                'site_name' => 'sometimes|string|max:255',
                'site_description' => 'sometimes|string',
                'site_localisation' => 'sometimes|string|max:255',
                'site_amount' => 'sometimes|numeric|min:0|max:1000000',
                'site_number_available' => 'sometimes|integer|min:0'
            ]);
            if ($request->hasFile('site_picture')) {
                // Supprimer l'ancienne image si elle existe
                if ($site->site_picture && Storage::disk('public')->exists($site->site_picture)) {
                    Storage::disk('public')->delete($site->site_picture);
                }
                $file = $request->file('site_picture');
                $filename = now()->format('YmdHis') . '_' . Str::slug($validated['site_name'] ?? $site->site_name) . '.' . $file->extension();
                $validated['site_picture'] = $file->storeAs('sites/images', $filename, 'public');
            }
            $site->update($validated);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'site mis à jour avec succès',
                'data' => $site->fresh()
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Site non trouvé'
            ], 404);
            //throw $th;
        }  catch(\Illuminate\Validation\ValidationException $e){
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);

        }  catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur mise à jour du site #'.$id.' : ' . $e->getMessage(), [
                'input' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Supprimer un site.
     */
    public function destroy($id)
    {
        //
        try {
            
            DB::beginTransaction();
            $site = Site::findOrFail($id);
            if ($site->site_picture) {
                Storage::disk('public')->delete($site->site_picture);
            }
            $site->delete();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Site supprimé avec succès'
            ],200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de la suppression du site touristique :" . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Une erreur s'est produite lors de la suppression du site touristique",
                'error' => config('app.debug') ? $e->getMessage() : null
            ],500);
            //throw $th;
        }
    }
    /**
     * Recherché un site via son site_name.
     */
    public function searchSite($name){
        $sites = Site::where('site_name', 'LIKE', "%{$name}%")->get();
        if ($sites->isEmpty()) {
            return response()->json([
               'success' => false,
               'message' => 'Aucun site trouvé avec ce nom'
            ], 404);
        }
        return response()->json([
           'success' => true,
           'message' => 'Sites trouvés',
            'data' => $sites
        ], 200);
    }
}
