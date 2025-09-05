{{-- Redirect Unauthenticated Users Setting --}}
<div class="form-group">
    <label for="redirect_unauthenticated_users" class="col-sm-2 control-label">
        {{ __('Redirect Unauthenticated Users') }}
    </label>
    <div class="col-sm-10">
        <div class="controls">
            <div class="onoffswitch-wrap">
                <div class="onoffswitch">
                    <input type="checkbox" 
                           name="redirect_unauthenticated_users" 
                           id="redirect_unauthenticated_users" 
                           class="onoffswitch-checkbox"
                           @if($redirectEnabled) checked @endif
                    >
                    <label class="onoffswitch-label" for="redirect_unauthenticated_users"></label>
                </div>
            </div>
        </div>
        <p class="help-block">
            {{ __('When enabled, unauthenticated users visiting the portal will be automatically redirected to the login page.') }}
        </p>
    </div>
</div>