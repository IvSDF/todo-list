<?php

namespace App\Form;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Workflow\WorkflowInterface;

class TaskUpdateType extends AbstractType
{
    private const STATUS_TRANSITIONS_LABELS = [
        'to_done' => 'Done',
        'to_rejected' => 'Rejected'
    ];
    public function __construct(private WorkflowInterface $taskStateMachine)
    {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $task = $options['data'];
        $enabledTransitions = $this->taskStateMachine->getEnabledTransitions($task);

        $choices = [];
        foreach ($enabledTransitions as $enabledTransition) {
            $choices[self::STATUS_TRANSITIONS_LABELS[$enabledTransition->getName()]] = $enabledTransition->getName();
        }

        $builder
            ->add('title')
            ->add(
                'transitionName',
                ChoiceType::class,
                [
                    'choices' => $choices,
                    'mapped' => false
                ]

            )
            ->add('submit', SubmitType::class)
        ;

        $taskStateMachine = $this->taskStateMachine;
        $builder->addEventListener( FormEvents::PRE_SUBMIT,  function (FormEvent $event) use ($task, $taskStateMachine) {
            $data = $event->getData();
            $transitionName = $data['transitionName'];

            if (!$taskStateMachine->can($task, $transitionName)) {
                $event->getForm()->addError(new FormError('Status transition is not allowed'));
                return;
            }

            $taskStateMachine->apply($task, $transitionName);
        });

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
