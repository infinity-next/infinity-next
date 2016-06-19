<fieldset class="form-fields">
    <legend class="form-legend">{{ trans("config.legend.page_body") }}</legend>

    <dl class="option option-page_name">
        <dt class="option-term">
            {!! Form::label(
                'name',
                trans("config.option.page_name"),
                [
                    'class' => "field-label",
                ]
            ) !!}
        </dt>
        <dd class="option-definition">
            {!! Form::text(
                'name',
                old('name') ?: ($page ? $page->name : ""),
                [
                    'class'     => "field-control",
                ]
            ) !!}
        </dd>
    </dl>

    <textarea name="body">{{ old('body') ?: ($page ? $page->body : "") }}</textarea>
</fieldset>
