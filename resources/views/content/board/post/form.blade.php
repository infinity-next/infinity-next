@if ($board->canPost($user, $reply_to))
@if (isset($post))
{!! Form::model($post, [
	'url'    => Request::url(),
	'method' => "PATCH",
	'files'  => true,
	'id'     => "mod-form",
	'class'  => "form-mod smooth-box",
]) !!}
@else
{!! Form::open([
	'url'    => url($board->board_uri . '/thread/' . ($reply_to ? $reply_to->board_id: "")),
	'files'  => true,
	'method' => "PUT",
	'id'     => "post-form",
	'class'  => "form-post",
	'data-widget' => "postbox",
]) !!}
@endif
	@if (!isset($post))
	<ul class="post-menu">
		@if ($user->canUsePassword($board))
		<li class="menu-input menu-password">
			{!! Form::password(
				'password',
				[
					'id'          => "password",
					'class'       => "field-control",
					'maxlength'   => 255,
					'placeholder' => trans('board.field.password'),
			]) !!}
		</li>
		@endif
		<li class="menu-icon menu-icon-minimize">
			<span class="menu-icon-button"></span>
			<span class="menu-icon-text">Minimize</span>
		</li>
		<li class="menu-icon menu-icon-maximize">
			<span class="menu-icon-button"></span>
			<span class="menu-icon-text">Expand</span>
		</li>
		<li class="menu-icon menu-icon-close">
			<span class="menu-icon-button"></span>
			<span class="menu-icon-text">Close</span>
		</li>
	</ul>
	@endif

	<fieldset class="form-fields">
		<legend class="form-legend"><i class="fa fa-reply"></i>{{ trans("board.legend." . implode($actions, "+")) }}</legend>

		@include('widgets.messages')

		@if ($board->canPostWithAuthor($user, !!$reply_to))
		<div class="field row-author">
			{!! Form::text(
				'author',
				isset($post) ? $post->author : old('author'),
				[
					'id'          => "author",
					'class'       => "field-control",
					'maxlength'   => 255,
					'placeholder' => trans('board.field.author')
			]) !!}
		</div>
		@endif

		@if ($board->canPostWithSubject($user, !!$reply_to))
		<div class="field row-subject">
			{!! Form::text(
				'subject',
				old('subject'),
				[
					'id'          => "subject",
					'class'       => "field-control",
					'maxlength'   => 255,
					'placeholder' => trans('board.field.subject'),
			]) !!}
		</div>
		@endif

		@if (isset($post) && $post->capcode_id)
		<div class="field row-capcode">
			<span>{{ "## {$post->capcode->getDisplayName()}" }}</span>
		</div>
		@endif

		@if ($board->hasFlags())
		<div class="field row-submit row-double">
			<select id="flag" class="field-control field-flag" name="flag_id">
				<option value="" selected>@lang('board.field.flag')</option>

				@foreach ($board->getFlags() as $flag)
					<option value="{!! $flag->board_asset_id !!}">{{{ $flag->asset_name }}}</option>
				@endforeach
			</select>
		@else
		<div class="field row-submit">
		@endif
			{!! Form::text(
				'email',
				old('email'),
				[
					'id'          => "email",
					'class'       => "field-control",
					'maxlength'   => 254,
					'placeholder' => trans('board.field.email'),
			]) !!}
		</div>

		<div class="field row-post">
			{!! Form::textarea(
				'body',
				old('body'),
				[
					'id'           => "body",
					'class'        => "field-control",
					'autocomplete' => "off",
			]) !!}
		</div>

		@if ($board->canAttach($user) && !isset($post))
		<div class="field row-file">
			<div class="dz-container">
				<span class="dz-instructions"><span class="dz-instructions-text"><i class="fa fa-upload"></i>&nbsp;@lang('board.field.file-dz')</span></span>
				<div class="fallback">
					<input class="field-control" id="file" name="files[]" type="file" multiple />
					<div class="field-control">
						<label class="dz-spoiler"><input name="spoilers" type="checkbox" value="1" />&nbsp;@lang('board.field.spoilers')</label>
					</div>
				</div>
			</div>
		</div>
		@endif

		<div class="field row-captcha" style="display:@if ($board->canPostWithoutCaptcha($user)) none @else block @endif;">
			<label class="field-label" for="captcha" data-widget="captcha">
				@if (!$board->canPostWithoutCaptcha($user))
					{!! captcha() !!}
				@else
					<img src="" class="captcha">
					<input type="hidden" name="captcha_hash" value="" />
				@endif
			</label>

			{!! Form::text(
				'captcha',
				"",
				[
					'id'           => "captcha",
					'class'        => "field-control",
					'placeholder'  => "Security Code",
					'autocomplete' => "off",
			]) !!}
		</div>

		@if (!$user->isAnonymous() && !isset($post) && $user->getCapcodes($board))
		<div class="field row-submit row-double">
			<select id="capcode" class="field-control field-capcode" name="capcode">
				<option value="" selected>@lang('board.field.capcode')</option>

				@foreach ($user->getCapcodes($board) as $role)
					<option value="{!! $role->role_id !!}">{{{ $role->getCapcodeName() }}}</option>
				@endforeach
			</select>
		@else
		<div class="field row-submit">
		@endif

			{!! Form::button(
				trans("board.submit." . implode($actions, "+")),
				[
					'type'      => "submit",
					'id'        => "submit-post",
					'class'     => "field-submit",
			]) !!}
		</div>
	</fieldset>

@if (!isset($form) || $form)
</form>
@endif
@endif
