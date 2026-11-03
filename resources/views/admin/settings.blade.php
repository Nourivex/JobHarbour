<x-layouts.admin title="Pengaturan - Admin Portal JobHarbour" active="settings" crumb="Pengaturan">
    @include('partials.account-settings')

    @push('scripts')
        @vite('resources/js/pages/recruiter/settings.js')
    @endpush
</x-layouts.admin>
