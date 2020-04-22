<!-- Board Flags  -->
{!! Form::open([
    'url'    => Request::url(),
    'method' => "PUT",
    'files'  => true,
    'id'     => "flags-upload",
    'class'  => "form-config",
]) !!}
    <fieldset class="form-fields group-board_flags">
        <legend class="form-legend">{{ trans("config.legend.board_flags") }}</legend>

        <dl class="option option-board_flags">
            <dt class="option-term">
                {!! Form::label(
                    null,
                    trans("config.option.boardAssetFlagUpload"),
                    [
                        'class' => "field-label",
                ]) !!}
            </dt>
            <dd class="option-definition">
                {!! Form::text(
                    "new_board_flags[name][]",
                    "",
                    [
                        'class'     => "field-control",
                        'id'        => "flags-upload-new-name",
                ]) !!}

                <input class="field-control" name="new_board_flags[file][]" id="flags-upload-new-file" type="file" />
            </dd>
        </dl>
    </fieldset>

    {!! Form::hidden('asset_type', 'board_flags') !!}

    <div class="field row-submit">
        {!! Form::button(
            trans("config.upload"),
            [
                'type'      => "submit",
                'class'     => "field-submit",
        ]) !!}
    </div>
{!! Form::close() !!}

@if (count($flags))
{!! Form::open([
    'url'    => Request::url(),
    'method' => "PATCH",
    'files'  => true,
    'id'     => "flags-board",
    'class'  => "form-config grip-100",
]) !!}
    <fieldset class="form-fields group-board_banned">
        @foreach ($flags as $flag)
        <dl class="option option-asset">
            <dt class="option-term"></dt>
            <dd class="option-definition asset-definition">
                <label for="flag_{{ $flag->board_asset_id }}" class="field-label">
                    {!! Form::checkbox(
                        "asset[{$flag->board_asset_id}]",
                        1,
                        true,
                        [
                            'id'    => "flag_{$flag->board_asset_id}",
                            'class' => "field-control asset-enable-input",
                    ]) !!}

                    {!! $flag->toHtml() !!}
                </label>

                <input class="field-control asset-file-input" name="asset_file[{{ $flag->board_asset_id }}]" type="file" />

                {!! Form::text(
                    "asset_name[{$flag->board_asset_id}]",
                    $flag->asset_name,
                    [
                        'id'    => "flag_name_{$flag->board_asset_id}",
                        'class' => "field-control asset-name-input",
                    ]
                ) !!}
            </dd>
        </dl>
        @endforeach
    </fieldset>

    <div class="field row-submit">
        {!! Form::button(
            trans("config.submit"),
            [
                'type'      => "submit",
                'name'      => "patching",
                'value'     => "board_flags",
                'class'     => "field-submit",
        ]) !!}
    </div>
{!! Form::close() !!}
@endif
