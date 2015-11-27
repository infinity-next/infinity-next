@if (isset($option) && $user->canViewSettingLock($board, $option))
<label class="field-lock" title="{{ $user->canEditSettingLock($board, $option) ? trans('config.locking') : trans('config.locked') }}">
	(<i class="fa fa-lock"></i>@if ($user->canEditSettingLock($board, $option))&nbsp;{!! Form::checkbox(
		"lock[{$option_name}]",
		1,
		$option->is_locked,
		[
			'id'        => "lock-{$option_name}",
			'class'     => "field-lockbox",
	]) !!}
	@endif)
</label>
@endif