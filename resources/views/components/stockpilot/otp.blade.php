@props([
    'name' => 'code',
    'length' => 6,
    'label' => 'OTP Code',
])

<div
    x-data="{
        value: @js(old($name, '')),
        length: {{ $length }},

        normalize() {
            this.value = this.value.replace(/\D/g, '').slice(0, this.length);
        },

        focus() {
            this.$refs.input?.focus();
        }
    }"
    x-init="$nextTick(() => focus())"
    class="grid justify-items-center gap-3"
>
    <label
        for="{{ $name }}"
        class="sr-only"
    >
        {{ $label }}
    </label>

    <input
        x-ref="input"
        id="{{ $name }}"
        name="{{ $name }}"
        type="text"
        inputmode="numeric"
        pattern="[0-9]*"
        maxlength="{{ $length }}"
        autocomplete="one-time-code"
        x-model="value"
        x-on:input="normalize()"
        {{ $attributes->merge([
            'class' => 'block w-full max-w-xs rounded-xl border border-sp-border bg-sp-surface px-4 py-4 text-center text-2xl font-semibold tracking-[0.45em] text-sp-text shadow-sm outline-none transition placeholder:text-sp-text-subtle focus:border-sp-primary focus:ring-2 focus:ring-sp-primary/20 dark:bg-sp-surface dark:text-sp-text',
        ]) }}
    />

    <p class="text-xs text-sp-text-subtle">
        {{ $length }}-digit authentication code
    </p>

    @error($name)
        <p class="text-sm text-sp-danger" role="alert">
            {{ $message }}
        </p>
    @enderror
</div>
