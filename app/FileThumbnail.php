<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use File;
use Request;
use Settings;
use Storage;

/**
 * Model representing file many-to-many file thumbnail associations.
 *
 * @category   Model
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2020 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class FileThumbnail extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'file_thumbnails';

    /**
     * The database primary key.
     *
     * @var string
     */
    protected $primaryKey = 'file_thumbnail_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'file_thumbnail_id',
        'source_id',
        'thumbnail_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'file_thumbnail_id' => "int",
        'source_id' => "int",
        'thumbnail_id' => "int",
    ];

    public function source()
    {
        return $this->hasOne(FileStorage::class, 'file_id', 'source_id');
    }

    public function thumbnail()
    {
        return $this->hasOne(FileStorage::class, 'file_id', 'thumbnail_id');
    }
}
