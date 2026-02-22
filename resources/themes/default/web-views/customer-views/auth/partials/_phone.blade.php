<div class="form-group m-0">
    <label class="form-label font-semibold">
        {{ translate('phone_number') }}
        <span class="input-required-icon">*</span>
    </label>
    <input name="phone" class="form-control text-align-direction"
           type="tel" value="{{ old('phone') }}"
           placeholder="Enter Phone Number without Country Code">
    <span>Start From 01...</span>
</div>
