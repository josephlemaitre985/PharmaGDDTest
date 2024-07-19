<?php

namespace App\Service;

use App\Entity\Marque;
use Symfony\Component\Workflow\WorkflowInterface;

class MarqueWorkflowService
{
    private $workflow;

    public function __construct(WorkflowInterface $marqueStateMachine)
    {
        $this->workflow = $marqueStateMachine;
    }

    public function getCurrentPlace(Marque $marque): string
    {
        return $this->workflow->getMarking($marque)->getPlaces()[0];
    }

    public function revertToDraft(Marque $marque): void
    {
        if ($this->workflow->can($marque, 'revert_to_draft')) {
            $this->workflow->apply($marque, 'revert_to_draft');
        }
    }

    public function publish(Marque $marque): void
    {
        if ($this->workflow->can($marque, 'publish')) {
            $this->workflow->apply($marque, 'publish');
        }
    }

    public function requireChanges(Marque $marque): void
    {
        if ($this->workflow->can($marque, 'request_changes')) {
            $this->workflow->apply($marque, 'request_changes');
        }
    }
}
