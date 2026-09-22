{{-- Toast Notification Component --}}
<div id="toast-container" class="fixed top-20 right-4 z-[9999] flex flex-col gap-2 max-w-sm w-full pointer-events-none">
    {{-- Toasts will be injected here via JS --}}
</div>

<script>
window.showToast = function(message, type = 'info', duration = 3000) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    const id = 'toast-' + Date.now();
    toast.id = id;
    toast.className = 'pointer-events-auto transform transition-all duration-300 ease-out translate-x-full opacity-0';

    const icons = {
        success: '<span class="material-symbols-outlined text-emerald-500">check_circle</span>',
        error: '<span class="material-symbols-outlined text-rose-500">error</span>',
        warning: '<span class="material-symbols-outlined text-amber-500">warning</span>',
        info: '<span class="material-symbols-outlined text-sky-500">info</span>',
    };

    const colors = {
        success: 'bg-emerald-50 border-emerald-200',
        error: 'bg-rose-50 border-rose-200',
        warning: 'bg-amber-50 border-amber-200',
        info: 'bg-sky-50 border-sky-200',
    };

    toast.innerHTML = `
        <div class="flex items-start gap-3 p-4 rounded-xl border ${colors[type] || colors.info} bg-white/95 backdrop-blur-sm shadow-lg">
            ${icons[type] || icons.info}
            <p class="text-sm text-slate-700 font-medium flex-1">${message}</p>
            <button onclick="window.closeToast('${id}')" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
    `;

    container.appendChild(toast);

    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full', 'opacity-0');
    }, 10);

    // Auto remove
    setTimeout(() => {
        window.closeToast(id);
    }, duration);
};

window.closeToast = function(id) {
    const toast = document.getElementById(id);
    if (!toast) return;

    toast.classList.add('translate-x-full', 'opacity-0');
    setTimeout(() => toast.remove(), 300);
};
</script>
