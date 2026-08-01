Hi {{ $client->recipient_names }}

Here are the key metrics for {{ $client->name }} in {{ $start_date->format('F Y') }}.

Audience
Active Users: {{ number_format($analytics['active_users']) }}
New Users: {{ number_format($analytics['new_users']) }}
Page Views: {{ number_format($analytics['page_views']) }}

Leads
@forelse ($forms as $form)
{{ $form['form_name'] }}: {{ number_format($form['submission_count']) }}
@empty
No forms found.
@endforelse

@if ($store)
Store Sales
Orders: {{ number_format($store['orders']) }}
Total Revenue: ${{ number_format($store['total_revenue'], 2) }}
Average Order Value: ${{ number_format($store['average_order_value'], 2) }}

@endif
Key Links
1. Traffic Statistics: https://analytics.google.com/
2. View Leads: {{ rtrim($client->website, '/') }}/wp-admin/admin.php?page=fluent_forms_all_entries
@if ($client->store === 'woocommerce')
3. Shop Orders: {{ rtrim($client->website, '/') }}/wp-admin/admin.php?page=wc-orders
4. Shop Analytics: {{ rtrim($client->website, '/') }}/wp-admin/admin.php?page=wc-admin&path=%2Fanalytics%2Foverview
@endif

Please let me know if you have any questions, requests or suggestions for improvement.

Have a great month!