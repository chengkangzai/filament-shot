<div class="space-y-6 p-4">
    <div class="rounded-xl p-6 shadow-sm"
         style="background-color: {{ $tier['color'] }}15; border-left: 4px solid {{ $tier['color'] }}">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Current Tier</p>
                <h3 class="text-2xl font-bold" style="color: {{ $tier['color'] }}">{{ $tier['name'] }}</h3>
            </div>
            <div class="flex gap-6 text-right">
                <div>
                    <p class="text-sm font-medium text-gray-500">Tier Points</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($tierPoints) }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Redeemable Points</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($redeemablePoints) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
