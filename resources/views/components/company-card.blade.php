@props(['company', 'title' => 'Company Details'])

@if($company)
    <div class="bg-gray-50 border rounded-lg p-6 mt-4 text-gray-800">
        <h3 class="text-lg font-semibold mb-4 text-gray-900">{{ $title }}</h3>

        <div class="grid grid-cols-2 gap-y-2 gap-x-6 text-sm">
            <p><span class="font-semibold">Name:</span> {{ $company['name'] ?? $company->name ?? '-' }}</p>
             <p><span class="font-semibold">Reg. Nr:</span> {{ $company['reg_nr'] ?? $company->reg_nr ?? '-' }}</p>
            <p><span class="font-semibold">Email:</span> {{ $company['email'] ?? $company->email ?? '-' }}</p>
            <p><span class="font-semibold">Phone:</span> {{ $company['phone'] ?? $company->phone ?? '-' }}</p>
            <p><span class="font-semibold">Post Code:</span> {{ $company['post_code'] ?? $company->jur_post_code ?? '-' }}</p>
            <p class="col-span-2">
                <span class="font-semibold">Address:</span>
                {{ $company['address'] ?? $company->jur_address ?? '-' }},
                {{ $company['city'] ?? $company->jur_city ?? '' }},
                {{ $company['country'] ?? $company->jur_country ?? '' }}
            </p>
        </div>
    </div>
@endif
