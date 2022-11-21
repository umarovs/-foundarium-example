<?php

namespace App\Models\Transports;

use App\Models\Bookings\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transport extends Model
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
    protected $table = 'transports';

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

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Define getting locked transport model instance
     *
     * @param int $transportId
     * @param int|null $userId
     * @return mixed
     */
    public static function lockTransport(int $transportId, int $userId = null)
    {
        return self::query()
            ->lockForUpdate()
            ->with('latest_booking')
            ->withUserId($userId)
            ->withTransportId($transportId)
            ->firstOrFail();
    }

    /**
     * Define setting reserving user information
     *
     * @param int $userId
     * @return void
     */
    public function createReserve(int $userId)
    {
        $this->user_id = $userId;
        $this->save();

        $this->transactions()
            ->create([
                'user_id' => $userId,
                'reserved_from' => now()
            ]);
    }

    /**
     * @param Transaction $transaction
     * @return void
     */
    public function closeReserve(Transaction $transaction)
    {
        $this->user_id = null;
        $this->save();

        $transaction->reserved_to = now();
        $transaction->save();
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Define getting model transactions relation instance
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'transport_id', 'id');
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

    /**
     *
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latest_booking()
    {
        return $this->hasOne(Transaction::class, 'transport_id', 'id')
            ->whereNull('reserved_to');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Define adding filter scope with model id
     *
     * @param  Builder  $query
     * @param  null  $value
     * @return Builder
     */
    public function scopeWithTransportId(Builder $query, $value = null)
    {
        return ! is_null($value) ? $query->whereId($value) : $query;
    }

    /**
     * Define adding filter scope not booked models
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsNotReserved(Builder $query)
    {
        return $query->whereNull('user_id');
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

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
