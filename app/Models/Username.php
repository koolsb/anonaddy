<?php

namespace App\Models;

use App\Traits\HasEncryptedAttributes;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Username extends Model
{
    use HasEncryptedAttributes;
    use HasFactory;
    use HasUuid;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $encrypted = [
        'description',
        'from_name',
    ];

    protected $fillable = [
        'user_id',
        'username',
        'description',
        'from_name',
        'active',
        'catch_all',
        'can_login',
    ];

    protected $casts = [
        'id' => 'string',
        'user_id' => 'string',
        'active' => 'boolean',
        'catch_all' => 'boolean',
        'can_login' => 'boolean',
        'default_recipient_id' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        Username::deleting(function ($username) {
            $username->aliases()->withTrashed()->forceDelete();
            DeletedUsername::create(['username' => $username->username]);
        });
    }

    /**
     * Set the username.
     */
    public function setUsernameAttribute($value)
    {
        $this->attributes['username'] = strtolower($value);
    }

    /**
     * Get the user for the username.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all of the usernames's aliases.
     */
    public function aliases()
    {
        return $this->morphMany(Alias::class, 'aliasable');
    }

    /**
     * Get the usernames's default recipient.
     */
    public function defaultRecipient()
    {
        return $this->hasOne(Recipient::class, 'id', 'default_recipient_id');
    }

    /**
     * Set the usernames's default recipient.
     */
    public function setDefaultRecipientAttribute($recipient)
    {
        $this->attributes['default_recipient_id'] = $recipient->id;
        $this->setRelation('defaultRecipient', $recipient);
    }

    /**
     * Deactivate the username.
     */
    public function deactivate()
    {
        $this->update(['active' => false]);
    }

    /**
     * Activate the username.
     */
    public function activate()
    {
        $this->update(['active' => true]);
    }

    /**
     * Disable catch-all for the username.
     */
    public function disableCatchAll()
    {
        $this->update(['catch_all' => false]);
    }

    /**
     * Enable catch-all for the username.
     */
    public function enableCatchAll()
    {
        $this->update(['catch_all' => true]);
    }

    /**
     * Disallow login for the username.
     */
    public function disallowLogin()
    {
        $this->update(['can_login' => false]);
    }

    /**
     * Allow login for the username.
     */
    public function allowLogin()
    {
        $this->update(['can_login' => true]);
    }
}
