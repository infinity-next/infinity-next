<!-- Board banned -->
{!! Form::open([
    'url'    => Request::url(),
    'method' => "PUT",
    'files'  => true,
    'id'     => "banned-upload",
    'class'  => "form-config",
]) !!}
    <fieldset class="form-fields group-new_board_banned">
        <legend class="form-legend">{{ trans("config.legend.board_banned") }}</legend>

        <dl class="option option-new_board_banned">
            <dt class="option-term">
                {!! Form::label(
                    "new_board_banned",
                    trans("config.option.boardAssetbannedUpload"),
                    [
                        'class' => "field-label",
                ]) !!}
            </dt>
            <dd class="option-definition">
                <input class="field-control" id="new_board_banned" name="new_board_banned" type="file" />
            </dd>
        </dl>
    </fieldset>

    {!! Form::hidden('asset_type', 'board_banned') !!}

    <div class="field row-submit">
        {!! Form::button(
            trans("config.upload"),
            [
                'type'      => "submit",
                'class'     => "field-submit",
        ]) !!}
    </div>
{!! Form::close() !!}

@if (count($banned))
{!! Form::open([
    'url'    => Request::url(),
    'method' => "PATCH",
    'files'  => true,
    'id'     => "banned-board",
    'class'  => "form-config grip-100",
]) !!}
    <fieldset class="form-fields group-board_banned">
        @foreach ($banned as $banned)
        <dl class="option option-asset">
            <dt class="option-term"></dt>
            <dd class="option-definition">
                <label for="banned_{{ $banned->board_asset_id }}" class="field-label">
                    {!! Form::checkbox(
                        "asset[{$banned->board_asset_id}]",
                        1,
                        true,
                        [
                            'id'    => "banned_{$banned->board_asset_id}",
                            'class' => "field-control",
                    ]) !!}

                    {!! $banned->toHtml() !!}
                </label>
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
                'value'     => "board_banned",
                'class'     => "field-submit",
        ]) !!}
    </div>
{!! Form::close() !!}
@endif
