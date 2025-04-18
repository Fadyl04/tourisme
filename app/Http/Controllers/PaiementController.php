<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use App\Models\Paiement;
use App\Models\Reservation;
use FedaPay\FedaPay;
use FedaPay\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\callback;

class PaiementController extends Controller
{
    public function initierPaiement(Request $request)
    {
        try {
            $request->validate([
                'id_reservation' => 'required|exists:reservations,id',
                'method' => 'required|in:mobile_money,bank_card'
            ]);
            $reservation = Reservation::with(['user', 'site', 'event'])->findOrFail($request->id_reservation);
            if (Paiement::where('id_reservation',$reservation->id)->where('status', 'paid')->exists()) { 
                return response()->json([
                    'message' => 'Cette reservation a été déjâ payée.'
                ],400);
            }
    
            // Initialiser le paiement
            FedaPay::setApiKey(config('services.fedapay.secret_key'));
            FedaPay::setEnvironment(config('services.fedapay.mode'));
    
            // Vérifier et mapper le mode de paiement de Fedepay
            $paiementMethod = match($request->method){
                'mobile_money' => 'MTN', // ou 'MOOV' selon le reseau
                'bank_card' => 'card',
                default => null,
            };
            if (!$paiementMethod) {
                return response()->json([
                    'message' => 'Méthode de paiement invalide'
                ],400);
            }
            // Déterminer le montant
            if ($reservation->id_event && $reservation->event) {
                $montant = $reservation->event->amount;
            } elseif ($reservation->id_site && $reservation->site) {
                $montant = $reservation->site->amount;
            } else {
                return response()->json([
                    'message' => 'Aucun site ou événement valide associé à cette réservation.'
                ], 400);
            }
            $transaction = Transaction::create([
                'amount' => (int) $montant,
                'currency' => ['iso' => 'XOF'],
                'description' => "Paiement de la réservation #{$reservation->id}",
                'callback_url' => route('paiement.callback', ['id_reservation' => $reservation->id]),
                'customer' => [
                    'firstname' => $reservation->user->user_name,
                    'email' => $reservation->user->user_email
                ],
                'method' => $paiementMethod 
            ]);
    
            $paiementUrl = $transaction->generateToken()->url;
            Log::info("URL de paiement générée :" . $paiementUrl);
            Paiement::create([
                'id_reservation' => $reservation->id,
                'amount'=> $montant, 
                'method' => $request->method,
                'status' => 'pending',
                'id_transaction' => $transaction->id // ID de la transaction de FedaPay
            ]);
    
            return response()->json([
                'message' => 'Paiement initié avec succès',
                'paiement_url' =>$paiementUrl,
            ],201);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'initiation du paiement:' .$e->getMessage());
            return response()->json([
                'message' => " Une erreur est survenue lors de l'initiation du paiement.",
                'error' => $e->getMessage(),
            ],500);
        }
    }



    public function callbackPaiement(Request $request)
    {
        try {
            Log::info('Transaction callback reçue', $request->all());
            // Initialiser FedaPay
            FedaPay::setApiKey(env('FEDAPAY_SECRET_KEY'));
            FedaPay::setEnvironment(env('FEDAPAY_MODE', 'sandbox'));

            // Recupérer la reservation
            $Idreservation = $request-> id_reservation ?? Paiement::where('status','pending')->latest()->value('id_reservation');
            abort_unless($Idreservation, 400, 'Reservation introuvable');

            $reservation = Reservation::findOrFail($Idreservation);
            $paiement = Paiement::where('id_reservation', $reservation->id)->where('status', 'pending')->firstOrFail();
            // Recupérer la transaction depuis FedaPay
            $transaction = Transaction::retrieve($paiement->id_transaction);
            abort_unless($transaction && isset($transaction->id), 400, 'Transaction introuvable ou invalide');
            Log::info('Transaction récupérée depuis FedaPay', ['transaction' => $transaction]);

            // Mise à jour du paiement,  de la réservation et du nombre de plce si le paiement est validé
            if ($transaction->status == 'approved') {
                $paiement->update(['status' => 'paid']);
                $reservation->update(['status' => 'confirmed']);
            
                // Gestion du nombre de places
                if ($reservation->site_id) {
                    $site = $reservation->site;
                    if ($site->places > 0) {
                        $site->places -= 1;
                        $site->save();
                    } else {
                        Log::warning("Plus de places disponibles pour le site ID: " . $site->id);
                        return response()->json([
                            'message' => 'Plus de places disponibles pour ce site.'
                        ], 400);
                    }
                } elseif ($reservation->event_id) {
                    $event = $reservation->event;
                    if ($event->places > 0) {
                        $event->places -= 1;
                        $event->save();
                    } else {
                        Log::warning("Plus de places disponibles pour l\'événement ID: " . $event->id);
                        return response()->json([
                            'message' => 'Plus de places disponibles pour cet événement.'
                        ], 400);
                    }
                }
            
                // Notification et log
                //$reservation->user->notify(new Notice($reservation));
                Log::info("Paiement validé, réservation confirmée et places mises à jour", ['id_reservation' => $Idreservation]);
            
                return response()->json([
                    "message" => "Paiement validé et place réservée"
                ], 200);
            }
            
            $paiement->update([ 'status' => 'failure' ]);
            Log::warning("Le paiement à échoué", ['id_reservation' => $Idreservation]);
            return response()->json([
                'message' => 'Le paiement à échoué'
            ], 400);

        } catch (\Exception $t) {
            //throw $th;
            Log::error("Erreur lors du traitement du paiement", ['error' => $t->getMessage()]);
            return response()->json([
                'message' => 'Erreur lors du traitement du paiement',
                'error' => $t->getMessage()
            ], 500);

        }
    }
}
