<div class="fi-fo-key-value" style="border: 1px solid var(--gray-200); border-radius: 0.75rem; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="padding: 0.5rem 0.75rem; text-align: start; font-size: 0.75rem; font-weight: 500; color: var(--gray-500); border-bottom: 1px solid var(--gray-200);">Key</th>
                <th style="padding: 0.5rem 0.75rem; text-align: start; font-size: 0.75rem; font-weight: 500; color: var(--gray-500); border-bottom: 1px solid var(--gray-200); border-inline-start: 1px solid var(--gray-200);">Value</th>
            </tr>
        </thead>
        <tbody>
            @forelse($field['pairs'] ?? [] as $key => $value)
                <tr>
                    <td style="padding: 0.375rem 0.75rem; border-bottom: 1px solid var(--gray-200);">
                        <input type="text" value="{{ $key }}" class="fi-input" style="border: none; outline: none; width: 100%; padding: 0; box-shadow: none;" />
                    </td>
                    <td style="padding: 0.375rem 0.75rem; border-bottom: 1px solid var(--gray-200); border-inline-start: 1px solid var(--gray-200);">
                        <input type="text" value="{{ $value }}" class="fi-input" style="border: none; outline: none; width: 100%; padding: 0; box-shadow: none;" />
                    </td>
                </tr>
            @empty
                <tr>
                    <td style="padding: 0.375rem 0.75rem;">
                        <input type="text" placeholder="Key" class="fi-input" style="border: none; outline: none; width: 100%; padding: 0; box-shadow: none;" />
                    </td>
                    <td style="padding: 0.375rem 0.75rem; border-inline-start: 1px solid var(--gray-200);">
                        <input type="text" placeholder="Value" class="fi-input" style="border: none; outline: none; width: 100%; padding: 0; box-shadow: none;" />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
