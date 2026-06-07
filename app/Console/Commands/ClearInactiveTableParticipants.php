<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\TableParticipant;

class ClearInactiveTableParticipants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-inactive-table-participants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Job que valida que participante da mesa esta inativo e remove ele da mesa';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $Orders = Order::where('payment_status', Order::PAYMENT_STATUS_PAID)
            ->where('updated_at', '<=', now()->subMinutes(5))
            ->get();

        foreach ($Orders as $order) {
            $Participant = TableParticipant::where('id', $order->participant_id)
                ->delete();
        }

        $Participants = TableParticipant::where('created_at', '<=', now()->subMinutes(60))->get();

        foreach ($Participants as $participant) {
            $Order = Order::where('participant_id', $participant->participant_id)->first();

            if (!$Order) {
                $participant->delete();
            }
        }
    }
}
