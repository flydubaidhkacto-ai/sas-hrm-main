<form
    action="{{ $_action ?? route('settings.attendance.update') }}"
    class="d-inline-block"
    method="{{ $_method ?? 'POST' }}">
    @csrf
    <input type="hidden" name="key" value="{{ $name }}">
    <input type="hidden" name="value" value="0">

    <label class="switch">
        <input type="checkbox" name="value" value="1" onchange="this.form.submit()"
            {{ $enabled ? 'checked' : '' }}>
        <span class="slider round"></span>
    </label>
</form>
