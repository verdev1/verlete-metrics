<p>Hi {{ $client->recipient_names }},</p>

<p>
    Here are the key metrics for {{ $client->name }}
    in {{ $start_date->format('F Y') }}.
</p>

<p>
    <strong>Audience</strong><br>
    Active Users: {{ number_format($analytics['active_users']) }}<br>
    New Users: {{ number_format($analytics['new_users']) }}<br>
    Page Views: {{ number_format($analytics['page_views']) }}
</p>

<p>
    <strong>Leads</strong><br>
    @forelse ($forms as $form)
        {{ $form['form_name'] }}:
        {{ number_format($form['submission_count']) }}<br>
    @empty
        No forms found.
    @endforelse
</p>

@if ($store)
    <p>
        <strong>Store Sales</strong><br>
        Orders: {{ number_format($store['orders']) }}<br>
        Total Revenue:
        ${{ number_format($store['total_revenue'], 2) }}<br>
        Average Order Value:
        ${{ number_format($store['average_order_value'], 2) }}
    </p>
@endif

<p>
    <strong>Key Links</strong><br>
    1.
    <a href="https://analytics.google.com/">
        Traffic Statistics
    </a><br>

    2.
    <a href="{{ rtrim($client->website, '/') }}/wp-admin/admin.php?page=fluent_forms_all_entries">
        View Leads
    </a>

    @if ($client->store === 'woocommerce')
        <br>
        3.
        <a href="{{ rtrim($client->website, '/') }}/wp-admin/admin.php?page=wc-orders">
            Shop Orders
        </a><br>

        4.
        <a href="{{ rtrim($client->website, '/') }}/wp-admin/admin.php?page=wc-admin&amp;path=%2Fanalytics%2Foverview">
            Shop Analytics
        </a>
    @endif
</p>

<p>
    Please let me know if you have any questions, requests or suggestions
    for improvement.
</p>

<p>Have a great month!</p>

<p><strong>Verlete Sports</strong></p>