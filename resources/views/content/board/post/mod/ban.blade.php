
@can('delete', $post)
<fieldset class="form-fields">
    <legend class="form-legend">@lang('board.legend.post_retention')</legend>

    <div class="field-buttons">
        <label class="field-button">
            <input name="delete" value="0" type="radio" checked="checked" />
            @lang('board.action.keep')
        </label>

        <label class="field-button">
            <input name="delete" value="1" type="radio" />
            @lang('board.action.delete')
        </label>

        @if ($post->hasAuthorIp())
        <label class="field-button">
            <input name="delete" value="2" type="radio" />
            @lang('board.action.delete_all')
        </label>
        @endif
    </div>
</fieldset>
@endcan

@if ($post->hasAuthorIp())
@can('ban', $board)
<fieldset class="form-fields">
    <legend class="form-legend">@lang('board.legend.ban')</legend>

    <div class="fields-toggleable">
        <label class="field-button">
            <input name="ban" value="1" type="checkbox" />
            @lang('board.action.ban')
        </label>

        <div class="fields-toggled">
            @can('ip-address')
            {!! Form::hidden('raw_ip', 1) !!}
            <div class="field row-ip label-inline row-inline">
                {!! Form::text('ban_ip', $post->getAuthorIpAsString(), [
                    'id'        => "ban_ip",
                    'class'     => "field-control",
                    'maxlength' => 255,
                ]) !!}
                {!! Form::label("ban_ip", trans('board.field.ip'), [ 'class' => "field-label", ]) !!}
            </div>
            @else
            {!! Form::hidden('raw_ip', 0) !!}
            <div class="field row-ipless label-inline row-inline">
                {!! Form::text('ban_ip', ip_less($post->author_ip), [
                    'id'        => "ban_ip",
                    'class'     => "field-control",
                    'maxlength' => 255,
                    'disabled'  => "disabled",
                ]) !!}
                {!! Form::label("ban_ip", trans('board.field.ip'), [ 'class' => "field-label", ]) !!}
            </div>
            @endcan

            <div class="field row-iplessrange label-inline row-inline">
                {!! Form::select(
                    'ban_ip_range',
                    $post->getAuthorIpRangeOptions(),
                    $post->getAuthorIpBitSize(),
                    [
                        'id'        => "ban_ip_range",
                        'class'     => "field-control",
                ]) !!}
                {!! Form::label(
                    "ban_ip_range",
                    trans('board.field.ip_range'),
                    [
                        'class' => "field-label",
                ]) !!}
            </div>

            <div class="field row-ip label-inline">
                {!! Form::text(
                    'justification',
                    "",
                    [
                        'id'        => "justification",
                        'class'     => "field-control",
                        'maxlength' => 255,
                ]) !!}
                {!! Form::label(
                    "justification",
                    trans('board.field.justification'),
                    [
                        'class' => "field-label",
                ]) !!}
            </div>

            @if ($banMaxLength > 0)
            <div class="field row-inline row-expires row-expires-days">
                <span class="field-label">@lang('board.field.expires')</span>
                {!! Form::selectRange('expires_days', 0, $banMaxLength, 0, [ 'class' => "field-control",]) !!}
                @lang('board.field.expires-days')
            </div>
            @else
                {!! Form::hidden('expires_days', 0) !!}
            @endif

            <div class="field row-inline row-expires row-expires-hours">
                <span class="field-label"></span>
                {!! Form::selectRange('expires_hours', 0, 23, 0, [ 'class' => "field-control", ]) !!}
                @lang('board.field.expires-hours')
            </div>

            <div class="field row-inline row-expires row-expires-minutes">
                <span class="field-label"></span>
                {!! Form::selectRange('expires_minutes', 0, 59, 0, [ 'class' => "field-control", ]) !!}
                @lang('board.field.expires-minutes')
            </div>
        </div>
    </div>
</fieldset>
@endcan

<fieldset class="form-fields fields-togglable">
    <legend class="form-legend">@lang('board.legend.scope')</legend>

    <div class="field-buttons">
        @canAny('global-delete', 'global-ban')
        <label class="field-button">
            <input type="radio" name="scope" value="_global" />
            <strong>@lang('board.action.global')</strong>
        </label>
        @endcan

        {{-- @foreach ($boardsWithRights as $board) --}}
        <label class="field-button">
            <input type="radio" name="scope" value="{{ $board->board_uri }}" checked="checked" />
            <img class="crown-image" src="{{ $board->getIconUrl() }}" />
            /{{ $board->board_uri }}/
        </label>
        {{-- @endforeach --}}
    </div>
</fieldset>
@else
    <p>@lang('board.ban.no_ip')</p>
@endif

<fieldset class="form-fields">
    <div class="field row-submit">
        {!! Form::button(
            trans('board.submit.confirm'),
            [
                'type'      => "submit",
                'class'     => "field-delete",
        ]) !!}
    </div>
</fieldset>
