<?php

namespace App\Http\Requests;

use App\Ban;
use App\Board;
use App\FileStorage;
use App\Post;
use App\PostChecksum;
use App\Contracts\ApiController as ApiContract;
use App\Http\Controllers\API\ApiController;
use App\Services\ContentFormatter;
use App\Support\IP;
use App\Exceptions\BannedException;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Cache;
use Session;

/**
 * Handles a new post.
 *
 * @category   Request
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.5.1
 */
class PostRequest extends Request implements ApiContract
{
    use ApiController;

    const VIEW_BANNED = 'errors.banned';

    /**
     * Input items that should not be returned when reloading the page.
     *
     * @var array
     */
    protected $dontFlash = ['password', 'password_confirmation', 'captcha'];

    /**
     * The board pertinent to the request.
     *
     * @var App\Board
     */
    protected $board;

    /**
     * A ban pulled during validation checks.
     *
     * @var App\Ban
     */
    protected $ban;

    /**
     * Indicates if we're checking a traditional file post or a dropzone file array.
     *
     * @var bool
     */
    protected $dropzone;

    /**
     * The thread we're replying to.
     *
     * @var App\Post
     */
    protected $thread;

    /**
     * The user.
     *
     * @var App\Trait\PermissionUser
     */
    protected $user;

    /**
     * Does this post respect the robot?
     * If set to false during validation and failedValidation is triggered,
     * an automatic board ban will be issued by The Robot for a variable length.
     *
     * @var bool
     */
    protected $respectTheRobot = true;

    /**
     * Fetches the user and our board config.
     */
    public function __construct(Board $board, Post $thread)
    {
        $this->board = $board;
        $this->thread = $thread->exists ? $thread : false;
    }

    /**
     * Get all form input.
     *
     * @return array
     */
    public function all($keys = NULL)
    {
        $input = parent::all($keys);

        $this->dropzone = isset($input['dropzone']);

        if (isset($input['files']) && is_array($input['files'])) {
            // Having an [null] file array passes validation.
            $input['files'] = array_filter($input['files']);
        }

        if (isset($input['capcode']) && $input['capcode']) {
            $user = user();

            if ($user && !$user->isAnonymous()) {
                $role = $user->roles->find((int) $input['capcode']);

                if ($role && $role->capcode != '') {
                    $input['capcode_id'] = (int) $role->role_id;
                    // $input['author']     = $user->username;
                }
                else {
                    $this->failedAuthorization();
                }
            } else {
                unset($input['capcode']);
            }
        }

        if (user()->cannot('author', [Post::class, $this->board])) {
            unset($input['author']);
        }

        if (user()->cannot('subject', [Post::class, $this->board])) {
            unset($input['subject']);
        }

        return $input;
    }

    /**
     * Returns if the client has access to this form.
     *
     * @return bool
     */
    public function authorize()
    {
        // Ban check.
        $ban = Ban::getBan($this->ip(), $this->board->board_uri);

        if ($ban) {
            $this->ban = $ban;
        }

        if ($this->thread instanceof Post) {
            return user()->can('reply', $this->thread);
        }
        else {
            return user()->can('post', $this->board);
        }
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \App\Validation\BannedException;
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->ban instanceof Ban) {
            throw (new BannedException($validator))
                        ->errorBag($this->errorBag)
                        ->ban($this->ban)
                        ->redirectTo($this->ban->getUrl());
        }

        return parent::failedValidation($validator);
    }

    /**
     * Returns validation rules for this request.
     *
     * @return array
     */
    public function rules()
    {
        $board = $this->board;
        $user = user();
        $rules = [
            'author' => [ 'nullable', 'string', 'encoding:UTF-8', 'max:60', ],
            'email' => [ 'nullable', 'string', 'encoding:UTF-8', 'max:320', ],
            'subject' => [ 'string', 'encoding:UTF-8', 'max:60', ],
        ];

        // Add a subject requirement if we need one.
        if (!$this->thread && $board->getConfig('threadRequireSubject')) {
            $rules['subject'][] = 'required';
        }
        else {
            $rules['subject'][] = 'nullable';
        }

        // Modify the validation rules based on what we've been supplied.
        if ($board && $user) {
            $rules['body'] = [
                'encoding:UTF-8',
                'min:'.$board->getConfig('postMinLength', 0),
                'max:'.$board->getConfig('postMaxLength', 65534),
            ];

            // post formatting requirements
            $newLineMax = (int) $board->getConfig('postNewLines', 0);
            if ($newLineMax > 0) {
                $rules['body'][] = "ugc_height:{$newLineMax}";
            }

            if ($user->cannot('attach', $board)) {
                $rules['body'][] = 'required';
                $rules['files'][] = 'array';
                $rules['files'][] = 'max:0';
            }
            else {
                $rules['body'][] = 'required_without:files';

                $attachmentsMax = max(0, (int) $board->getConfig('postAttachmentsMax', 1));

                // There are different rules for starting threads.
                if (!($this->thread instanceof Post)) {
                    $attachmentsMin = max(0, (int) $board->getConfig('postAttachmentsMin', 0), (int) $board->getConfig('threadAttachmentsMin', 0));
                } else {
                    $attachmentsMin = max(0, (int) $board->getConfig('postAttachmentsMin', 0));
                }


                // Add the rules for file uploads.
                if ($this->dropzone) {
                    $fileToken = 'files.hash';

                    // JavaScript enabled hash posting
                    static::rulesForFileHashes($board, $rules);

                    if ($attachmentsMin > 0) {
                        $rules['files.name'][] = 'required';
                        $rules['files.name'][] = "min:{$attachmentsMin}";
                        $rules['files.hash'][] = 'required';
                        $rules['files.hash'][] = "min:{$attachmentsMin}";
                    }
                }
                else {
                    $fileToken = 'files';

                    // Vanilla HTML upload
                    static::rulesForFiles($board, $rules);

                    if ($attachmentsMin > 0) {
                        $rules['files'][] = 'required';
                        $rules['files'][] = "min:{$attachmentsMin}";
                    }
                }

                for ($attachment = 0; $attachment < $attachmentsMax; ++$attachment) {
                    // Can only attach existing files.
                    if (!$user->can('create-attachment')) {
                        $rules["{$fileToken}.{$attachment}"][] = 'file_old';
                    }
                    // Can only attach new files.
                    elseif ($user->can('create-attachment') && !$user->can('attach', $board)) {
                        $rules["{$fileToken}.{$attachment}"][] = 'file_new';
                    }

                    for ($otherAttachment = 0; $otherAttachment < $attachment; ++$otherAttachment) {
                        $rules["{$fileToken}.{$attachment}"][] = "different:{$fileToken}.{$otherAttachment}";
                    }
                }
            }
        }

        return $rules;
    }

    /**
     * Returns rules specifically for files for a board.
     *
     * @param  Board  $board
     * @param  array  $rules (POINTER, MODIFIED)
     *
     * @return array  Validation rules.
     */
    public static function rulesForFiles(Board $board, array &$rules)
    {
        $attachmentsMax = max(0, (int) $board->getConfig('postAttachmentsMax', 1));
        $attachmentsFileSize = site_setting('attachmentFilesize') * 1.024;

        $rules['spoilers'] = 'boolean';

        $rules['files'][] = 'array';
        $rules['files'][] = "max:{$attachmentsMax}";

        // Create an additional rule for each possible file.
        for ($attachment = 0; $attachment < $attachmentsMax; ++$attachment) {
            $rules["files.{$attachment}"] = [
                'between:0,'.$attachmentsFileSize,
                'file_integrity',
            ];
        }
    }

    /**
     * Returns rules specifically for dropzone files for a board.
     *
     * @param  Board  $board
     * @param  array  $rules (POINTER, MODIFIED)
     *
     * @return array  Validation rules.
     */
    public static function rulesForFileHashes(Board $board, array &$rules)
    {
        global $app;

        $attachmentsMax = max(0, (int) $board->getConfig('postAttachmentsMax', 1));

        $rules['files'][] = 'array';
        $rules['files'][] = 'between:2,3'; // [files.hash,files.name] or +[files.spoiler]

        $rules['files.name'] = ['array', "max:{$attachmentsMax}"];
        $rules['files.hash'] = ['array', "max:{$attachmentsMax}"];
        $rules['files.spoiler'] = ['array', "max:{$attachmentsMax}"];

        // Create an additional rule for each possible file.
        for ($attachment = 0; $attachment < $attachmentsMax; ++$attachment) {
            $rules["files.name.{$attachment}"] = [
                'string',
                "required_with:files.hash.{$attachment}",
                'between:1,254',
                'file_name',
            ];

            $rules["files.hash.{$attachment}"] = [
                'string',
                "required_with:files.name.{$attachment}",
                'sha256',
                'exists:files,hash',
            ];

            $rules["files.spoiler.{$attachment}"] = [
                'boolean',
            ];
        }
    }


    /**
     * Validate the class instance.
     * This overrides the default invocation to provide additional rules after the controller is setup.
     */
    public function validate()
    {
        $board = $this->board;
        $thread = $this->thread;
        $user = user();

        $ip = new IP($this->ip());
        $carbon = new \Carbon\Carbon();

        $validator = $this->getValidatorInstance();
        $messages = $validator->errors();
        $isReply = $this->thread instanceof Post;

        if ($this->ban instanceof Ban) {
            $messages->add('banned', trans('validation.banned'));
            $this->failedValidation($validator);
            return;
        }

        // Check flood timers
        if ($isReply) {
            $floodTime = site_setting('postFloodTime');
            $cacheKeys = [ 'last_post_for_session:' . Session::getId() ];

            if ($user->isAccountable()) {
                $cacheKeys[] = "last_post_for_ip:" . $ip->toLong();
            }

            foreach ($cacheKeys as $cacheKey) {
                $nextPostTime = Carbon::createFromTimestamp(Cache::get($cacheKey, 0) + $floodTime);

                if ($nextPostTime->isFuture()) {
                    $timeDiff = $nextPostTime->diffInSeconds() + 1;

                    $messages->add('flood', trans_choice('validation.post_flood', $timeDiff, [
                        'time_left' => $timeDiff,
                    ]));

                    $this->failedValidation($validator);
                    return;
                }
            }
        }
        else {
            $floodTime = site_setting('threadFloodTime');

            $nextPostTime = Carbon::createFromTimestamp(Cache::get('last_thread_for_'.$ip->toLong(), 0) + $floodTime);

            if ($nextPostTime->isFuture()) {
                $timeDiff = $nextPostTime->diffInSeconds() + 1;

                $messages->add('flood', trans_choice('validation.thread_flood', $timeDiff, [
                    'time_left' => $timeDiff,
                ]));

                $this->failedValidation($validator);
                return;
            }
        }

        // Board-level setting validaiton.
        $validator->sometimes('captcha', 'required|captcha', function ($input) {
            return !user()->can('bypass-captcha');
        });

        // Validate and return errors before we check file originality.
        if (!$validator->passes()) {
            $this->failedValidation($validator);
            return;
        }

        // Validate individual files being uploaded right now.
        $this->validateOriginality();

        if (count($validator->errors())) {
            $this->failedValidation($validator);
        }
        elseif (!$this->passesAuthorization()) {
            $this->failedAuthorization();
        }
    }

    protected function validateOriginality()
    {
        $board = $this->board;
        $thread = $this->thread;
        $user = user();
        $input = $this->all();

        $validated = true;
        $validator = $this->getValidatorInstance();
        $messages = $validator->errors();

        // Process uploads.
        if (isset($input['files'])) {
            $uploads = $input['files'];

            if (count($uploads) > 0) {
                // Standard upload originality and integrity checks.
                if (!$this->dropzone) {
                    foreach ($uploads as $uploadIndex => $upload) {
                        // If a file is uploaded that has a specific filename, it breaks the process.
                        if (method_exists($upload, 'getPathname') && !file_exists($upload->getPathname())) {
                            $validated = false;
                            $messages->add("files.{$uploadIndex}", trans('validation.file_corrupt', [
                                'filename' => $upload->getClientOriginalName(),
                            ]));
                        }
                    }

                    if ($board->getConfig('originalityImages')) {
                        foreach ($uploads as $uploadIndex => $upload) {
                            if (!($upload instanceof UploadedFile)) {
                                continue;
                            }

                            if ($board->getConfig('originalityImages') == 'thread') {
                                if ($thread instanceof Post && $originalPost = FileStorage::checkUploadExists($upload, $board, $thread)) {
                                    $validated = false;
                                    $messages->add("files.{$uploadIndex}", trans('validation.unoriginal_image_thread', [
                                        'filename' => $upload->getClientOriginalName(),
                                        'url' => $originalPost->getURL(),
                                    ]));
                                }
                            } elseif ($originalPost = FileStorage::checkUploadExists($upload, $board)) {
                                $validated = false;
                                $messages->add("files.{$uploadIndex}", trans('validation.unoriginal_image_board', [
                                    'filename' => $upload->getClientOriginalName(),
                                    'url' => $originalPost->getURL(),
                                ]));
                            }
                        }
                    }
                }
                // Dropzone hash checks.
                else {
                    foreach ($uploads['hash'] as $uploadIndex => $upload) {
                        switch ($board->getConfig('originalityImages')) {
                            case "thread" :
                                if ($thread instanceof Post && $originalPost = FileStorage::checkHashExists($upload, $board, $thread)) {
                                    $validated = false;
                                    $messages->add("files.{$uploadIndex}", trans('validation.unoriginal_image_thread', [
                                        'filename' => $uploads['name'][$uploadIndex],
                                        'url' => $originalPost->getURL(),
                                    ]));
                                }
                            break;
                            case "board" :
                                if ($originalPost = FileStorage::checkHashExists($upload, $board)) {
                                    $validated = false;
                                    $messages->add("files.{$uploadIndex}", trans('validation.unoriginal_image_board', [
                                        'filename' => $uploads['name'][$uploadIndex],
                                        'url' => $originalPost->getURL(),
                                    ]));
                                }
                            break;
                            default :
                                ## TODO: OP originality check here, maybe.
                            break;
                        }
                    }
                }
            }
        }

        // Process body checksum for origianlity.
        $strictness = $board->getConfig('originalityPosts');

        if (isset($input['body']) && $strictness) {
            $checksum = Post::makeChecksum($input['body']);

            if ($strictness == 'board' || $strictness == 'boardr9k') {
                $checksums = PostChecksum::getChecksum($checksum, $board);
            } elseif ($strictness == 'site' || $strictness == 'siter9k') {
                $checksums = PostChecksum::getChecksum($checksum);
            }

            if ($checksums->count()) {
                $validated = false;

                $messages->add('body', trans('validation.unoriginal_content'));

                // If we are in R9K mode, set $respectTheRobot property to to false.
                // This will trigger a Robot ban in failedValidation.
                $this->respectTheRobot = !($strictness == 'boardr9k' || $strictness == 'siter9k');
                $this->ban = Ban::addRobotBan($this->board);
            }
        }

        if ($validated !== true) {
            $this->failedValidation($validator);
            return;
        }
    }
}
