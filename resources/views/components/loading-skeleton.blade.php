{{-- Loading Skeleton Component --}}
@props(['type' => 'card'])

@if($type === 'card')
<div class="rainova-card p-6 animate-pulse">
    <div class="h-4 bg-slate-200 rounded w-1/3 mb-4"></div>
    <div class="space-y-3">
        <div class="h-3 bg-slate-200 rounded"></div>
        <div class="h-3 bg-slate-200 rounded w-5/6"></div>
        <div class="h-3 bg-slate-200 rounded w-4/6"></div>
    </div>
</div>
@elseif($type === 'stat')
<div class="rainova-card p-5 animate-pulse">
    <div class="w-10 h-10 bg-slate-200 rounded-2xl mb-3"></div>
    <div class="h-3 bg-slate-200 rounded w-1/2 mb-2"></div>
    <div class="h-6 bg-slate-200 rounded w-2/3"></div>
</div>
@elseif($type === 'gauge')
<div class="rainova-card p-6 animate-pulse flex flex-col items-center">
    <div class="h-3 bg-slate-200 rounded w-1/2 mb-4"></div>
    <div class="w-40 h-40 bg-slate-200 rounded-full mb-4"></div>
    <div class="h-3 bg-slate-200 rounded w-2/3"></div>
</div>
@endif

<style>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
