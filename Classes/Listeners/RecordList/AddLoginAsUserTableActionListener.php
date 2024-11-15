<?php

namespace Cabag\CabagLoginas\Listeners\RecordList;

use Cabag\CabagLoginas\Hook\ToolbarItemHook;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Backend\RecordList\Event\ModifyRecordListRecordActionsEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;
//use TYPO3\CMS\Recordlist\Event\ModifyRecordListTableActionsEvent;
use TYPO3\CMS\Backend\RecordList\Event\ModifyRecordListTableActionsEvent;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

final class AddLoginAsUserTableActionListener
{
    protected LoggerInterface $logger;


    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    public function modifyRecordsListTableActions(ModifyRecordListTableActionsEvent $event): void
    {
        if( $event->getTable() === 'fe_users') {
            // this are the actions that are available when multiple records are selected
        }
    }

    public function modifyRecordListRecordActions(ModifyRecordListRecordActionsEvent $event): void
    {
        if( $event->getTable() === 'fe_users') {
            /** @var ToolbarItemHook $loginAsObj */
            $loginAsObj = GeneralUtility::makeInstance( ToolbarItemHook::class );
            $markup = $loginAsObj->getLoginAsIconInTable( $event->getRecord() );
//            $markup = "markup";
            // Add a custom clipboard action after "copyMarked"
            $event->setAction($markup, 'loginAs', 'secondary', 'view', '');
        }
    }
}


