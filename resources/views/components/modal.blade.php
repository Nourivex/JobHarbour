@props([
    'id',
    'title' => '',
    'maxWidth' => 'max-w-lg',
])

<div
    id="{{ $id }}"
    class="fixed inset-0 z-50 hidden overflow-y-auto"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}-title"
>
    <!-- Backdrop -->
    <div
        class="fixed inset-0 bg-[#0C2238]/60 backdrop-blur-xs transition-opacity duration-200 jh-modal-backdrop"
        onclick="window.JHModal && window.JHModal.close('{{ $id }}')"
    ></div>

    <!-- Modal Dialog -->
    <div class="min-h-full flex items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl w-full {{ $maxWidth }} shadow-2xl border border-[#E2E7ED] transform transition-all duration-200 my-8 p-6 text-left">
            <!-- Header -->
            <div class="flex items-center justify-between pb-4 border-b border-[#E2E7ED]">
                <h3 id="{{ $id }}-title" class="text-lg font-bold text-[#0B1B2B] leading-tight">
                    {{ $title }}
                </h3>
                <button
                    type="button"
                    class="text-[#627080] hover:text-[#0B1B2B] p-1.5 rounded-lg hover:bg-[#F4F6F8] transition-colors cursor-pointer"
                    aria-label="Tutup jendela dialog"
                    onclick="window.JHModal && window.JHModal.close('{{ $id }}')"
                >
                    <x-icon name="x" class="w-5 h-5" />
                </button>
            </div>

            <!-- Body -->
            <div class="py-4">
                {{ $slot }}
            </div>

            @isset($footer)
                <!-- Footer -->
                <div class="pt-4 border-t border-[#E2E7ED] flex items-center justify-end gap-3">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>

<script>
window.JHModal = window.JHModal || {
    open(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        const firstFocus = el.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (firstFocus) firstFocus.focus();
    },
    close(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
};

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const openModals = document.querySelectorAll('[role="dialog"]:not(.hidden)');
        openModals.forEach((m) => {
            if (m.id && window.JHModal) window.JHModal.close(m.id);
        });
    }
});
</script>
