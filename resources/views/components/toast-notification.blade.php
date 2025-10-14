<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
    class="text-sm bg-{{ $type === 'error' ? 'red' : ($type === 'success' ? 'green' : 'blue') }}-600 text-white px-4 py-3 mb-3 ml-3 rounded-md shadow-lg"
    role="alert">
    <strong class="font-bold">{{ ucfirst($type) }}:</strong>
    <span class="block sm:inline">{{ $message }}</span>
    <span
        class="absolute top-0 bottom-0 right-0 px-4 py-3 text-white/[.5] hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-{{ $type === 'error' ? 'red' : ($type === 'success' ? 'green' : 'blue') }}-800 focus:ring-{{ $type === 'error' ? 'red' : ($type === 'success' ? 'green' : 'blue') }}-500 transition-all text-sm dark:focus:ring-offset-{{ $type === 'error' ? 'red' : ($type === 'success' ? 'green' : 'blue') }}-500 dark:focus:ring-{{ $type === 'error' ? 'red' : ($type === 'success' ? 'green' : 'blue') }}-700"
        @click="show = false">X
    </span>
</div>
{{-- fonte: https://www.creative-tim.com/twcomponents/component/tailwind-css-toasts-solid-color --}}
