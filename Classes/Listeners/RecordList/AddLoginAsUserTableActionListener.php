<?php

namespace Cabag\CabagLoginas\Listeners\RecordList;

use Cabag\CabagLoginas\Hook\ToolbarItemHook;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Recordlist\Event\ModifyRecordListTableActionsEvent;

final class AddLoginAsUserTableActionListener
{
    protected LoggerInterface $logger;

    /**
     * @var $loginAsObj ToolbarItemHook
     */
    public $loginAsObj = null;


    public function getLoginAsObject()
    {
        if ($this->loginAsObj === null) {
            $this->loginAsObj = GeneralUtility::makeInstance(ToolbarItemHook::class);
        }

        return $this->loginAsObj;
    }


    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    public function modifyTableActions(ModifyRecordListTableActionsEvent $event): void
    {
        if( $event->getTable() == 'fe_users') {
            // Add a custom clipboard action after "copyMarked"
            $event->setAction('<button>My action</button>', 'myAction', '', 'copyMarked');
        }




    }
}


