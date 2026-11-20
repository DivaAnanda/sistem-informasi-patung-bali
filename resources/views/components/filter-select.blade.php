@props(['name', 'label', 'placeholder', 'options', 'selected' => null])

<label class="flex min-w-0 flex-1 flex-col gap-1.5">
    <span class="text-xs font-semibold tracking-wide text-stone-600 uppercase">{{ $label }}</span>
    <select
        name="{{ $name }}"
        class="h-11 w-full rounded-xl border border-stone-300 bg-white px-3 text-sm focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 focus:outline-none"
    >
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $option)
            <option
                value="{{ $option['value'] }}"
                @selected($selected === $option['value'])
                @disabled($option['count'] === 0 && $selected !== $option['value'])
            >{{ $option['label'] }} ({{ $option['count'] }})</option>
        @endforeach
    </select>
</label>
