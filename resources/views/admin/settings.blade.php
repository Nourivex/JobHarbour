<x-layouts.admin title="Akun saya - Admin Portal JobHarbour" active="settings" crumb="Akun saya">
    @include('partials.account-settings')

    @push('scripts')
        @vite('resources/js/pages/recruiter/settings.js')
    @endpush
</x-layouts.admin>
