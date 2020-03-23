<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FileAttachment extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'file_attachments';

    /**
     * The database primary key.
     *
     * @var string
     */
    protected $primaryKey = 'attachment_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'post_id',
        'file_id',
        'filename',
        'is_spoiler',
        'is_deleted',
        'position',
    ];

    /**
     * Indicates if Laravel should set created_at and updated_at timestamps.
     *
     * @var array
     */
    public $timestamps = false;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var array
     */
    public $incrementing = false;

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function storage()
    {
        return $this->belongsTo(FileStorage::class, 'file_id');
    }

    /**
     * Ties database triggers to the model.
     *
     * @static
     */
    public static function boot()
    {
        parent::boot();

        // Setup event bindings...

        // Fire events on post created.
        static::created(function (FileAttachment $attachment) {
            if (!is_link($attachment->storage->getFullPath())) {
                $attachment->storage->processAttachment($attachment);
            }
        });
    }

    /**
     * Returns a few posts for the front page.
     *
     * @static
     *
     * @param int  $number  How many to pull.
     * @param bool $sfwOnly If we only want SFW boards.
     *
     * @return Collection of static
     */
    public static function getRecentImages($number = 16)
    {
        $sfw = static::getRecentImagesByWorksafe($number, true);
        $nsfw = static::getRecentImagesByWorksafe($number, false);
        $images = $sfw->merge($nsfw)->sortByDesc('attachment_id');

        return $images;
    }

    protected static function getRecentImagesByWorksafe($number = 16, $sfw = false)
    {
        $query = static::where('is_spoiler', false)
            ->where('is_deleted', false)
            ->whereHas('storage', function ($query) {
                $query->where('has_thumbnail', true);
            })
            ->whereHas('post.board', function ($query) use ($sfw) {
                $query->where('is_indexed', '=', true);
                $query->where('is_overboard', '=', true);
                $query->where('is_worksafe', '=', $sfw);
            })
            ->with('storage')
            ->with('post.board')
            ->take($number);

        if ($query->getQuery()->getConnection() instanceof \Illuminate\Database\PostgresConnection) {
            // PostgreSQL does not support the MySQL standards non-compliant group_by syntax.
            // DISTINCT itself selects distinct combinations [attachment_id,file_id], not just file_id.
            // We have to use raw SQL to accomplish this.
            $query->select(
                \DB::raw('distinct on (file_id) *')
            );

            $query->orderBy('file_id', 'desc');
        } else {
            $query->orderBy('attachment_id', 'desc');
            $query->groupBy('file_id');
        }

        return $query->get();
    }

    public function scopeWhereForBoard($query, Board $board)
    {
        return $query->whereHas('post.board', function ($query) use ($board) {
            $query->where('board_uri', $board->board_uri);
        });
    }
}
