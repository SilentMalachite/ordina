@props(['action', 'method' => 'POST'])

<form x-data="{ submitting: false }" 
      @submit="submitting = true" 
      action="{{ $action }}" 
      method="{{ $method === 'GET' ? 'GET' : 'POST' }}"
      {{ $attributes->merge(['class' => '']) }}>
    
    @if ($method !== 'GET' && $method !== 'POST')
        @method($method)
    @endif
    
    @if ($method !== 'GET')
        @csrf
    @endif
    
    {{ $slot }}
</form>