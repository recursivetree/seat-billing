<?php

return [

    'tax_state_open' => 'Open',
    'tax_state_pending'=>'Pending',
    'tax_state_completed'=>'Completed',
    'tax_state_prediction'=>'Prediction',
    'tax_state_overtaxed' => 'Overpaid',
    'user_tax_invoices'=>'Tax Invoices',
    'no_user_invoice_history'=>'You don\'t seem to have any open tax invoices.',
    'tax_to_corporation_title'=>'Tax owed to :corporation',
    'character'=>'Character',
    'receiver_corporation'=>'Receiver Corporation',
    'tax_reason'=>'Reason',
    'remaining_tax'=>'Remaining',
    'tax_state'=>'Payment State',
    'tax_states'=>'Payment States',
    'tax_code'=>'Tax Code',
    'tax_no_matching_invoice'=>'You paid :tax ISK as tax to \':corp\' using the code \':code\', but there are no open tax invoices that could be covered.',
    'too_much_tax_paid'=>'You have paid :tax ISK too much to \':corp\' while paying taxes using the code \':code\'.',
    'overpayment_balancing_remainder'=>'After balancing overpayments to :corp, you still have unused ISK remaining.',
    'balance_overpayments'=>'Balance Overpayments',
    'overpayment_balancing_scheduled'=>'Tax Overpayment Balancing scheduled. Please wait a moment until it\'s processed.',
    'corporation_tax_overview'=>':corp Tax Overview',
    'corporation_tax_overview_selection'=>'Corporation Selection',
    'corporation'=>'Corporation',
    'no_corporation_with_tax_invoices'=>'It looks like there are no corporations having characters with tax invoices.',
    'view_tax_details'=>'View Tax Details',
    'actions'=>'Actions',
    'total_invoices'=>'Total Invoices',
    'total_invoices_desc'=>'In all it\'s history, this many invoices have been created',
    'open_invoices'=>'Open Invoices',
    'open_invoices_desc'=>'Lists how many invoices are unpaid',
    'completed_invoices'=>'Completed Invoices',
    'completed_invoices_desc'=>'Lists how many invoices are paid',
    'pending_tax'=>'Open ISK',
    'pending_tax_desc'=>"This much ISK is waiting to be paid in by users",
    'corporation_tax_user_totals'=>'User Tax Details',
    'user'=>'User',
    'tax_user_total_amount'=>'Total Tax',
    'tax_user_open_amount'=>'Open Tax',
    'no_corporation_user_overview_data'=>'There is no tax data available for your users.',
    'due_until'=>'Due Until',
    'created_at'=>'Created At',
    'tax_user_overdue_amount'=>'Overdue Tax',
    'tax_user_overdue_amount_desc'=>'This much ISK remains unpaid after the deadline',
    'instructions'=>'Instructions',
    'tax_instructions' => 'On this page you have an overview of the tax you have to pay. There is a table for each corporation you owe taxes to, containing how much isk you have to pay and the payment status. You can pay your taxes by transferring the ISK listed under "remaining" to the corporation. Make sure to include the tax code in the description of the payment, or the payment can\'t be automatically detected. After you initiate the payment, it can take up to an hour until the payment status changes. This is due to how ESI work, please have some patience.',
    'regenerate_tax_invoices' => 'Regenerate Tax Invoices',
    'month'=>'Month',
    'regenerate_tax_invoices_desc'=>'Already paid isk won\'t be lost.',
    'tax_state_open_desc'=>'You should proceed to pay the invoice until the specified date.',
    'tax_state_pending_desc'=>'The invoice has been partially paid. Please pay the remaining amount until the specified date.',
    'tax_state_completed_desc'=>'The invoice has been fully paid. You don\'t have to do anything.',
    'tax_state_prediction_desc'=>'This is the predicted tax you will have to pay at the end of the month. It is <b>not</b> possible to pay predictions until the next month starts.',
    'tax_state_overtaxed_desc'=>'It seems like you have transferred a little bit too much ISK to your corp. This is not an issue, you can use the surplus to pay the taxes next month. When there is a new open invoices, use the <i>:button</i> button to balance the payments out.',
];
