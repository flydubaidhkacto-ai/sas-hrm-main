<?php

namespace App\Contracts;

interface ApprovalModuleService
{
    public function onApply($model, $actor, array $context = []): void;

    public function onApprove($modal, $actor, array $context = []): void;

    public function onReject($modal, $actor, array $context = []): void;

    public function onForward($modal, $actor, $nextApprover, array $context = []): void;

}
