<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Site;
use App\Models\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * Récupérer toutes les reservations.
     */
    public function index()
    {
        $reservations = Reservation::all();
        return response()->json($reservations);
    }

    /**
     * Créer une réservation.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'id_site' => 'nullable|exists:sites,id',
                'id_event' => 'nullable|exists:events,id',
                'reservation_date' => 'nullable|date|after_or_equal:today',
            ]);

            return DB::transaction(function () use ($request) {
                if ($request->id_site) {
                    $site = Site::findOrFail($request->id_site);
                    if ($site->site_number_available <= 0) {
                        return response()->json(['message' => 'Plus de places disponibles pour ce site'], 400);
                    }
                }

                if ($request->id_event) {
                    $event = Event::findOrFail($request->id_event);
                    if ($event->number_available_event <= 0) {
                        return response()->json(['message' => 'Plus de places disponibles pour cet événement'], 400);
                    }
                }

                $reservation = Reservation::create([
                    'id_user' => Auth::id(),
                    'id_site' => $request->id_site,
                    'id_event' => $request->id_event,
                    'status' => 'pending',
                    'reservation_date' => $request->reservation_date ?? now(),
                ]);

                return response()->json([
                    'message' => 'Réservation créée avec succès.',
                    'data' => $reservation
                ], 201);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur réservation : ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        //
        try {
            $request->validate([
                'id_site' => 'nullable|exists:sites,id',
                'id_event' => 'nullable|exists:events,id',
                'reservation_date' => 'nullable|date|after_or_equal:today',
            ]);
            return DB::transaction(function () use ($request){
                if ($request->id_site) {
                    $site = Site::findOrFail($request->id_site);
                    if ($site->site_number_available <= 0) {
                        return response()->json(['message' => 'Plus de places disponibles pour ce site'], 400);
                    }
                }

                if ($request->id_event) {
                    $event = Event::findOrFail($request->id_event);
                    if ($event->number_available_event <= 0) {
                        return response()->json(['message' => 'Plus de places disponibles pour cet événement'], 400);
                    }
                }
                $reservation = Reservation::update([
                    'id_user' => Auth::id(),
                    'id_site' => $request->id_site,
                    'id_event' => $request->id_event,
                    'status' => 'pending',
                    'reservation_date' => $request->reservation_date ?? now(),
                ]);
                return response()->json([
                    'message' => 'Mise à jour de la réservation fait avec succès.',
                    'data' => $reservation
                ],201);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation', 'errors' => $e->errors()
            ],422);
        } catch (\Exception $e){
            Log::error('Erreur réservation : ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Erreur serveur', 
                'error' => $e->getMessage()
            ],500);
        }
    }

    /**
     * Annuler une réservation.
     */
    public function cancel($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->update(['status' => 'canceled']);
        return response()->json(['message' => 'Reservation canceled']);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        $reservation = Reservation::findOrFail($id);
        return response()->json($reservation);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reservation $reservation)
    {
        //
    }


}
