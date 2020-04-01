<?php

namespace App;

use App\Contracts\PseudoEnum  as PseudoEnumContract;
use App\Traits\PseudoEnum as PseudoEnum;
use Illuminate\Database\Eloquent\Model;

class BoardAsset extends Model implements PseudoEnumContract
{
    use PseudoEnum;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'board_assets';

    /**
     * The database primary key.
     *
     * @var string
     */
    protected $primaryKey = 'board_asset_id';

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'board_asset_id' => 'int',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'board_uri',
        'file_id',
        'asset_type',
        'asset_name',
    ];

    /**
     * Psuedo-enum attributes and their permissable values.
     *
     * @var array
     */
    protected $enum = [
        'asset_type' => [
            'board_banner',
            'board_banned',
            'board_icon',
            'board_flags',
            'file_deleted',
            'file_spoiler',
        ],
    ];

    public function board()
    {
        return $this->belongsTo('\App\Board', 'board_uri');
    }

    public function flagPosts()
    {
        return $this->hasMany('\App\Post', 'flag_id', 'file_id');
    }

    public function storage()
    {
        return $this->belongsTo('\App\FileStorage', 'file_id');
    }

    public function asHTML()
    {
        return "<img src=\"{$this->getURL()}\" alt=\"/{$this->board_uri}/\" class=\"board-asset asset-{$this->asset_type}\" />";
    }

    public function getDisplayName()
    {
        return $this->asset_name ?: '';
    }

    public function getURL()
    {
        return route('static.file.hash', [
            'board' => $this->board,
            'hash' => $this->storage->hash,
            'filename' => 'banner.png',
        ]);
    }

    public function scopeWhereBoardIcon($query)
    {
        return $query->where('asset_type', 'board_icon');
    }
}
