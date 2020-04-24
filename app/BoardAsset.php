<?php

namespace App;

use App\Contracts\PseudoEnum  as PseudoEnumContract;
use App\Traits\PseudoEnum as PseudoEnum;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class BoardAsset extends Model implements Htmlable, PseudoEnumContract
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
        return $this->belongsTo(Board::class, 'board_uri');
    }

    public function flagPosts()
    {
        return $this->hasMany(Post::class, 'flag_id', 'file_id');
    }

    public function storage()
    {
        return $this->belongsTo(FileStorage::class, 'file_id');
    }

    /**
     * Static values for asset requirements.
     *
     * @var array
     */
    public static $validationRules = [
        'board_banned' => [ 'dimensions:max_height=500,max_width=500,min_height=100,min_width=100', 'max:250', ],
        'board_banner' => [ 'dimensions:max_height=200,max_width=600,ratio=3/1', 'max:1024', ],
        'board_flags.file.*' => [ 'dimensions:max_height=33,max_width=90', 'max:100', ],
        'board_icon' => [ 'dimensions:width=64,height=64,ratio=1/1', 'max:50', ],
        'file_deleted' => [ 'dimensions:max_height=250,max_width=250,min_height=100,min_width=100', 'max:250', ],
        'file_spoiler' => [ 'dimensions:max_height=250,max_width=250,min_height=100,min_width=100', 'max:250', ],
    ];

    public function getDisplayName()
    {
        return $this->asset_name ?: '';
    }

    public function getUrl()
    {
        return route('static.file.hash', [
            'board' => $this->board,
            'hash' => $this->storage->hash,
            'filename' => "{$this->created_at->timestamp}.{$this->storage->guessExtension()}",
        ]);
    }

    public function toHtml()
    {
        return "<img src=\"{$this->getUrl()}\" alt=\"/{$this->board_uri}/\" class=\"board-asset asset-{$this->asset_type}\" />";
    }

    public function scopeWhereBoardIcon($query)
    {
        return $query->where('asset_type', 'board_icon');
    }
}
