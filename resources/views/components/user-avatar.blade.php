@props([
    'user' => null,
    'size' => 'w-10 h-10',
    'textSize' => 'text-sm',
])
@php($avatarUrl = $user?->avatarUrl())
<div {{ $attributes->merge(['class' => "$size rounded-full shrink-0 overflow-hidden flex items-center justify-center bg-gradient-to-tr from-[#10B981] to-[#047857] text-white font-bold $textSize"]) }}>
  @if($avatarUrl)
    <img loading="lazy" decoding="async" src="{{ $avatarUrl }}" alt="Foto de perfil de {{ $user->name }}" class="w-full h-full object-cover" onerror="this.remove();this.nextElementSibling.classList.remove('hidden')">
  @endif
  <span class="{{ $avatarUrl ? 'hidden' : '' }}">{{ strtoupper(substr($user?->name ?? '?', 0, 1)) }}</span>
</div>
