<?php

namespace App\Models\Bookings;

use App\Models\Transports\Transport;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    /**
     * Define model table name on DB
     *
     * @var string
     */
    protected $table = 'booking_transactions';

    /**
     * Define primary key field
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Define guarder list
     *
     * @var string[]
     */
    protected $guarded = ['id'];

    /**
     * Define transaction date fields
     *
     * @var string[]
     */
    protected $dates = [
        'reserved_from',
        'reserved_to'
    ];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Getting last booked transport for current user
     *
     * @param int $transportId
     * @param int $userId
     * @return mixed
     */
    public static function latestBooking(int $transportId, int $userId)
    {
        return self::withTransportId($transportId)
            ->withUserId($userId)
            ->isActive()
            ->first();
    }

    /**
     * Getting any booked transport not equal current transport id
     *
     * @param int $transportId
     * @param int $userId
     * @return mixed
     */
    public static function activeBooking(int $transportId, int $userId)
    {
        return self::notUseTransportId($transportId)
            ->withUserId($userId)
            ->isActive()
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Define getting model booked user relation instance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transport()
    {
        return $this->belongsTo(Transport::class, 'transport_id', 'id');
    }

    /**
     * Define getting model booked user relation instance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Define adding filter scope with transport id
     *
     * @param  Builder  $query
     * @param  null  $value
     * @return Builder
     */
    public function scopeWithTransportId(Builder $query, $value = null)
    {
        return ! is_null($value) ? $query->where('transport_id', $value) : $query;
    }

    /**
     * Define adding filter scope with user id
     *
     * @param  Builder  $query
     * @param  null  $value
     * @return Builder
     */
    public function scopeWithUserId(Builder $query, $value = null)
    {
        return ! is_null($value) ? $query->where('user_id', $value) : $query;
    }

    /**
     * Define adding active filter scope
     *
     * @param  Builder  $query
     * @param  null  $value
     * @return Builder
     */
    public function scopeIsActive(Builder $query, $value = null)
    {
        return $query->whereNull('reserved_to');
    }

    /**
     * Define adding filter scope with not using current transport id
     *
     * @param  Builder  $query
     * @param  null  $value
     * @return Builder
     */
    public function scopeNotUseTransportId(Builder $query, $value = null)
    {
        return ! is_null($value) ? $query->where('transport_id', '!=', $value) : $query;
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /**
     * Getting already reserved message attribute
     *
     * @return string
     */
    public function getAlreadyReservedMessageAttribute()
    {
        return config('booking.already_reserved') .  $this->reserved_from->toDateTimeString();
    }

    /**
     * Getting already having active reserved transport message attribute
     *
     * @return string
     */
    public function getAlreadyHasActiveBookingMessageAttribute()
    {
        return config('booking.already_has_active_booking') .  $this->reserved_from->toDateTimeString();
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
