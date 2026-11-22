<?php

namespace App\Models;

use App\Models\EarnHead;
use App\Models\Fine;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollCard extends Model
{
    use HasFactory;
    protected $guarded = [];
    function employee()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    function earn_heads()
    {
        return $this->hasMany(PayrollCardEarnHeadItem::class, 'payroll_card_id');
    }
    function deductions()
    {
        return $this->hasMany(PayrollCardDeductionItem::class, 'payroll_card_id');
    }
    function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    function recalculateSalaryItems()
    {
        $netSalary = 0;
        $grossAmount = 0;
        $basicAmount = 0;

        if($this->grade && $this->grade->items->count() > 0) {
            $this->earn_heads()->delete();
            foreach($this->grade->items as $item) {
                $this->earn_heads()->create([
                    'code' => $item->code,
                    'name' => $item->name,
                    'amount' => $item->amount,
                    'amount_type' => $item->amount_type,
                    'category' => $item->category,
                ]);
            }
        }

        if(!$this->earn_heads ?: !$this->earn_heads()->where('category', 'basic')->first())
        {
            return null;
        }
        $basicIsUsingPercentage = $this->earn_heads()->where('category', 'basic')->first()?->amount_type !== 'fixed';

        if($basicIsUsingPercentage) {
            // divide everything from gross salary
            $percentageAmount = $this->earn_heads()->where('category', 'basic')->first()?->amount ?: 0;
            $basicAmount = ($this->gross_salary * ($percentageAmount / 100));
            $grossAmount = $this->gross_salary;
            $netSalary = $basicAmount;
        } else {
            // get total of everything from earn heads
            $basicAmount = $this->earn_heads()->where('category', 'basic')->first()?->amount ?: 0;
            // dd($basicAmount, $netSalary, $grossAmount);
            $netSalary = $basicAmount;
            $grossAmount = $basicAmount;
        }
        foreach($this->earn_heads()->get() as $head) {
            if(!willComeFromSystem($head->category) && $head->category != 'basic') {
                $__amount = $head->amount ?: 0;
                $mainAmount = $basicIsUsingPercentage ? $grossAmount : $basicAmount;
                $amount = $head->amount_type == 'percentage' ? ($__amount / 100) * $mainAmount : ($head->amount_type == 'other' ? $mainAmount * $__amount : $__amount);
                $netSalary = round($netSalary + $amount);
                if(!$basicIsUsingPercentage)
                {
                    $grossAmount = round($grossAmount + $amount);
                }
            }
        }

        // deduct all deductions
        foreach($this->deductions()->get() as $deduction) {
            if($deduction->category == 'fine') {
                if($deduction->reference_id === null) {
                    // System fine: skip calculation (existing behavior)
                    continue;
                } elseif($deduction->reference_id == 0) {
                    // Manual fine: use the entered amount
                    $__amount = $deduction->amount ?: 0;
                    $mainAmount = $basicIsUsingPercentage ? $grossAmount : $basicAmount;
                    $amount = $deduction->amount_type == 'percentage' ? ($__amount / 100) * $mainAmount : ($deduction->amount_type == 'other' ? $mainAmount * $__amount : $__amount);
                    $netSalary = round($netSalary - $amount);
                } else {
                    // Existing fine: calculate remaining amount from the referenced fine
                    $fine = Fine::find($deduction->reference_id);
                    if($fine) {
                        $tmp = $fine->amount - ($fine->paid_amount ?: 0);
                        if($fine->cut_off_as == 'monthly' && $fine->cut_off_amount > 0) {
                            $tmp = min($tmp, $fine->cut_off_amount);
                        }
                        $netSalary = round($netSalary - $tmp);
                    }
                }
            } elseif(!willComeFromSystem($deduction->category) && $deduction->category != 'basic') {
                $__amount = $deduction->amount ?: 0;
                $mainAmount = $basicIsUsingPercentage ? $grossAmount : $basicAmount;
                $amount = $deduction->amount_type == 'percentage' ? ($__amount / 100) * $mainAmount : ($deduction->amount_type == 'other' ? $mainAmount * $__amount : $__amount);
                $netSalary = round($netSalary - $amount);
            }
        }
        $this->basic_salary = $basicAmount;
        $this->net_salary = $netSalary;
        if($grossAmount !== $this->gross_salary) {
            $this->gross_salary = $grossAmount;
        }
        // dump($basicAmount, $netSalary, $grossAmount);
        $this->save();
    }
}










