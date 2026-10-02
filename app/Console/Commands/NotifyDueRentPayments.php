<?php
namespace App\Console\Commands;
use App\Models\RentDueItem;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;
class NotifyDueRentPayments extends Command
{
    protected $signature = 'wasal:notify-due-rent-payments {--days=1}';
    protected $description = 'Create admin tasks for upcoming and overdue rent payments';
    public function handle(): int
    {
        $admin = User::role(['system_admin','property_management'])->where('status','active')->first();
        if (! $admin) { $this->warn('No active property management user found.'); return self::SUCCESS; }
        $until = now()->addDays((int) $this->option('days'))->toDateString();
        $items = RentDueItem::with(['tenancy.tenant','tenancy.unit.property'])->whereIn('status',['due','overdue'])->whereDate('due_date','<=',$until)->get();
        foreach ($items as $item) {
            $exists = Task::where('related_type','rent_due_item')->where('related_id',$item->id)->whereIn('status',['pending','in_progress'])->exists();
            if ($exists) continue;
            Task::create(['title'=>'متابعة استحقاق إيجار '.$item->due_date->format('Y-m-d'),'description'=>'المستأجر: '.$item->tenancy->tenant->name.' — العقار: '.$item->tenancy->unit->property->internal_code,'related_type'=>'rent_due_item','related_id'=>$item->id,'assigned_to_user_id'=>$admin->id,'created_by_user_id'=>$admin->id,'due_at'=>$item->due_date->copy()->setTime(9,0),'notify_before_minutes'=>1440]);
            $this->line('Created reminder for due item #'.$item->id);
        }
        return self::SUCCESS;
    }
}
