<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $events = Event::all();
        return response()->json($events);
    }

    /**
     * Créer un évènement culturel.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (Auth::user()->role_id !== 'admin' && Auth::user()->role_id !== 'organizer') {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à créer un événement.'
            ], 403);
        }

        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'picture_event' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
                'label_event' => 'required|string|max:255|unique:events,label_event',
                'description_event' => 'required|string',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'localisation' => 'required|string|max:255',
                'amount_event' => 'required|numeric|min:0|max:1000000',
                'number_available_event' => 'required|integer|min:0'
            ]);

            $file = $request->file('picture_event');
            $filename = now()->format('YmdHis') . '_' . Str::slug($validated['label_event']) . '.' . $file->extension();
            $path = $file->storeAs('events/images', $filename, 'public');

            $event = Event::create([
                'picture_event' => $path,
                'label_event' => $validated['label_event'],
                'description_event' => $validated['description_event'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'localisation' => $validated['localisation'],
                'amount_event' => $validated['amount_event'],
                'number_available_event' => $validated['number_available_event']
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Événement culturel créé avec succès',
                'data' => $event
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création événement : ' . $e->getMessage());
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
     * Mise à jour d'un évènement culturel.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if (Auth::user()->role_id !== 'admin' && Auth::user()->role_id !== 'organizer') {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à modifier cet événement.'
            ], 403);
        }

        DB::beginTransaction();

        try {
            $event = Event::findOrFail($id);

            $validated = $request->validate([
                'picture_event' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
                'label_event' => 'sometimes|string|max:255|unique:events,label_event,'.$event->id,
                'description_event' => 'sometimes|string',
                'start_date' => 'sometimes|date|after_or_equal:today',
                'end_date' => 'sometimes|date|after_or_equal:start_date',
                'localisation' => 'sometimes|string|max:255',
                'amount_event' => 'sometimes|numeric|min:0|max:1000000',
                'number_available_event' => 'sometimes|integer|min:0'
            ]);

            if ($request->hasFile('picture_event')) {
                if ($event->picture_event && Storage::disk('public')->exists($event->picture_event)) {
                    Storage::disk('public')->delete($event->picture_event);
                }

                $file = $request->file('picture_event');
                $filename = now()->format('YmdHis') . '_' . Str::slug($validated['label_event'] ?? $event->label_event) . '.' . $file->extension();
                $validated['picture_event'] = $file->storeAs('events/images', $filename, 'public');
            }

            $event->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Événement culturel mis à jour avec succès',
                'data' => $event->fresh()
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Événement non trouvé'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur mise à jour événement #'.$id.' : ' . $e->getMessage());
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
        $user = Auth::user();

        if (Auth::user()->role_id !== 'admin' && Auth::user()->role_id !== 'organizer'){
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à supprimer cet événement.'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $event = Event::findOrFail($id);

            if ($event->picture_event) {
                Storage::disk('public')->delete($event->picture_event);
            }

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
     * Rechercher un évènement grâce à son label_event.
     */
    public function searchEvent($label)
    {
        $events = Event::where('label_event', 'LIKE', "%{$label}%")->get();

        if ($events->isEmpty()) {
            return response()->json(['message' => 'Aucun événement trouvé.'], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Événements trouvés',
            'data' => $events
        ], 200);
    }
}
