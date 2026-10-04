<?php

namespace App\Http\Controllers;

use App\Services\PropertyManagementReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PropertyManagementReportExportController extends Controller
{
    public function __invoke(
        Request $request,
        string $report,
        PropertyManagementReportService $service,
    ): StreamedResponse {
        abort_unless($request->user()?->can('property_management_reports.export'), 403);

        $from = Carbon::parse($request->query('from', now()->startOfMonth()->toDateString()));
        $to = Carbon::parse($request->query('to', today()->toDateString()));

        abort_if($to->lt($from), 422, 'Invalid report period.');

        [$headers, $rows] = match ($report) {
            'collections' => $this->collections($service, $from, $to),
            'arrears' => $this->arrears($service),
            'expenses' => $this->expenses($service, $from, $to),
            'maintenance' => $this->maintenance($service, $from, $to),
            'owner-settlements' => $this->settlements($service, $from, $to),
            default => abort(404),
        };

        $filename = 'wasal-'.$report.'-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function collections(PropertyManagementReportService $service, Carbon $from, Carbon $to): array
    {
        $rows = $service->collectionsRows($from, $to)->map(fn ($payment): array => [
            $payment->receipt_number,
            $payment->paid_at?->format('Y-m-d H:i'),
            $payment->tenancy?->unit?->property?->internal_code,
            $payment->tenancy?->unit?->code,
            $payment->tenancy?->tenant?->name,
            $payment->amount,
            $payment->currency?->code,
            $payment->payment_method,
            $payment->status,
        ])->all();

        return [[
            'رقم السند','تاريخ الدفع','العقار','الوحدة','المستأجر',
            'المبلغ','العملة','طريقة الدفع','الحالة',
        ], $rows];
    }

    private function arrears(PropertyManagementReportService $service): array
    {
        $rows = $service->arrearsRows()->map(fn ($item): array => [
            $item->tenancy?->contract_number,
            $item->tenancy?->unit?->property?->internal_code,
            $item->tenancy?->unit?->code,
            $item->tenancy?->tenant?->name,
            $item->due_date?->format('Y-m-d'),
            $item->amount,
            $item->paid_amount,
            max(0, (float) $item->amount - (float) $item->paid_amount),
            $item->currency?->code,
            $item->status,
        ])->all();

        return [[
            'العقد','العقار','الوحدة','المستأجر','الاستحقاق',
            'المبلغ','المسدد','المتبقي','العملة','الحالة',
        ], $rows];
    }

    private function expenses(PropertyManagementReportService $service, Carbon $from, Carbon $to): array
    {
        $rows = $service->expensesRows($from, $to)->map(fn ($expense): array => [
            $expense->incurred_at?->format('Y-m-d'),
            $expense->property?->internal_code,
            $expense->unit?->code,
            $expense->category,
            $expense->description,
            $expense->vendor?->name,
            $expense->amount,
            $expense->paid_amount,
            $expense->currency?->code,
            $expense->cost_bearer,
            $expense->payment_status,
        ])->all();

        return [[
            'التاريخ','العقار','الوحدة','التصنيف','الوصف','المورد',
            'المبلغ','المسدد','العملة','متحمل التكلفة','حالة السداد',
        ], $rows];
    }

    private function maintenance(PropertyManagementReportService $service, Carbon $from, Carbon $to): array
    {
        $rows = $service->maintenanceRows($from, $to)->map(fn ($request): array => [
            $request->reference_number,
            $request->created_at?->format('Y-m-d H:i'),
            $request->property?->internal_code,
            $request->unit?->code,
            $request->service?->name_ar,
            $request->vendor?->name,
            $request->priority,
            $request->status,
            $request->estimated_cost,
            $request->actual_cost,
            $request->currency?->code,
        ])->all();

        return [[
            'المرجع','تاريخ الإنشاء','العقار','الوحدة','الخدمة','المورد',
            'الأولوية','الحالة','التكلفة التقديرية','التكلفة الفعلية','العملة',
        ], $rows];
    }

    private function settlements(PropertyManagementReportService $service, Carbon $from, Carbon $to): array
    {
        $rows = $service->settlementRows($from, $to)->map(fn ($settlement): array => [
            $settlement->settlement_number,
            $settlement->property?->internal_code,
            $settlement->owner?->external_owner_name ?: $settlement->owner?->user?->name,
            $settlement->period_start?->format('Y-m-d'),
            $settlement->period_end?->format('Y-m-d'),
            $settlement->gross_collections,
            $settlement->owner_expenses,
            $settlement->management_fee,
            $settlement->net_payable,
            $settlement->currency?->code,
            $settlement->status,
        ])->all();

        return [[
            'رقم التسوية','العقار','المالك','من','إلى','التحصيلات',
            'المصروفات','رسوم وصال','صافي المستحق','العملة','الحالة',
        ], $rows];
    }
}
