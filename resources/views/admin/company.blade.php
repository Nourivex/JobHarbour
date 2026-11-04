<x-layouts.admin title="Detail Perusahaan - Admin Portal JobHarbour" active="companies" crumb="Detail perusahaan">
    <a href="/admin/companies" class="ws-btn ws-btn-ghost ws-btn-sm -ml-2 mb-3"><i data-lucide="arrow-left"></i>Semua perusahaan</a>

    <div id="cd-root" data-company-id="{{ $companyId }}" aria-live="polite"></div>

    @push('scripts')
        @vite('resources/js/pages/admin/company.js')
    @endpush
</x-layouts.admin>
