<?php

namespace App\Http\Requests\API\Bookings;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *      title="Transport Booking Request",
 *      description="Данные для бронирования транспорта",
 *      type="object",
 *      required={"transport_id", "user_id"},
 *      @OA\Property(
 * 		    property="transport_id",
 * 		    type="int",
 * 		    example=1
 * 	    ),
 *     @OA\Property(
 * 		    property="user_id",
 * 		    type="int",
 * 		    example=1
 * 	    )
 * )
 */
class TransportBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'transport_id' => 'required|exists:transports,id',
            'user_id' => 'required|exists:users,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'transport_id' => 'Транспорт',
            'user_id' => 'Пользователь'
        ];
    }
}
