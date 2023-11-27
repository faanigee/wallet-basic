<?php

declare(strict_types=1);

namespace Faanigee\Wallet\Models;

use function array_key_exists;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Support\Str;



/**
 * Class Wallet.
 *
 * @property string                          $holder_type
 * @property int|string                      $holder_id
 * @property string                          $name
 * @property string                          $slug
 * @property string                          $uuid
 * @property string                          $description
 * @property null|array                      $meta
 * @property int                             $decimal_places

 * @property string                          $credit
 * @property string                          $currency
 *
 * @method int getKey()
 */
class Wallet extends Model
{

    /**
     * @var string[]
     */
    protected $fillable = [
        'holder_type',
        'holder_id',
        'wallet_status',
        'name',
        'slug',
        'uuid',
        'description',
        'meta',
        'balance',
        'decimal_places',
        'created_at',
        'updated_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'decimal_places' => 'int',
        'meta' => 'json',
    ];

    /**
     * @var array<string, int|string>
     */
    protected $attributes = [
        'balance' => 0,
        'decimal_places' => 2,
    ];

    public function getTable(): string
    {
        if ((string) $this->table === '') {
            $this->table = config('wallet.wallet.table', 'wallets');
        }

        return parent::getTable();
    }

    public function setNameAttribute(string $name): void
    {
        $this->attributes['name'] = $name;

        /**
         * Must be updated only if the model does not exist or the slug is empty.
         */
        if (!$this->exists && !array_key_exists('slug', $this->attributes)) {
            $this->attributes['slug'] = Str::slug($name);
        }
    }
    public function getOriginalBalanceAttribute(): string
    {
        return (string) $this->getRawOriginal('balance', 0);
    }

    public function getAvailableBalanceAttribute(): float|int|string
    {
        return $this->walletTransactions()
            ->where('confirmed', true)
            ->sum('amount');
    }

    public function holder(): MorphTo
    {
        return $this->morphTo();
    }

    public function getCreditAttribute(): string
    {
        return (string) ($this->meta['credit'] ?? '0');
    }

    public function getCurrencyAttribute(): string
    {
        return $this->meta['currency'] ?? Str::upper($this->slug);
    }

    public function getBalance()
    {
        $this->refresh();
        return (int) $this->balance;
    }

}
