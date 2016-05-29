<dl class="option option-permission">
    <dt class="option-term">@lang('config.permission.master.help.quickcheck')</dt>
    <dd class="option-definition">
        <label class="option-permission option-permission-allow option-master" id="permission-master-allow" title="@lang('config.permission.master.help.allow')">
            @lang('config.permission.master.allow')
        </label>
        <label class="option-permission option-permission-unset option-master" id="permission-master-inherit" title="@lang('config.permission.master.help.inherit')">
            @lang('config.permission.master.inherit')
        </label>
        <label class="option-permission option-permission-revoke option-master" id="permission-master-revoke" title="@lang('config.permission.master.help.revoke')">
            @lang('config.permission.master.revoke')
        </label>
        {{-- <label class="option-permission option-permission-deny option-master" id="permission-master-deny" title="@lang('config.permission.master.help.deny')">
            @lang('config.permission.master.deny')
        </label> --}}
    </dd>
</dl>

@foreach ($groups as $group)
    @if (count($group->permissions))
    @include('widgets.config.permissions',[
        'permissions' => $group->permissions,
    ])
    @endif
@endforeach
