<?php

namespace App\Http\Controllers\Board;

use App\Board;
use App\FileStorage;
use App\PostAttachment;
use App\Http\Controllers\Controller;
use Gate;
use File;
use Request;
use Response;
use Settings;
use Storage;
use Validator;
use Event;
use App\Events\AttachmentWasModified;

/**
 * Manages content attachments.
 *
 * @category   Controller
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class AttachmentController extends Controller
{
    const VIEW_VERIFY = 'board.verify';
    const VIEW_VERIFY_PASS = 'board.verify.password';
    const VIEW_VERIFY_MOD = 'board.verify.mod';

    /**
     * Delete a post's attachment.
     *
     * @param \App\PostAttachment $attachment
     *
     * @return Response
     */
    public function getDeleteAttachment(Board $board, PostAttachment $attachment)
    {
        if (!$attachment->exists) {
            return abort(404);
        }

        if (!Gate::any(['delete', 'delete-self'], $attachment->post)) {
            return abort(403);
        }

        $scope = [
            'board' => $board,
            'mod' => user()->can('delete', $attachment->post),
        ];

        return $this->makeView(static::VIEW_VERIFY, $scope);
    }

    /**
     * Toggle a post's spoiler status.
     *
     * @param \App\PostAttachment $attachment
     *
     * @return Response
     */
    public function getSpoilerAttachment(Board $board, PostAttachment $attachment)
    {
        if (!$attachment->exists) {
            return abort(404);
        }

        if (!Gate::any(['delete', 'delete-self'], $attachment->post)) {
            return abort(403);
        }

        $scope = [
            'board' => $board,
            'mod' => user()->can('delete', $attachment->post),
        ];

        return $this->makeView(static::VIEW_VERIFY, $scope);
        $attachment->is_spoiler = !$attachment->is_spoiler;
        $attachment->save();

        Event::dispatch(new AttachmentWasModified($attachment));

        return redirect()->back();
    }

    /**
     * Delete a post's attachment.
     *
     * @param \App\PostAttachment $attachment
     *
     * @return Response
     */
    public function postDeleteAttachment(Board $board, PostAttachment $attachment)
    {
        if (!$attachment->exists) {
            return abort(404);
        }

        $input = Request::all();

        $validator = Validator::make($input, [
            'scope' => 'required|string|in:other,self',
            'confirm' => 'boolean|required_if:scope,other',
            'password' => 'string|required_if:scope,self',
        ]);

        if (!$validator->passes()) {
            return redirect()
                ->back()
                ->withInput($input)
                ->withErrors($validator->errors());
        }

        if ($input['scope'] == 'other') {
            $this->authorize('delete', $attachment->post);
            $this->log('log.attachment.delete', $attachment->post, [
                'board_uri' => $attachment->post->board_uri,
                'board_id' => $attachment->post->board_id,
                'post_id' => $attachment->post->post_id,
                'file' => $attachment->file->hash,
            ]);
        }
        elseif ($input['scope'] == 'self') {
            $this->authorize('delete-self', $attachment->post);
            if (!$attachment->post->checkPassword($input['password'])) {
                return redirect()
                    ->back()
                    ->withInput($input)
                    ->withErrors([
                        'password' => \Lang::get('validation.password', [
                            'attribute' => 'password',
                        ]),
                    ]);
            }
        }

        $attachment->is_deleted = true;
        $attachment->save();
        $attachment->post->forget();

        Event::dispatch(new AttachmentWasModified($attachment));


        return redirect($attachment->post->getUrl());
    }

    /**
     * Delete a post's attachment.
     *
     * @param \App\PostAttachment $attachment
     *
     * @return Response
     */
    public function postSpoilerAttachment(Board $board, PostAttachment $attachment)
    {
        if (!$attachment->exists) {
            return abort(404);
        }

        $input = Request::all();

        $validator = Validator::make($input, [
            'scope' => 'required|string|in:other,self',
            'confirm' => 'boolean|required_if:scope,other',
            'password' => 'string|required_if:scope,self',
        ]);

        if (!$validator->passes()) {
            return redirect()
                ->back()
                ->withInput($input)
                ->withErrors($validator->errors());
        }

        if ($input['scope'] == 'other') {
            $this->authorize('delete', $attachment->post);

            $this->log(
                !$attachment->is_spoiler ? 'log.attachment.spoiler' : 'log.attachment.unspoiler',
                $attachment->post,
                [
                    'board_uri' => $attachment->post->board_uri,
                    'board_id' => $attachment->post->board_id,
                    'post_id' => $attachment->post->post_id,
                    'file' => $attachment->file->hash,
                ]
            );
        }
        elseif ($input['scope'] == 'self') {
            $this->authorize('delete-self', $attachment->post);
            if (!$attachment->post->checkPassword($input['password'])) {
                return redirect()
                    ->back()
                    ->withInput($input)
                    ->withErrors([
                        'password' => \Lang::get('validation.password', [
                            'attribute' => 'password',
                        ]),
                    ]);
            }
        }

        $attachment->is_spoiler = !$attachment->is_spoiler;
        $attachment->save();
        $attachment->post->forget();

        Event::dispatch(new AttachmentWasModified($attachment));


        return redirect($attachment->post->getUrl());
    }
}
