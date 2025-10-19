<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
    class=" bg-{{ $type === 'error' ? 'red' : ($type === 'success' ? 'green' : 'blue') }}-100 border border-{{ $type === 'error' ? 'red' : ($type === 'success' ? 'green' : 'blue') }}-400 text-{{ $type === 'error' ? 'red' : ($type === 'success' ? 'green' : 'blue') }}-700 px-4 py-3 rounded shadow-md z-50"
    role="alert">
    <strong class="font-bold">{{ ucfirst($type) }}:</strong>
    <span class="block sm:inline">{{ $message }}</span>
    <span class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="show = false">
        <svg class="fill-current h-6 w-6 text-{{ $type === 'error' ? 'red' : ($type === 'success' ? 'green' : 'blue') }}-500"
            role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <title>Close</title>
            <path
                d="M14.348 5.652a1 1 0 10-1.414-1.414L10 7.172 7.066 4.238a1 1 0 10-1.414 1.414L8.586 10l-2.934 2.934a1 1 0 101.414 1.414L10 12.828l2.934 2.934a1 1 0 001.414-1.414L11.414 10l2.934-2.934z" />
        </svg>
    </span>
</div>
{{-- <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 7000)"
    class="fixed top-10 right-10 bg-{{ $type === 'error' ? 'red' : ($type === 'success' ? 'green' : 'blue') }}-100 border border-{{ $type === 'error' ? 'red' : ($type === 'success' ? 'green' : 'blue') }}-400 text-{{ $type === 'error' ? 'red' : ($type === 'success' ? 'green' : 'blue') }}-700 px-4 py-3 rounded shadow-md z-50"
    role="alert">
    <strong class="font-bold">{{ ucfirst($type) }}:</strong>
    <span class="block sm:inline">{{ $message }}</span>
    <span class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="show = false">
        <svg class="fill-current h-6 w-6 text-{{ $type === 'error' ? 'red' : ($type === 'success' ? 'green' : 'blue') }}-500"
            role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <title>Close</title>
            <path
                d="M14.348 5.652a1 1 0 10-1.414-1.414L10 7.172 7.066 4.238a1 1 0 10-1.414 1.414L8.586 10l-2.934 2.934a1 1 0 101.414 1.414L10 12.828l2.934 2.934a1 1 0 001.414-1.414L11.414 10l2.934-2.934z" />
        </svg>
    </span>
</div> --}}
