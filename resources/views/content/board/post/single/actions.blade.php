<span class="post-action-tab">
	<span class="post-action-label post-action-open">@lang('board.action.open')</span>
	
	<ul class="post-action-groups">
		<li class="post-action-group">
			
			<!-- Board specific content management actions -->
			<ul class="post-actions">
				@if ($post->canEdit($user))
				<li class="post-action">
					<a class="post-action-link action-link-edit" href="{!! url("{$board->board_uri}/post/{$post->board_id}/edit") !!}">@lang('board.action.edit')</a>
				</li>
				@endif
				
				@if ($post->canSticky($user))
				<li class="post-action">
					@if (!$post->stickied_at)
					<a class="post-action-link action-link-sticky" href="{!! url("{$board->board_uri}/post/{$post->board_id}/sticky") !!}">@lang('board.action.sticky')</a>
					@else
					<a class="post-action-link action-link-unsticky" href="{!! url("{$board->board_uri}/post/{$post->board_id}/unsticky") !!}">@lang('board.action.unsticky')</a>
					@endif
				</li>
				@endif
				
				@if ($post->canLock($user))
				<li class="post-action">
					@if (!$post->locked_at)
					<a class="post-action-link action-link-lock" href="{!! url("{$board->board_uri}/post/{$post->board_id}/lock") !!}">@lang('board.action.lock')</a>
					@else
					<a class="post-action-link action-link-unlock" href="{!! url("{$board->board_uri}/post/{$post->board_id}/unlock") !!}">@lang('board.action.unlock')</a>
					@endif
				</li>
				@endif
				
				@if ($post->canBumplock($user))
				<li class="post-action">
					@if (!$post->locked_at)
					<a class="post-action-link action-link-bumplock" href="{!! url("{$board->board_uri}/post/{$post->board_id}/bumplock") !!}">@lang('board.action.bumplock')</a>
					@else
					<a class="post-action-link action-link-unbumplock" href="{!! url("{$board->board_uri}/post/{$post->board_id}/unbumplock") !!}">@lang('board.action.unbumplock')</a>
					@endif
				</li>
				@endif
				
			</ul>
			
			<!-- Broad sweeping user & board actions -->
			<ul class="post-actions">
				@if ($board->canBan($user))
				<li class="post-action">
					<a class="post-action-link action-link-ban" href="{!! url("{$board->board_uri}/post/{$post->board_id}/mod/ban") !!}">@lang('board.action.ban')</a>
				</li>
				@endif
				
				@if ($post->canDelete($user))
					<li class="post-action">
						<a class="post-action-link action-link-delete" href="{!! url("{$board->board_uri}/post/{$post->board_id}/mod/delete") !!}">@lang('board.action.delete')</a>
					</li>
					
					@if ($board->canDelete($user))
					<li class="post-action">
						<a class="post-action-link action-link-delete-all" href="{!! url("{$board->board_uri}/post/{$post->board_id}/mod/delete/all") !!}">@lang('board.action.delete_board')</a>
					</li>
					@endif
					
					@if ($board->canBan($user))
					<li class="post-action">
						<a class="post-action-link action-link-ban-delete" href="{!! url("{$board->board_uri}/post/{$post->board_id}/mod/ban/delete") !!}">@lang('board.action.ban_delete')</a>
					</li>
					
					<li class="post-action">
						<a class="post-action-link action-link-ban-delete-all" href="{!! url("{$board->board_uri}/post/{$post->board_id}/mod/ban/delete/all") !!}">@lang('board.action.ban_delete_board')</a>
					</li>
					@endif
				@endif
			</ul>
			
			<ul class="post-actions">
				@if ($user->canDeleteGlobally())
					<li class="post-action">
						<a class="post-action-link action-link-delete-global" href="{!! url("{$board->board_uri}/post/{$post->board_id}/mod/delete/global") !!}">@lang('board.action.delete_global')</a>
					</li>
					
					@if ($user->canBanGlobally())
					<li class="post-action">
						<a class="post-action-link action-link-ban-delete-global" href="{!! url("{$board->board_uri}/post/{$post->board_id}/mod/ban/delete/global") !!}">@lang('board.action.ban_delete_global')</a>
					</li>
					@endif
				@endif
			</ul>
		</li>
	</ul>
</span>