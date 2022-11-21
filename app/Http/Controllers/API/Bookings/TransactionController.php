<?php

namespace App\Http\Controllers\API\Bookings;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Bookings\TransportBookingRequest;
use App\Models\Bookings\Transaction;
use App\Models\Transports\Transport;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * @OA\Post(
     *      path="/transport/booking",
     *      operationId="transportBooking",
     *      tags={"Booking"},
     *      summary="Бронирование транспорта",
     *      description="Бронирование транспорта пользователем",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/TransportBookingRequest")
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="Успешное бронирование",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error"
     *      )
     * )
     */
    public function booking(TransportBookingRequest $request)
    {
        $transportId = $request->transport_id;
        $userId = $request->user_id;

        DB::transaction(function () use ($transportId, $userId) {

            $otherActiveTransaction =  Transaction::activeBooking($transportId, $userId);
            $hasError = optional($otherActiveTransaction)->already_has_active_booking_message;

            if (! $hasError) {
                $transport = Transport::lockTransport($transportId);

                switch ($transport->user_id) {

                    case null:
                        $transport->createReserve($userId);
                        break;

                    case $userId:
                        $hasError = optional($transport->latest_booking)->already_reserved_message;
                        break;

                    default:
                        break;
                }
            }

            if ($hasError)
                throw new \Exception($hasError);

        }, env('DEADLOCK_ATTEMPT_COUNT', 3));

        return response()->noContent();
    }

    /**
     * @OA\Post(
     *      path="/transport/un-booking",
     *      operationId="transportUnBooking",
     *      tags={"Booking"},
     *      summary="Окончание брони транспорта",
     *      description="Закончить бронирование транспорта",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/TransportBookingRequest")
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="Успешное окончание брони",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error"
     *      )
     * )
     */
    public function unBooking(TransportBookingRequest $request)
    {
        $transportId = $request->transport_id;
        $userId = $request->user_id;

        DB::transaction(function () use ($transportId, $userId) {

            $transport = Transport::lockTransport($transportId, $userId);
            $transaction = Transaction::latestBooking($transportId, $userId);
            if (! $transaction)
                throw new \Exception(config('booking.not_have_reserved_transport'));

            $transport->closeReserve($transaction);

        }, env('DEADLOCK_ATTEMPT_COUNT', 3));

        return response()->noContent();
    }
}
