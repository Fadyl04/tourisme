<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    /**
     * Récupérer tous les évènements.
     */
    public function index(Request $request)
    {
        //
        $events = Event::all();
        return response()->json($events);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Créer un évènement culturel.
     */
    public function store(Request $request)
    {
        DB::beginTransaction(); // Démarrer une transaction

        try {
            // Validation des données entrantes
            $validated = $request->validate([
                'picture_event' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
                'label_event' => 'required|string|max:255|unique:events,label_event',
                'description_event' => 'required|string',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'localisation' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0|max:1000000',
                'number_available_event' => 'required|integer|min:0'
            ]);

            // Vérifier si un fichier est bien reçu
            if (!$request->hasFile('picture_event')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun fichier reçu',
                ], 400);
            }

            // Récupérer le fichier et le stocker
            $file = $request->file('picture_event');
            $filename = now()->format('YmdHis') . '_' . Str::slug($validated['label_event']) . '.' . $file->extension();
            $path = $file->storeAs('events/images', $filename, 'public');

            // Création de l'événement
            $event = Event::create([
                'picture_event' => $path,
                'label_event' => $validated['label_event'],
                'description_event' => $validated['description_event'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'localisation' => $validated['localisation'],
                'amount' => $validated['amount'],
                'number_available_event' => $validated['number_available_event']
            ]);

            DB::commit(); // Valider la transaction

            return response()->json([
                'success' => true,
                'message' => 'Événement culturel créé avec succès',
                'data' => $event
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack(); // Annuler la transaction en cas d'erreur de validation
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack(); // Annuler la transaction en cas d'erreur
            Log::error('Erreur création événement : ' . $e->getMessage(), [
                'input' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }


    /**
     * Afficher un évènement culturel spécifique.
     */
    public function show($id)
    {
        //
        try {
            $event = Event::findOrFail($id);
    
            return response()->json([
                'success' => true,
                'message' => 'Événement culturel trouvé',
                'data' => $event
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Événement culturel non trouvé'
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        //
    }

    /**
     * Mise à jour d'un évènement culturel.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction(); // Démarrer une transaction

        try {
            // Trouver l'événement ou échouer
            $event = Event::findOrFail($id);

            // Validation des données entrantes (champs optionnels)
            $validated = $request->validate([
                'picture_event' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
                'label_event' => 'sometimes|string|max:255|unique:events,label_event,'.$event->id,
                'description_event' => 'sometimes|string',
                'start_date' => 'sometimes|date|after_or_equal:today',
                'end_date' => 'sometimes|date|after_or_equal:start_date',
                'localisation' => 'sometimes|string|max:255',
                'amount' => 'sometimes|numeric|min:0|max:1000000',
                'number_available_event' => 'sometimes|integer|min:0'
            ]);

            // Gestion du fichier image (si nouveau fichier fourni)
            if ($request->hasFile('picture_event')) {
                // Supprimer l'ancienne image si elle existe
                if ($event->picture_event && Storage::disk('public')->exists($event->picture_event)) {
                    Storage::disk('public')->delete($event->picture_event);
                }

                // Stocker la nouvelle image
                $file = $request->file('picture_event');
                $filename = now()->format('YmdHis') . '_' . Str::slug($validated['label_event'] ?? $event->label_event) . '.' . $file->extension();
                $validated['picture_event'] = $file->storeAs('events/images', $filename, 'public');
            }

            // Mise à jour de l'événement
            $event->update($validated);

            DB::commit(); // Valider la transaction

            return response()->json([
                'success' => true,
                'message' => 'Événement culturel mis à jour avec succès',
                'data' => $event->fresh() // Recharger les données fraîches
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Événement non trouvé'
            ], 404);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack(); // Annuler la transaction
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            DB::rollBack(); // Annuler la transaction
            Log::error('Erreur mise à jour événement #'.$id.' : ' . $e->getMessage(), [
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
     * Supprimer un évènement.
     */
    public function destroy($id)
    {
        //
        try {
            DB::beginTransaction();
    
            // Trouver l'événement ou retourner une erreur 404
            $event = Event::findOrFail($id);
    
            // Supprimer l'image associée si elle existe
            if ($event->picture_event) {
                Storage::disk('public')->delete($event->picture_event);
            }
    
            // Supprimer l'événement
            $event->delete();
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Événement culturel supprimé avec succès'
            ], 200);
    
        } catch (\Exception $e) {
            DB::rollBack();
    
            Log::error("Erreur lors de la suppression de l'événement : " . $e->getMessage());
    
            return response()->json([
                'success' => false,
                'message' => "Une erreur s'est produite lors de la suppression de l'événement",
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Rechercher un évènement grace à son label_event.
     */
    public function searchEvent($label){
        $events = Event::where('label_event', 'LIKE', "%{$label}%")->get();
    
        if ($events->isEmpty()) {
            return response()->json(['message' => 'Aucun événement trouvé.'], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Evènement trouvés',
            'data' => $events
        ],200);
    }
}  
